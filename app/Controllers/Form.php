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

    // public function index($formKey = 'accuracyform')
    // {

    //     // return "coming";
    //     $formModel = new FormModel();
    //     $sectionModel = new SectionModel();

    //     // 1. Get form
    //     $form = $formModel->where('form_key', $formKey)->first();



    //     if (!$form) {
    //         throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    //     }


    //     // 2. Check composite
    //     $db = \Config\Database::connect();

    //     // return $db;

    //     $childIds = $db->table('form_compositions')
    //         ->select('child_form_id')
    //         ->where('parent_form_id', $form['id'])
    //         ->orderBy('order')
    //         ->get()
    //         ->getResultArray();

    //     if (empty($childIds)) {
    //         $formIds = [$form['id']];
    //     } else {
    //         $formIds = array_column($childIds, 'child_form_id');
    //     }

    //     $dataValues = [];

    //     $sections = $sectionModel->getSectionsWithFields($formIds);

    //     foreach ($sections as $section) {
    //         // The submit path ALWAYS records into form_values (keyed by section_id) —
    //         // the forms table carries no `table` column, so every save lands there.
    //         // Read form_values first so saved data reflects back, regardless of
    //         // whether the section carries a dynamic `table` name (classic builder
    //         // sets one, e.g. fb_calc_neipa1, but nothing is ever written into it).
    //         $row = $db->table('form_values')
    //             ->where('section_id', $section['id'])
    //             ->orderBy('id', 'DESC')
    //             ->get()
    //             ->getRowArray();

    //         if ($row) {
    //             // values is a JSON array of row objects for repeatable tables,
    //             // or a single object for grid/inline sections.
    //             $dataValues[$section['id']] = json_decode($row['values'], true);
    //             continue;
    //         }

    //         // Fallback: a section bound to a real table with no form_values record
    //         // (legacy data written straight to its own table) reads its latest row.
    //         $table = !empty($section['table']) ? $section['table'] : null;
    //         if ($table && in_array($table, $db->listTables(), true)) {
    //             $tableRow = $db->table($table)
    //                 ->orderBy('id', 'DESC')
    //                 ->get()
    //                 ->getRowArray();

    //             if ($tableRow) {
    //                 $dataValues[$section['id']] = $tableRow;
    //             }
    //         }
    //     }

    //     return view('form_view', [
    //         'form' => $form,
    //         'sections' => $sections,
    //         'values' => $dataValues,
    //         'breadcrumb' => $form['name'] ?? 'Form',
    //     ]);
    // }
public function index($formKey = 'accuracyform')
{
    $asr=$_GET['asr']??0;
    $formModel = new FormModel();
    $sectionModel = new SectionModel();

    // 1. Get form
    $form = $formModel->where('form_key', $formKey)->first();

    if (!$form) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // 2. Check composite
    $db = \Config\Database::connect();

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
        // View-only mode (?mode=view): render the form structure with empty,
        // non-editable fields — the user can see the form but cannot type or
        // load any saved data.
        $request = service('request');
        $viewOnly = ($request->getGet('mode') === 'view');

    $sections = $sectionModel->getSectionsWithFields($formIds);

    foreach ($sections as $section) {
        // Read form_values first so saved data reflects back
        $row = $db->table('form_values')
            ->where('section_id', $section['id'])
            ->where('asr_id', $asr)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($row) {
            $dataValues[$section['id']] = json_decode($row['values'], true);
            continue;
        }

        // REMOVED: Fallback to section table - we only use form_values now
        // All data is stored in form_values table
        $dataValues = [];

        $sections = $sectionModel->getSectionsWithFields($formIds);

        // Check if form is approved for edit access
        $canEdit = ($form['status'] === 'Approved');
        // $asrId = (int) ($request->getGet('asr_id') ?? 0);
        $asrId = $asr;

        if ($asrId > 0) {
            $asrMapping = $db->table('form_asr_mapping')
                ->select('id')
                ->where('id', $asrId)
                ->where('form_id', $form['id'])
                ->get()
                ->getRow();

            if (!$asrMapping) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        }

        // In view-only mode, skip data loading entirely so every field renders empty.
        foreach (($viewOnly ? [] : $sections) as $section) {
            // The submit path ALWAYS records into form_values (keyed by section_id) —
            // the forms table carries no `table` column, so every save lands there.
            // Read form_values first so saved data reflects back, regardless of
            // whether the section carries a dynamic `table` name (classic builder
            // sets one, e.g. fb_calc_neipa1, but nothing is ever written into it).
            $valuesQuery = $db->table('form_values')
                ->where('section_id', $section['id']);

                $valuesQuery->where('asr_id', $asrId);
            

            $row = $valuesQuery
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($row) {
                // values is a JSON array of row objects for repeatable tables,
                // or a single object for grid/inline sections.
                $dataValues[$section['id']] = json_decode($row['values'], true);
                continue;
            }

            // Fallback: a section bound to a real table with no form_values record
            // (legacy data written straight to its own table) reads its latest row.
            $table = !empty($section['table']) ? $section['table'] : null;
            if ($asrId <= 0 && $table && in_array($table, $db->listTables(), true)) {
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
            'readonly' => $viewOnly || !$canEdit,
            'canEdit' => $canEdit,
            'asrId' => $asrId,
            'breadcrumb' => $form['name'] ?? 'Form',
        ]);
    }

    return view('form_view', [
        'form' => $form,
        'sections' => $sections,
        'values' => $dataValues,
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
        $asrIds   = $request->getPost('asr_id');

        // An unchecked checkbox submits NOTHING, so a section whose only input is
        // an unticked checkbox is entirely absent from `sections` — which used to
        // trip "No data submitted" and never record the 0. The hidden form_id[]
        // input is always rendered per section, so use it to discover every
        // section id even when that section posted no field values.
        $sectionIds = is_array($sections) ? array_keys($sections) : [];
        if (is_array($formIds)) {
            $sectionIds = array_values(array_unique(array_merge($sectionIds, array_keys($formIds))));
        }

        if (empty($sectionIds)) {
            return redirect()->back()->with('error', 'No data submitted');
        }

        $specialCharPattern = '/[^A-Za-z0-9\s]/';

        // Checkboxes only submit a value ("1") when ticked; an unticked box is
        // simply absent from the POST. Force every checkbox field to an explicit
        // "1"/"0" so the stored value is never an ambiguous empty string.
        $normalizeCheckboxes = static function (array $row, array $checkboxFields): array {
            foreach ($checkboxFields as $cb) {
                $row[$cb] = (isset($row[$cb]) && (string) $row[$cb] === '1') ? '1' : '0';
            }

            return $row;
        };

        foreach ($sectionIds as $sectionId) {

            // Whether this section actually posted any field values. A section
            // absent from `sections` only made it here via the hidden form_id[].
            $submitted = is_array($sections) && array_key_exists($sectionId, $sections);
            $fields    = $submitted ? $sections[$sectionId] : [];

            $tableNames = $request->getPost('table_name');
            $form_id = $request->getPost('form_id');
            $table = is_array($tableNames) ? ($tableNames[$sectionId] ?? null) : $tableNames;

            // Row-action mode: group/editable store many rows as an array;
            // singular (and grid/inline) store a single record object.
            $actionFlags = $request->getPost('action_flag');
            $actionFlag  = is_array($actionFlags) ? strtolower($actionFlags[$sectionId] ?? '') : '';
            $storeAsArray = in_array($actionFlag, ['group', 'editable'], true);

            // Field definitions for this section: used to coerce checkboxes to an
            // explicit 1/0 and (for real tables) to validate text-like fields.
            $sectionFieldDefs = $fieldModel->where('section_id', $sectionId)->findAll();
            $checkboxFields   = [];
            foreach ($sectionFieldDefs as $fieldDef) {
                if (strtolower((string) ($fieldDef['type'] ?? '')) === 'checkbox') {
                    $checkboxFields[] = $fieldDef['name'];
                }
            }

            // A section that posted nothing is only worth processing when it has
            // checkbox fields to record as "0". Skip otherwise so we never wipe an
            // untouched section with a blank record, and never inject a phantom
            // row into an empty repeatable (editable/group) table.
            if (!$submitted && (empty($checkboxFields) || $storeAsArray)) {
                continue;
            }

            // Repeatable table layouts submit each column as an array
            // (sections[sid][field][] -> [val0, val1, ...]). Detect that and
            // transpose the columns back into one record per row.
            $isRepeatable = false;
            foreach ((array) $fields as $value) {
                if (is_array($value)) {
                    $isRepeatable = true;
                    break;
                }
            }

            if ($isRepeatable) {
                // Inputs are indexed by row: sections[sid][field][rowIndex].
                // Collect the actual row indexes used (an unchecked checkbox or a
                // deleted row leaves gaps — iterating real keys keeps rows aligned).
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
                            // Array columns vary per row. Only include this field when
                            // it actually has a value at this index — otherwise it
                            // belongs to a DIFFERENT block instance and padding it with
                            // "" bloats the record with unrelated empty fields.
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

                // group/editable -> store every row as a JSON array (input 0, input 1, ...).
                // singular / grid / inline -> keep a single record object.
                // Coerce checkboxes to "1"/"0" on the rows we actually keep (done
                // after the blank-row skip so an unticked box never revives a row).
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
                $currentFormId = $form_id[$sectionId] ?? null;
                $currentAsrId = is_array($asrIds) ? (int) ($asrIds[$sectionId] ?? 0) : 0;

                if ($currentAsrId <= 0 && $currentFormId) {
                    $asrMapping = $db->table('form_asr_mapping')
                        ->select('id')
                        ->where('form_id', $currentFormId)
                        ->orderBy('id', 'DESC')
                        ->get()
                        ->getRow();

                    $currentAsrId = $asrMapping ? (int) $asrMapping->id : 0;
                }

                if ($currentAsrId > 0 && $currentFormId) {
                    $asrMapping = $db->table('form_asr_mapping')
                        ->select('id')
                        ->where('id', $currentAsrId)
                        ->where('form_id', $currentFormId)
                        ->get()
                        ->getRow();

                    if (!$asrMapping) {
                        return redirect()->back()->withInput()->with('error', 'Invalid ASR mapping for this form.');
                    }
                }

                $deleteQuery = $db->table('form_values')->where('section_id', $sectionId);
                if ($currentAsrId > 0) {
                    $deleteQuery->where('asr_id', $currentAsrId);
                }
                $deleteQuery->delete();

                $db->table('form_values')->insert([
                    'asr_id'     => $currentAsrId > 0 ? $currentAsrId : null,
                    'form_id'    => $currentFormId,
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
  public function updateStatus($formId)
{
    helper('auth');
    $request = service('request');
    $db = \Config\Database::connect();
    
    $reviewed = $request->getPost('reviewed') ? 1 : 0;
    $approved = $request->getPost('approved') ? 1 : 0;

    $form = $db->table('forms')->select('status')->where('id', $formId)->get()->getRowArray();
    if (! $form) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
    $currentStatus = $form['status'] ?? 'Created';

    // Only authorized roles may toggle these values.
    if (!has_role('Reviewer') && !has_role('Admin')) {
        $reviewed = 0;
    }
    if (!has_role('Approver') && !has_role('Admin')) {
        $approved = 0;
    }

    // Approval only allowed after review.
    if ($approved === 1 && !in_array($currentStatus, ['Reviewed', 'Approved'], true)) {
        return redirect()->back()->with('error', 'Form must be reviewed before it can be approved.');
    }
    
    // Determine status based on checkboxes
    if ($approved == 1) {
        $status = 'Approved';
    } elseif ($reviewed == 1) {
        $status = 'Reviewed';
    } else {
        $status = 'Created';
    }
    
    $db->table('forms')
        ->where('id', $formId)
        ->update([
            'status' => $status
        ]);
    
    return redirect()->back()->with('success', 'Status updated successfully');
}
}
