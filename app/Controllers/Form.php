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

        foreach ($sections as $section) {
            $table = $section['table']??'form_values';
            if (empty($table)) {
                continue;
            }
            $row = $db->table($table)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($row) {
                $dataValues[$section['id']] = $section['table']?$row:json_decode($row['values'], true);
            }
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


        if (!$sections) {
            return redirect()->back()->with('error', 'No data submitted');
        }

        $specialCharPattern = '/[^A-Za-z0-9\s]/';

        foreach ($sections as $sectionId => $fields) {

            $tableNames = $request->getPost('table_name');
            $form_id = $request->getPost('form_id');
            $table = is_array($tableNames) ? ($tableNames[$sectionId] ?? null) : $tableNames;

            // Row-action mode: group/editable store many rows as an array;
            // singular (and grid/inline) store a single record object.
            $actionFlags = $request->getPost('action_flag');
            $actionFlag  = is_array($actionFlags) ? strtolower($actionFlags[$sectionId] ?? '') : '';
            $storeAsArray = in_array($actionFlag, ['group', 'editable'], true);

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

            $sectionFieldDefs = $fieldModel
                ->where('section_id', $sectionId)
                ->findAll();

            $textLikeFieldNames = [];
            foreach ($sectionFieldDefs as $fieldDef) {
                $fieldType = strtolower((string) ($fieldDef['type'] ?? ''));
                if (in_array($fieldType, ['text', 'search', 'tel', 'url', 'email'], true)) {
                    $textLikeFieldNames[] = $fieldDef['name'];
                }
            }

            if ($table === 'form_values' || empty($table)) {

                // group/editable -> store every row as a JSON array (input 0, input 1, ...).
                // singular / grid / inline -> keep a single record object.
                $payload = $storeAsArray ? $rows : ($rows[0] ?? []);

                // Replace this section's previous record so the saved set always
                // reflects the full current table (rows accumulate, no duplicates).
                $db->table('form_values')->where('section_id', $sectionId)->delete();

                $db->table('form_values')->insert([
                    'form_id'    => $form_id[$sectionId] ?? null,
                    'section_id' => $sectionId,
                    'values'     => json_encode($payload),
                ]);
            } else {

                // ⚠️ SECURITY: validate table name
                $allowedTables = $db->listTables();
                if (!in_array($table, $allowedTables, true)) {
                    continue;
                }

                if (preg_match($specialCharPattern, $value)) {
                    return redirect()->back()->withInput()->with(
                        'error',
                        'Special characters are not allowed in "' . $fieldName . '". Use only letters, numbers, and spaces.'
                    );
                }
            }

            $db->table($table)->insert($fields);
            }
        }


        return redirect()->back()->with('success', 'Saved successfully');
        // return redirect('http://localhost:8888/code4/public/index.php/form')->with('success', 'Saved successfully');
        // return redirect()->back()->with('success', 'Saved successfully');
    }
}
