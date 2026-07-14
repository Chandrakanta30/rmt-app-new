<?php

namespace App\Controllers;

use App\Models\FormModel;
use App\Models\SectionModel;
use App\Models\FieldModel;
use App\Models\AuditLogModel;
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


        $viewOnly = (service('request')->getGet('mode') === 'view');

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

        // Check if form is approved for edit access.
        // ASR-scoped entries are always editable — the form template's own
        // approval workflow only gates direct (non-ASR) access to the form.
        $canEdit = ($asr > 0) ? true : ($form['status'] === 'Approved');
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
                $currentFormId = $form_id[$sectionId] ?? null;
                $currentAsrId = is_array($asrIds) ? (int) ($asrIds[$sectionId] ?? 0) : 0;
                // Whether this save actually came from an ASR-opened page (hidden
                // asr_id field present), as opposed to a direct form/template edit.
                $isAsrSave = $currentAsrId > 0;

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

                // Audit log: one row per save, in whichever module matches how the
                // save was triggered, always with the request payload and saved
                // snapshot so either audit trail can decode and diff field-by-field.
                $formValueId = $db->insertID();
                $savedAt     = date('Y-m-d H:i:s');
                $savedBy     = session()->get('user_id');

                if ($isAsrSave) {
                    // Saved via an ASR-opened form -> log under form_values only,
                    // this is what the ASR audit log page reads.
                    (new AuditLogModel())->record(
                        'save',
                        'form_values',
                        $formValueId,
                        'Saved form_values (form_id: ' . ($currentFormId ?? 'null')
                            . ', section_id: ' . $sectionId . ')',
                        [
                            'asr_id'     => $currentAsrId,
                            'section_id' => $sectionId,
                            'form_id'    => $currentFormId,
                        ],
                        [
                            'values' => $payload,
                        ]
                    );
                } elseif ($currentFormId) {
                    // Saved via a direct form/template edit (no ASR context) ->
                    // log under forms only.
                    (new AuditLogModel())->record(
                        'save_data',
                        'forms',
                        (int) $currentFormId,
                        'Saved data for section ' . $sectionId,
                        [
                            'section_id' => $sectionId,
                            'form_id'    => $currentFormId,
                        ],
                        [
                            'values' => $payload,
                        ]
                    );
                }

                if ($currentFormId) {
                    $db->table('forms')->where('id', $currentFormId)->update([
                        'updated_by' => $savedBy,
                        'updated_at' => $savedAt,
                    ]);
                }
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
        helper(['auth', 'workflow']);

        $request = service('request');
        $db      = \Config\Database::connect();

        $actionName = (string) $request->getPost('action');
        $remark     = trim((string) $request->getPost('remark'));

        $action = workflow_action($actionName);
        if (!$action) {
            return redirect()->back()->with('error', 'Unknown action.');
        }

        $form = $db->table('forms')->select('id, name, status')->where('id', $formId)->get()->getRowArray();
        if (!$form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $currentStatus = $form['status'] ?: 'created';

        if (!has_permission($action['permission'])) {
            return redirect()->back()->with('error', 'You are not allowed to ' . lcfirst($action['label']) . '.');
        }

        // Guard the state machine: you can only fire an action from a status it
        // is actually legal in. This is what stops "approve" jumping the review.
        if (!in_array($currentStatus, $action['from'], true)) {
            return redirect()->back()->with(
                'error',
                'Cannot ' . lcfirst($action['label']) . ' — the form is currently "'
                    . workflow_status_label($currentStatus) . '".'
            );
        }

        // Rejections must say why. That message is what the analyst redoes from.
        if ($action['remark'] && $remark === '') {
            return redirect()->back()->with('error', 'A reason is required to ' . lcfirst($action['label']) . '.');
        }

        $newStatus = $action['to'];
        $userId    = session()->get('user_id');
        $now       = date('Y-m-d H:i:s');

        $db->transStart();

        $db->table('forms')->where('id', $formId)->update([
            'status'     => $newStatus,
            'updated_by' => $userId,
            'updated_at' => $now,
        ]);

        // One row in audit_logs is the whole history
  
        $db->table('audit_logs')->insert([
            'user_id'         => $userId,
            'action'          => $actionName,
            'module'          => 'forms',
            'entity_id'       => $formId,
            'remark'          => $remark !== '' ? $remark : null,
            'request_payload' => json_encode([
                'from_status' => $currentStatus,
                'to_status'   => $newStatus,
            ]),
            'current_record'  => json_encode($form),
            'created_at'      => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Could not update the status. Please try again.');
        }

        return redirect()->back()->with(
            'success',
            $form['name'] . ' is now "' . workflow_status_label($newStatus) . '".'
        );
    }


    public function logs($formId)
    {
        helper('workflow');

        $db = \Config\Database::connect();

        $form = $db->table('forms f')
            ->select('f.*, cu.name AS created_by_name, uu.name AS updated_by_name')
            ->join('users cu', 'cu.id = f.created_by', 'left')
            ->join('users uu', 'uu.id = f.updated_by', 'left')
            ->where('f.id', $formId)
            ->get()
            ->getRowArray();

        if (!$form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }


        $auditLogs = $db->table('audit_logs a')
            ->select('a.*, u.name AS user_name, u.email AS user_email')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->where('a.module', 'forms')
            ->where('a.entity_id', $formId)
            ->orderBy('a.id', 'ASC')
            ->get()
            ->getResultArray();


        $prevUser = $form['created_by_name'] ?: null;
        $prevAt   = $form['created_at'] ?: null;

        foreach ($auditLogs as &$log) {
            $payload            = json_decode((string) $log['request_payload'], true) ?: [];
            $log['from_status'] = $payload['from_status'] ?? null;
            $log['to_status']   = $payload['to_status'] ?? null;

            $log['created_by_name'] = $prevUser;
            $log['created_on']      = $prevAt;
            $log['updated_by_name'] = $log['user_name'];
            $log['updated_on']      = $log['created_at'];

            $prevUser = $log['user_name'];
            $prevAt   = $log['created_at'];
        }
        unset($log);

        // Newest first for display.
        $auditLogs = array_reverse($auditLogs);

        return view('forms/logs', [
            'form'       => $form,
            'auditLogs'  => $auditLogs,
            'breadcrumb' => 'Audit log',
        ]);
    }
}
