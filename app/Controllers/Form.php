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
        $formModel = new FormModel();
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
            'forms' => $forms,
            'breadcrumb' => 'Forms',
        ]);
    }

    public function index($formKey = 'accuracyform')
    {

        // return "coming";
        $formModel = new FormModel();
        $sectionModel = new SectionModel();

        // 1. Get form
        $form = $formModel->where('form_key', $formKey)->first();



        if (!$form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }


        $viewOnly = (service('request')->getGet('mode') === 'view');

        // 2. Check composite
        $db = \Config\Database::connect();

        // return $db;

        $childIds = $db->table('form_compositions')
            ->select('child_form_id')
            ->where('parent_form_id', $form['id'])
            ->orderBy('order')
            ->get()
            ->getResultArray();

        if (empty($childIds)) {
            $formIds = [$form['id']];
        } else {
            $formIds = array_column($childIds, 'child_form_id');
        }

        $dataValues = [];

        $sections = $sectionModel->getSectionsWithFields($formIds);

        // In view-only mode, skip data loading entirely so every field renders empty.
        foreach (($viewOnly ? [] : $sections) as $section) {
            // The submit path ALWAYS records into form_values (keyed by section_id) —
            $row = $db->table('form_values')
                ->where('section_id', $section['id'])
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($row) {
                // values is a JSON array of row objects for repeatable tables,
                // or a single object for grid/inline sections.
                $dataValues[$section['id']] = json_decode($row['values'], true);
                continue;
            }

            $table = !empty($section['table']) ? $section['table'] : null;
            if ($table && in_array($table, $db->listTables(), true)) {
                $tableRow = $db->table($table)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($tableRow) {
                    $dataValues[$section['id']] = $tableRow;
                }
            }
        }

        return view('form_view', [
            'form' => $form,
            'sections' => $sections,
            'values' => $dataValues,
            'readonly' => $viewOnly,
            'breadcrumb' => $form['name'] ?? 'Form',
        ]);
    }

    public function submit()
    {
        $request = service('request');
        $db = \Config\Database::connect();
        $fieldModel = new FieldModel();

        $sections = $request->getPost('sections');
        $formIds  = $request->getPost('form_id');

        $sectionIds = is_array($sections) ? array_keys($sections) : [];
        if (is_array($formIds)) {
            $sectionIds = array_values(array_unique(array_merge($sectionIds, array_keys($formIds))));
        }

        if (empty($sectionIds)) {
            return redirect()->back()->with('error', 'No data submitted');
        }

        $specialCharPattern = '/[^A-Za-z0-9\s]/';

        $normalizeCheckboxes = static function (array $row, array $checkboxFields): array {
            foreach ($checkboxFields as $cb) {
                $row[$cb] = (isset($row[$cb]) && (string) $row[$cb] === '1') ? '1' : '0';
            }

            return $row;
        };

        foreach ($sectionIds as $sectionId) {


            $submitted = is_array($sections) && array_key_exists($sectionId, $sections);
            $fields    = $submitted ? $sections[$sectionId] : [];

            $tableNames = $request->getPost('table_name');
            $form_id = $request->getPost('form_id');
            $table = is_array($tableNames) ? ($tableNames[$sectionId] ?? null) : $tableNames;


            $actionFlags = $request->getPost('action_flag');
            $actionFlag  = is_array($actionFlags) ? strtolower($actionFlags[$sectionId] ?? '') : '';
            $storeAsArray = in_array($actionFlag, ['group', 'editable'], true);


            $sectionFieldDefs = $fieldModel->where('section_id', $sectionId)->findAll();
            $checkboxFields   = [];
            foreach ($sectionFieldDefs as $fieldDef) {
                if (strtolower((string) ($fieldDef['type'] ?? '')) === 'checkbox') {
                    $checkboxFields[] = $fieldDef['name'];
                }
            }


            if (!$submitted && (empty($checkboxFields) || $storeAsArray)) {
                continue;
            }


            $isRepeatable = false;
            foreach ((array) $fields as $value) {
                if (is_array($value)) {
                    $isRepeatable = true;
                    break;
                }
            }

            if ($isRepeatable) {

                $rowIndexes = [];
                foreach ($fields as $value) {
                    if (is_array($value)) {
                        foreach (array_keys($value) as $idx) {
                            $rowIndexes[$idx] = true;
                        }
                    }
                }
                $rowIndexes = array_keys($rowIndexes);
                sort($rowIndexes, SORT_NUMERIC);

                $rows = [];
                foreach ($rowIndexes as $idx) {
                    $row = [];
                    foreach ($fields as $fieldName => $value) {
                        if (is_array($value)) {

                            if (array_key_exists($idx, $value)) {
                                $row[$fieldName] = $value[$idx];
                            }
                        } else {
                            // Scalar columns repeat on every row.
                            $row[$fieldName] = $value;
                        }
                    }

                    // Skip rows the user left completely blank.
                    $hasData = false;
                    foreach ($row as $cell) {
                        if (is_string($cell) ? trim($cell) !== '' : !empty($cell)) {
                            $hasData = true;
                            break;
                        }
                    }
                    if ($hasData) {
                        $rows[] = $row;
                    }
                }
            } else {
                $rows = [$fields]; // single record (grid / inline / fixed table)
            }

            if ($table === 'form_values' || empty($table)) {

                if ($storeAsArray) {
                    $payload = array_map(
                        static fn(array $r) => $normalizeCheckboxes($r, $checkboxFields),
                        $rows
                    );
                } else {
                    $payload = $normalizeCheckboxes($rows[0] ?? [], $checkboxFields);
                }

                // Replace this section's previous record so the saved set always
                // reflects the full current table (rows accumulate, no duplicates).
                $db->table('form_values')->where('section_id', $sectionId)->delete();

                $db->table('form_values')->insert([
                    'form_id'    => $form_id[$sectionId] ?? null,
                    'section_id' => $sectionId,
                    'values'     => json_encode($payload),
                ]);

                // Audit log: record every form_values save.
                $formValueId = $db->insertID();
                $db->table('audit_logs')->insert([
                    'user_id'    => session()->get('user_id'),
                    'action'     => 'save',
                    'module'     => 'form_values',
                    'entity_id'  => $formValueId,
                    'remark'     => 'Saved form_values (form_id: ' . ($form_id[$sectionId] ?? 'null')
                        . ', section_id: ' . $sectionId . ')',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } else {

                // ⚠️ SECURITY: validate table name
                $allowedTables = $db->listTables();
                if (!in_array($table, $allowedTables, true)) {
                    continue;
                }

                $textLikeFieldNames = [];
                foreach ($sectionFieldDefs as $fieldDef) {
                    $fieldType = strtolower((string) ($fieldDef['type'] ?? ''));
                    if (in_array($fieldType, ['text', 'search', 'tel', 'url', 'email'], true)) {
                        $textLikeFieldNames[] = $fieldDef['name'];
                    }
                }

                // Insert one DB row per submitted row.
                foreach ($rows as $row) {
                    $row = $normalizeCheckboxes($row, $checkboxFields);
                    foreach ($row as $fieldName => $value) {
                        if (!in_array($fieldName, $textLikeFieldNames, true) || !is_string($value) || $value === '') {
                            continue;
                        }

                        if (preg_match($specialCharPattern, $value)) {
                            return redirect()->back()->withInput()->with(
                                'error',
                                'Special characters are not allowed in "' . $fieldName . '". Use only letters, numbers, and spaces.'
                            );
                        }
                    }

                    $db->table($table)->insert($row);
                }
            }
        }


        return redirect()->back()->with('success', 'Saved successfully');
        // return redirect('http://localhost:8888/code4/public/index.php/form')->with('success', 'Saved successfully');
        // return redirect()->back()->with('success', 'Saved successfully');
    }
}
