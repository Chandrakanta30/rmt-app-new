<?php

namespace App\Controllers;

use App\Models\AsrModel;
use App\Models\AuditLogModel;
use App\Models\FormModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

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

        try {
            $asrId = $asrModel->insert([
                'asr_no'  => $asrNo,
                'form_id' => $formId,
            ]);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', 'This ASR number has already been used. Please enter a new, unique ASR number.');
        }

        (new AuditLogModel())->record(
            'create',
            'asr_no',
            $asrId,
            "Created ASR No. '{$asrNo}' for form '{$form['name']}'."
        );

        return redirect()->to(base_url('asr-mapping'))->with('success', 'ASR No. created successfully.');
    }

    public function delete($id)
    {
        $rules = [
            'delete_remark' => 'required|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('asr-mapping'))->with('delete_error', 'A remark is required to delete an ASR No.');
        }

        $asrModel = new AsrModel();
        $asr = $asrModel->where('deleted_at', null)->find($id);

        if (!$asr) {
            return redirect()->to(base_url('asr-mapping'))->with('delete_error', 'ASR No. not found.');
        }

        $remark = trim((string) $this->request->getPost('delete_remark'));
        $userId = (int) session()->get('user_id');

        $asrModel->softDelete((int) $id, $userId);

        (new AuditLogModel())->record(
            'delete',
            'asr_no',
            (int) $id,
            $remark
        );

        return redirect()->to(base_url('asr-mapping'))->with('delete_success', 'ASR No. deleted successfully.');
    }
}
