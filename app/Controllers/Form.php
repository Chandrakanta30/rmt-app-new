<?php

namespace App\Controllers;

use App\Models\FormModel;
use App\Models\SectionModel;
use App\Models\FieldModel;
use CodeIgniter\Controller;

class Form extends Controller
{
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

        $dataValues=[];

        $sections = $sectionModel->getSectionsWithFields($formIds);

        foreach ($sections as $section) {
            $table = $section['table'];
            $row = $db->table($table)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($row) {
            $dataValues[$section['id']] = $row;
        }
    }

        return view('form_view', [
            'form' => $form,
            'sections' => $sections,
            'values' => $dataValues
        ]);
    }

    public function submit()
    {
        $request = service('request');
        $db = \Config\Database::connect();

        $sections = $request->getPost('sections');
        

        if (!$sections) {
            return redirect()->back()->with('error', 'No data submitted');
        }

        
        foreach ($sections as $sectionId => $fields) {

            $table = $request->getPost('table_name');

            // ⚠️ SECURITY: validate table name
            $allowedTables = $db->listTables();
            if (!in_array($table, $allowedTables)) {
                continue;
            }

            $db->table($table)->insert($fields);
        }


        return redirect()->to('/form')->with('success', 'Saved successfully');
        // return redirect('http://localhost:8888/code4/public/index.php/form')->with('success', 'Saved successfully');
        // return redirect()->back()->with('success', 'Saved successfully');
    }
}