<?php
namespace App\Controllers;

use App\Models\FormModel;
use App\Models\SectionModel;
use App\Models\FieldModel;
use CodeIgniter\Controller;

class Form extends Controller
{
    public function listing()
    {
        $formModel   = new FormModel();
        $sectionModel = new SectionModel();

        $forms = $formModel
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($forms as &$form) {
            $form['section_count'] = $sectionModel
                ->where('form_id', $form['id'])
                ->countAllResults();
        }

        return view('forms/list', [
            'forms'      => $forms,
            'breadcrumb' => 'Forms',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW FORM - Keep as is, no changes
    // ─────────────────────────────────────────────────────────────────────────
    public function index($formKey = 'accuracyform')
    {
        $formModel    = new FormModel();
        $sectionModel = new SectionModel();

        $form = $formModel->where('form_key', $formKey)->first();

        if (!$form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();

        $childRows = $db->table('form_compositions')
            ->select('child_form_id')
            ->where('parent_form_id', $form['id'])
            ->orderBy('order')
            ->get()
            ->getResultArray();

        $formIds = empty($childRows)
            ? [$form['id']]
            : array_column($childRows, 'child_form_id');

        $sections = $sectionModel->getSectionsWithFields($formIds);

        $dataValues = [];
        $recordIds = [];

        foreach ($sections as $section) {

            if (isset($section['_merged_tables']) && is_array($section['_merged_tables'])) {
                $tableIds = []; // Store ID for each table in merged section

                foreach ($section['_merged_tables'] as $tableInfo) {
                    $tableName   = $tableInfo['table'] ?? '';
                    $sourceId    = $tableInfo['id'] ?? null;
                    if (empty($tableName) || $sourceId === null) {
                        continue;
                    }

                    $row = $db->table($tableName)
                        ->orderBy('id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->getRowArray();

                    if ($row) {
                        $rowId = $row['id'] ?? null;
                        if ($rowId) {
                            $tableIds[$tableName] = $rowId;
                        }
                        $dataValues[$sourceId] = $row;
                    }
                }

                if (!empty($tableIds)) {
                    $recordIds[$section['id']] = $tableIds; // Store per-table IDs for merged sections
                }

            } else {
                $table = $section['table'] ?? '';
                if (empty($table)) {
                    continue;
                }

                $row = $db->table($table)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if ($row) {
                    $dataValues[$section['id']] = $row;
                    $recordIds[$section['id']] = $row['id'] ?? null; // Store single ID for normal sections
                }
            }
        }

        return view('form_view', [
            'form'       => $form,
            'sections'   => $sections,
            'values'     => $dataValues,
            'recordIds'  => $recordIds,
            'breadcrumb' => $form['name'] ?? 'Form',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUBMIT - FIXED: Proper field separation for merged sections
    // ─────────────────────────────────────────────────────────────────────────
    public function submit()
    {
        $request = service('request');
        $db      = \Config\Database::connect();

        $sections = $request->getPost('sections');
        $recordIds = $request->getPost('record_ids') ?? []; // Per-table IDs from form

        if (!$sections || !is_array($sections)) {
            return redirect()->back()->with('error', 'No data submitted');
        }

        $allowedTables = $db->listTables();
        $mergedTablesMap = $request->getPost('merged_tables') ?? [];
        $tableNamesMap   = $request->getPost('table_name')    ?? [];

        $db->transStart();

        foreach ($sections as $sectionId => $sectionData) {
            if (empty($sectionData)) {
                continue;
            }

            $mergedTablesJson = $mergedTablesMap[$sectionId] ?? null;

            if (!empty($mergedTablesJson)) {
                // ── MERGED SECTION ───────────────────────────────────────────
                // Fields are nested by original section ID to prevent collision
                // Format: sections[primaryId][originalSectionId][fieldName] = value
                
                $mergedTables = json_decode($mergedTablesJson, true);
                if (!is_array($mergedTables)) {
                    continue;
                }

                foreach ($mergedTables as $tableInfo) {
                    $originalSectionId = $tableInfo['id'] ?? null;
                    $originalTable     = $tableInfo['table'] ?? '';

                    if (empty($originalTable) || !in_array($originalTable, $allowedTables, true)) {
                        continue;
                    }

                    // Extract fields for THIS specific original section
                    // They're nested under the original section ID
                    if (isset($sectionData[$originalSectionId]) && is_array($sectionData[$originalSectionId])) {
                        $filteredData = $sectionData[$originalSectionId];
                    } else {
                        // Fallback: if not nested, skip this section
                        continue;
                    }

                    if (!empty($filteredData)) {
                        // Get the ID for THIS specific table from the form data
                        $recordIdForTable = $recordIds[$sectionId][$originalTable] ?? null;

                        if ($recordIdForTable) {
                            // UPDATE existing record
                            $db->table($originalTable)
                                ->where('id', $recordIdForTable)
                                ->update($filteredData);
                        } else {
                            // INSERT new record
                            $db->table($originalTable)->insert($filteredData);
                        }
                    }
                }
            } else {
                // ── NORMAL SECTION ───────────────────────────────────────────
                // Single table, flat field structure
                // Format: sections[sectionId][fieldName] = value
                
                $table = is_array($tableNamesMap)
                    ? ($tableNamesMap[$sectionId] ?? null)
                    : $tableNamesMap;

                if (empty($table) || !in_array($table, $allowedTables, true)) {
                    continue;
                }

                if (!empty($sectionData)) {
                    $recordId = $recordIds[$sectionId] ?? null;

                    if ($recordId) {
                        // UPDATE existing record
                        $db->table($table)
                            ->where('id', $recordId)
                            ->update($sectionData);
                    } else {
                        // INSERT new record
                        $db->table($table)->insert($sectionData);
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to save data');
        }

        $existingIdWasUsed = false;
        foreach ($recordIds as $ids) {
            if (!empty($ids)) {
                $existingIdWasUsed = true;
                break;
            }
        }

        return redirect()->back()->with('success', $existingIdWasUsed ? 'Updated successfully' : 'Saved successfully');
    }
}