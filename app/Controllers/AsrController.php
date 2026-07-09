<?php

namespace App\Controllers;

use App\Models\AsrModel;
use App\Models\FormModel;

class AsrController extends BaseController
{
    public function index()
    {
        $asrModel = new AsrModel();
        $formModel = new FormModel();

        $approvedForms = $formModel
            ->where('status', 'Approved')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('asr/index', [
            'asrList'       => $asrModel->getWithFormDetails(),
            'approvedForms' => $approvedForms,
            'breadcrumb'    => 'ASR No.',
        ]);
    }

    public function store()
    {
        $rules = [
            'asr_no'  => 'required|max_length[50]',
            'form_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $asrNo  = trim((string) $this->request->getPost('asr_no'));
        $formId = (int) $this->request->getPost('form_id');

        $formModel = new FormModel();
        $form = $formModel->where('status', 'Approved')->find($formId);

        if (!$form) {
            return redirect()->back()->withInput()->with('error', 'Selected form is not an approved form.');
        }

        $asrModel = new AsrModel();
        $duplicate = $asrModel->where('asr_no', $asrNo)->first();

        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', 'This ASR number has already been used. Please enter a new, unique ASR number.');
        }

        $asrModel->insert([
            'asr_no'  => $asrNo,
            'form_id' => $formId,
        ]);

        return redirect()->to(base_url('asrno'))->with('success', 'ASR No. created successfully.');
    }
}
