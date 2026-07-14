<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR No.<?= $this->endSection() ?>

<?php
$hasModalError = session()->getFlashdata('error') || session()->getFlashdata('errors');
$actionSuccess = session()->getFlashdata('action_success');
$canModify = has_permission('delete_asrno') || has_permission('update_asrno');
$canViewAuditLog = has_permission('view_audit_log');
?>

<?= $this->section('content') ?>
<style>
    .asr-page {
        background: linear-gradient(180deg, #eef6f2 0%, #eef3f8 45%, #f6f8fb 100%);
        border-radius: 20px;
        padding: 1.75rem;
        border: 1px solid #e2ece7;
    }

    .asr-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #e6edf3;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 6px 18px rgba(15,23,42,0.04);
    }

    .asr-header .eyebrow {
        display: block;
        margin-bottom: 0.35rem;
    }

    .asr-header h2 {
        margin: 0;
        color: #12263a;
        font-size: 1.6rem;
    }

    .asr-header p {
        color: #607184;
        margin-top: 0.4rem;
        font-size: 0.88rem;
    }

    .btn-create {
        background: linear-gradient(135deg, #289672, #1e6f5c);
        color: white;
        box-shadow: 0 10px 24px rgba(40,150,114,0.28);
        border: none;
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #23875f, #185a4b);
        box-shadow: 0 14px 28px rgba(40,150,114,0.35);
        transform: translateY(-1px);
    }

    .asr-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.25rem;
    }

    .asr-table-card {
        padding: 0;
        overflow: hidden;
        border-radius: 16px;
    }

    .asr-table-scroll {
        overflow-x: auto;
    }

    .asr-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.88rem;
    }

    .asr-table thead tr {
        background: #f6f9fc;
    }

    .asr-table th {
        padding: 0.9rem 1.1rem;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #5a6e7c;
        border-bottom: 1px solid #eef2f6;
        white-space: nowrap;
    }

    .asr-table td {
        padding: 0.9rem 1.1rem;
        vertical-align: middle;
    }

    .asr-table tbody tr {
        border-bottom: 1px solid #eef2f6;
        transition: background 0.15s;
    }

    .asr-table tbody tr:last-child {
        border-bottom: none;
    }

    .asr-table tbody tr:hover {
        background: #fafcfe;
    }

    .asr-id {
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.82rem;
    }

    .asr-no-pill {
        display: inline-flex;
        align-items: center;
        background: #eef5f2;
        color: #1e6f5c;
        font-weight: 700;
        padding: 0.32rem 0.7rem;
        border-radius: 999px;
        font-size: 0.82rem;
    }

    .asr-form-name {
        color: #28394b;
        font-weight: 500;
    }

    .asr-meta {
        color: #607184;
        font-size: 0.82rem;
    }

    .asr-timestamp {
        color: #8a99a8;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .asr-empty {
        padding: 3rem 2rem;
        text-align: center;
        color: #8a99a8;
    }

    .asr-empty .empty-icon {
        font-size: 1.8rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.6;
    }

    .icon-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        margin-right: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fbfdff;
        color: #34495e;
        font-size: 0.95rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
    }

    .icon-action:last-child {
        margin-right: 0;
    }

    .icon-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15,23,42,0.1);
    }

    .icon-action.icon-view {
        color: #1f6fb2;
        border-color: rgba(31,111,178,0.25);
        background: rgba(31,111,178,0.08);
    }

    .icon-action.icon-open {
        color: #153e5c;
        border-color: rgba(21,62,92,0.25);
        background: rgba(21,62,92,0.08);
    }

    .icon-action.icon-edit {
        color: #b8860b;
        border-color: rgba(184,134,11,0.25);
        background: rgba(184,134,11,0.08);
    }

    .icon-action.icon-delete {
        color: #c0392b;
        border-color: rgba(192,57,43,0.25);
        background: rgba(192,57,43,0.08);
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.55);
        backdrop-filter: blur(2px);
        align-items: center;
        justify-content: center;
        z-index: 1100;
    }

    .modal-card {
        width: 100%;
        max-width: 480px;
        margin: 0 1rem;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(15,23,42,0.25);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .modal-header h3 {
        margin: 0;
        color: #12263a;
    }

    .modal-close {
        border: none;
        background: #f1f5f9;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-size: 1.1rem;
        cursor: pointer;
        color: #7f8c8d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        background: #e2e8f0;
    }
</style>

<div class="asr-page">
<div class="asr-header">
    <div>
        <span class="eyebrow">ASR Management</span>
        <h2>ASR No.</h2>
        <p>Create, track, and manage ASR number mappings to approved forms.</p>
    </div>
    <button type="button" class="btn btn-create"
        onclick="document.getElementById('asrCreateModal').style.display='flex'">&#43; Create New ASR No</button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success asr-alert">
        <span>&#9989;</span>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('action_error')): ?>
    <div class="alert alert-error asr-alert">
        <span>&#9888;&#65039;</span>
        <span><?= session()->getFlashdata('action_error') ?></span>
    </div>
<?php endif; ?>

<div class="protocol-card asr-table-card">
    <div class="asr-table-scroll">
        <table class="asr-table">
            <thead>
                <tr>
                    <th>Sl No.</th>
                    <th>ASR No.</th>
                    <th>Form Name</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <?php if ($canViewAuditLog): ?>
                        <th>Audit Log</th>
                    <?php endif; ?>
                    <?php if ($canModify): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asrList)): ?>
                    <tr>
                        <td colspan="<?= 6 + ($canViewAuditLog ? 1 : 0) + ($canModify ? 1 : 0) ?>">
                            <div class="asr-empty">
                                <span class="empty-icon">&#128203;</span>
                                No ASR numbers found.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $asrSlNo = 1; ?>
                    <?php foreach ($asrList as $asr): ?>
                        <tr>
                            <td class="asr-id"><?= $asrSlNo++ ?></td>
                            <td><span class="asr-no-pill"><?= esc($asr['asr_no']) ?></span></td>
                            <td class="asr-form-name"><?= esc($asr['form_name'] ?? '-') ?></td>
                            <td class="asr-meta"><?= esc($asr['created_by_name'] ?? '-') ?></td>
                            <td class="asr-timestamp">
                                <?= $asr['created_at'] ? date('j M Y g.i a', strtotime($asr['created_at'])) : '-' ?>
                            </td>
                            <td class="asr-timestamp">
                                <?= $asr['updated_at'] ? date('j M Y g.i a', strtotime($asr['updated_at'])) : '-' ?>
                            </td>
                            <?php if ($canViewAuditLog): ?>
                                <td>
                                    <?php if (has_permission('view_audit_log')): ?>
                                        <a class="icon-action icon-view" title="View audit log" aria-label="View audit log"
                                            href="<?= base_url('asr-mapping/audit-log/' . $asr['id']) ?>">&#128337;</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($canModify): ?>
                                <td style="white-space: nowrap;">
                                    <a class="icon-action icon-open" title="Open form" aria-label="Open form"
                                        href="<?= base_url('form/' . $asr['form_key'] . '?asr=' . $asr['id']) ?>">&#128196;</a>
                                    <?php if (has_permission('update_asrno')): ?>
                                        <button type="button" class="icon-action icon-edit" title="Edit" aria-label="Edit"
                                            onclick="openAsrEditModal(<?= (int) $asr['id'] ?>, '<?= esc($asr['asr_no'], 'js') ?>', <?= (int) $asr['form_id'] ?>)">&#9998;</button>
                                    <?php endif; ?>
                                    <?php if (has_permission('delete_asrno')): ?>
                                        <button type="button" class="icon-action icon-delete" title="Delete" aria-label="Delete"
                                            onclick="openAsrDeleteModal(<?= (int) $asr['id'] ?>, '<?= esc($asr['asr_no'], 'js') ?>')">&#128465;</button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="asrCreateModal" class="modal-overlay" style="display: <?= $hasModalError ? 'flex' : 'none' ?>;">
    <div class="protocol-card modal-card">
        <div class="modal-header">
            <h3>Create ASR No.</h3>
            <button type="button" class="modal-close"
                onclick="document.getElementById('asrCreateModal').style.display='none'">&times;</button>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error" style="margin-bottom: 1.25rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error" style="margin-bottom: 1.25rem;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('asr-mapping/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="asr_no">ASR Number</label>
                <input type="text" id="asr_no" name="asr_no" value="<?= old('asr_no') ?>" required
                    placeholder="Enter ASR number">
            </div>

            <div class="form-group">
                <label for="form_id">Form</label>
                <select id="form_id" name="form_id" required>
                    <option value="">Select Approved Form</option>
                    <?php foreach ($approvedForms as $form): ?>
                        <option value="<?= $form['id'] ?>" <?= old('form_id') == $form['id'] ? 'selected' : '' ?>>
                            <?= esc($form['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($approvedForms)): ?>
                    <small style="color:#c0392b;">No approved forms available yet.</small>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Save</button>
                <button type="button" class="btn btn-ghost" style="flex: 1;"
                    onclick="document.getElementById('asrCreateModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php if (has_permission('delete_asrno')): ?>
    <div id="asrDeleteModal" class="modal-overlay" style="display: none;">
        <div class="protocol-card modal-card">
            <div class="modal-header">
                <h3>Delete ASR No.</h3>
                <button type="button" class="modal-close" onclick="closeAsrDeleteModal()">&times;</button>
            </div>

            <p style="margin-top:0;">Deleting <strong id="asrDeleteNoLabel"></strong>. This can't be undone from this
                screen. Please provide a reason.</p>

            <form id="asrDeleteForm" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="delete_remark">Reason for deletion</label>
                    <textarea id="delete_remark" name="delete_remark" rows="3" required
                        placeholder="Why is this ASR No. being deleted?"></textarea>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-danger"
                        style="flex: 1; background:#c0392b; color:#fff;">Delete</button>
                    <button type="button" class="btn btn-ghost" style="flex: 1;"
                        onclick="closeAsrDeleteModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (has_permission('update_asrno')): ?>
    <div id="asrEditModal" class="modal-overlay" style="display: none;">
        <div class="protocol-card modal-card">
            <div class="modal-header">
                <h3>Edit ASR No.</h3>
                <button type="button" class="modal-close" onclick="closeAsrEditModal()">&times;</button>
            </div>

            <form id="asrEditForm" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="edit_asr_no">ASR Number</label>
                    <input type="text" id="edit_asr_no" name="asr_no" required placeholder="Enter ASR number">
                </div>

                <div class="form-group">
                    <label for="edit_form_id">Form</label>
                    <select id="edit_form_id" name="form_id" required>
                        <option value="">Select Approved Form</option>
                        <?php foreach ($approvedForms as $form): ?>
                            <option value="<?= $form['id'] ?>"><?= esc($form['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="update_remark">Reason for change</label>
                    <textarea id="update_remark" name="update_remark" rows="3" required
                        placeholder="Why is this ASR No. being updated?"></textarea>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">Save Changes</button>
                    <button type="button" class="btn btn-ghost" style="flex: 1;"
                        onclick="closeAsrEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($actionSuccess): ?>
    <div id="asrActionSuccessModal" class="modal-overlay" style="display: flex;">
        <div class="protocol-card modal-card"
            style="max-width: 380px; text-align: center; padding: 2rem;">
            <div
                style="width:56px; height:56px; border-radius:50%; background:rgba(40,150,114,0.15); color:#1e6f5c; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:1.5rem;">
                &#10003;</div>
            <h3 style="margin:0 0 0.5rem;">Success</h3>
            <p style="color:#7f8c8d; margin:0 0 1.5rem;"><?= esc($actionSuccess) ?></p>
            <button type="button" class="btn btn-success" style="width: 100%;"
                onclick="document.getElementById('asrActionSuccessModal').style.display='none'">OK</button>
        </div>
    </div>
<?php endif; ?>

<script>
    function openAsrDeleteModal(id, asrNo) {
        document.getElementById('asrDeleteForm').action = '<?= base_url('asr-mapping/delete') ?>/' + id;
        document.getElementById('asrDeleteNoLabel').textContent = asrNo;
        document.getElementById('delete_remark').value = '';
        document.getElementById('asrDeleteModal').style.display = 'flex';
    }

    function closeAsrDeleteModal() {
        document.getElementById('asrDeleteModal').style.display = 'none';
    }

    function openAsrEditModal(id, asrNo, formId) {
        document.getElementById('asrEditForm').action = '<?= base_url('asr-mapping/update') ?>/' + id;
        document.getElementById('edit_asr_no').value = asrNo;
        document.getElementById('edit_form_id').value = formId;
        document.getElementById('update_remark').value = '';
        document.getElementById('asrEditModal').style.display = 'flex';
    }

    function closeAsrEditModal() {
        document.getElementById('asrEditModal').style.display = 'none';
    }
</script>
<?= $this->endSection() ?>
