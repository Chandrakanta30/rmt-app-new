<?php

namespace App\Controllers;

use App\Models\AsrModel;
use App\Models\AuditLogModel;
use App\Models\FormModel;
use App\Models\SectionModel;
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
            "Created ASR No. '{$asrNo}' for form '{$form['name']}'.",
            ['asr_no' => $asrNo, 'form_id' => $formId],
            $asrModel->find($asrId)
        );

        return redirect()->to(base_url('asr-mapping'))->with('success', 'ASR No. created successfully.');
    }

    public function update($id)
    {
        $rules = [
            'asr_no'        => 'required|max_length[50]',
            'form_id'       => 'required|is_natural_no_zero',
            'update_remark' => 'required|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'ASR No., Form and a remark are all required to update.');
        }

        $asrModel = new AsrModel();
        $asr = $asrModel->where('deleted_at', null)->find($id);

        if (!$asr) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'ASR No. not found.');
        }

        $asrNo  = trim((string) $this->request->getPost('asr_no'));
        $formId = (int) $this->request->getPost('form_id');
        $remark = trim((string) $this->request->getPost('update_remark'));

        $formModel = new FormModel();
        $form = $formModel->where('status', 'Approved')->find($formId);

        if (!$form) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'Selected form is not an approved form.');
        }

        $duplicate = $asrModel->where('asr_no', $asrNo)->where('id !=', $id)->first();

        if ($duplicate) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'This ASR number has already been used. Please enter a new, unique ASR number.');
        }

        try {
            $asrModel->update($id, [
                'asr_no'  => $asrNo,
                'form_id' => $formId,
            ]);
        } catch (DatabaseException $e) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'This ASR number has already been used. Please enter a new, unique ASR number.');
        }

        $updatedRecord = $asrModel->find($id);
        unset($updatedRecord['deleted_at']);

        (new AuditLogModel())->record(
            'update',
            'asr_no',
            (int) $id,
            $remark,
            ['asr_no' => $asrNo, 'form_id' => $formId, 'update_remark' => $remark],
            $updatedRecord
        );

        return redirect()->to(base_url('asr-mapping'))->with('action_success', 'ASR No. updated successfully.');
    }

    public function auditLog($id)
    {
        helper('audit');

        $asrModel = new AsrModel();
        $asr = $asrModel->find($id);

        if (!$asr) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'ASR No. not found.');
        }

        $formModel = new FormModel();
        $sectionModel = new SectionModel();
        $logs = (new AuditLogModel())
            ->select('audit_logs.*, users.name as updated_by_name')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.module', 'form_values')
            ->orderBy('audit_logs.created_at', 'ASC')
            ->orderBy('audit_logs.id', 'ASC')
            ->findAll();

        $formsById = [];
        $sectionsById = [];
        $previousSnapshots = [];
        $changes = [];

        foreach ($logs as $log) {
            $requestPayload = audit_decode_body($log['request_payload']);
            if (!is_array($requestPayload) || (int) ($requestPayload['asr_id'] ?? 0) !== (int) $id) {
                continue;
            }

            $sectionId = (int) ($requestPayload['section_id'] ?? 0);
            $formId = (int) ($requestPayload['form_id'] ?? 0);

            if ($sectionId <= 0 || $formId <= 0) {
                continue;
            }

            if (!isset($formsById[$formId])) {
                $formsById[$formId] = $formModel->find($formId);
            }
            if (!isset($sectionsById[$sectionId])) {
                $sectionsById[$sectionId] = $sectionModel->find($sectionId);
            }

            $formName = $formsById[$formId]['name'] ?? "Form #{$formId}";
            $sectionName = $sectionsById[$sectionId]['title'] ?? $sectionsById[$sectionId]['name'] ?? "Section #{$sectionId}";

            $currentRecord = audit_decode_body($log['current_record']);
            $currentSnapshot = audit_normalize_body($currentRecord['values'] ?? null);
            if ($currentSnapshot === null) {
                $currentSnapshot = $currentRecord;
            }

            $previousSnapshot = $previousSnapshots[$sectionId] ?? null;

            $changes[] = [
                'form'       => $formName,
                'section'    => $sectionName,
                'previous'   => audit_pretty_body($previousSnapshot),
                'current'    => audit_pretty_body($currentSnapshot),
                'updated_by' => $log['updated_by_name'] ?? 'Unknown',
                'date'       => $log['created_at'],
            ];

            $previousSnapshots[$sectionId] = $currentSnapshot;
        }

        return view('asr/audit_log', [
            'asr'        => $asr,
            'changes'    => $changes,
            'breadcrumb' => 'ASR Audit Log',
        ]);
    }

    public function delete($id)
    {
        $rules = [
            'delete_remark' => 'required|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'A remark is required to delete an ASR No.');
        }

        $asrModel = new AsrModel();
        $asr = $asrModel->where('deleted_at', null)->find($id);

        if (!$asr) {
            return redirect()->to(base_url('asr-mapping'))->with('action_error', 'ASR No. not found.');
        }

        $remark = trim((string) $this->request->getPost('delete_remark'));

        $asrModel->softDelete((int) $id);

        (new AuditLogModel())->record(
            'delete',
            'asr_no',
            (int) $id,
            $remark,
            ['delete_remark' => $remark],
            $asrModel->find($id)
        );

        return redirect()->to(base_url('asr-mapping'))->with('action_success', 'ASR No. deleted successfully.');
    }
}
