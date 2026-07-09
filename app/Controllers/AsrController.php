<?php

namespace App\Controllers;

use App\Models\AsrModel;
use App\Models\FormModel;

class AsrController extends BaseController
{
    public function index()
    {
        $asrModel = new AsrModel();

        return view('asr/index', [
            'asrList'    => $asrModel->getWithFormDetails(),
            'breadcrumb' => 'ASR No.',
        ]);
    }

    public function create()
    {
        $formModel = new FormModel();

        $approvedForms = $formModel
            ->where('status', 'Approved')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('asr/create', [
            'approvedForms' => $approvedForms,
            'breadcrumb'    => 'Create ASR No.',
        ]);
    }

    public function store()
    {
        $rules = [
            'group_name' => 'required|max_length[255]',
            'asr_no'     => 'required|max_length[50]',
            'form_id'    => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $groupName = trim((string) $this->request->getPost('group_name'));
        $asrNo     = trim((string) $this->request->getPost('asr_no'));
        $formId    = (int) $this->request->getPost('form_id');

        $formModel = new FormModel();
        $form = $formModel->where('status', 'Approved')->find($formId);

        if (!$form) {
            return redirect()->back()->withInput()->with('error', 'Selected form is not an approved form.');
        }

        $db = \Config\Database::connect();
        $db->table('forms')->where('id', $formId)->update(['Group_name' => $groupName]);

        $asrModel = new AsrModel();
        $asrModel->insert([
            'asr_no'  => $asrNo,
            'form_id' => $formId,
        ]);

        return redirect()->to(base_url('asrno'))->with('success', 'ASR No. created successfully.');
    }
}
