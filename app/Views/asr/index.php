<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR No. Mapping Directory<?= $this->endSection() ?>

<?php
$hasModalError = session()->getFlashdata('error') || session()->getFlashdata('errors');
$actionSuccess = session()->getFlashdata('action_success');
$canModify = has_permission('delete_asrno') || has_permission('update_asrno');
$canViewAuditLog = has_permission('view_audit_log');
?>

<?= $this->section('content') ?>
<style>
    .asr-page {
        padding: 0.5rem 0;
    }

    .asr-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    }

    .asr-header .eyebrow {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #10b981;
        background: #ecfdf5;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        border: 1px solid #a7f3d0;
    }

    .asr-header h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .asr-header p {
        color: #64748b;
        margin-top: 0.35rem;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .btn-create {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        border: none;
        padding: 0.75rem 1.35rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 14px 25px -5px rgba(16, 185, 129, 0.5);
        transform: translateY(-2px);
        color: white;
    }

    .btn-create:active {
        transform: scale(0.98);
    }

    .asr-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .asr-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .asr-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .asr-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    .asr-table th {
        padding: 1.05rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #cbd5e1;
        border-bottom: 1px solid #1e293b;
        white-space: nowrap;
    }

    .asr-table td {
        padding: 1.05rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .asr-table tbody tr {
        transition: all 0.15s ease-in-out;
    }

    .asr-table tbody tr:last-child td {
        border-bottom: none;
    }

    .asr-table tbody tr:hover {
        background: #f8fafc;
    }

    .asr-sl-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .asr-no-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 700;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-family: 'JetBrains Mono', monospace;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .asr-form-title {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.92rem;
    }

    .asr-user-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #334155;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .asr-user-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .asr-time-badge {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .action-btn-group {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 9px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        outline: none;
    }

    .btn-action-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-action-icon:active {
        transform: scale(0.95);
    }

    .btn-action-audit {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .btn-action-audit:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .btn-action-open {
        background: #ecfdf5;
        color: #059669;
        border-color: #a7f3d0;
    }
    .btn-action-open:hover {
        background: #10b981;
        color: #ffffff;
        border-color: #10b981;
    }

    .btn-action-edit {
        background: #fffbeb;
        color: #d97706;
        border-color: #fde68a;
    }
    .btn-action-edit:hover {
        background: #d97706;
        color: #ffffff;
        border-color: #d97706;
    }

    .btn-action-delete {
        background: #fff1f2;
        color: #e11d48;
        border-color: #fecdd3;
    }
    .btn-action-delete:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
    }

    .asr-empty {
        padding: 3.5rem 2rem;
        text-align: center;
        color: #94a3b8;
    }

    .asr-empty-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: #f1f5f9;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        z-index: 1100;
    }

    .modal-card {
        background: #ffffff;
        width: 100%;
        max-width: 480px;
        margin: 0 1rem;
        border-radius: 18px;
        padding: 1.75rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .modal-header h3 {
        margin: 0;
        color: #0f172a;
        font-size: 1.3rem;
        font-weight: 800;
    }

    .modal-close {
        border: none;
        background: #f1f5f9;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="asr-page">
    <div class="asr-header">
        <div>
            <span class="eyebrow">ASR Management</span>
            <h2>ASR No. Mappings</h2>
            <p>Create, track, and manage ASR number mappings to approved forms.</p>
        </div>
        <button type="button" class="btn-create" onclick="document.getElementById('asrCreateModal').style.display='flex'">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Create New ASR No
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.1rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span style="font-weight: 600;"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('action_error')): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius: 12px; background: #fef2f2; border: 1px solid #fecdd3; color: #991b1b; padding: 0.85rem 1.1rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span style="font-weight: 600;"><?= session()->getFlashdata('action_error') ?></span>
        </div>
    <?php endif; ?>

    <div class="asr-table-card">
        <div class="asr-table-scroll">
            <table class="asr-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Sl No.</th>
                        <th>ASR No.</th>
                        <th>Form Name</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <?php if ($canViewAuditLog): ?>
                            <th style="text-align: center; width: 100px;">Audit Log</th>
                        <?php endif; ?>
                        <?php if ($canModify): ?>
                            <th style="text-align: right; width: 140px;">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asrList)): ?>
                        <tr>
                            <td colspan="<?= 6 + ($canViewAuditLog ? 1 : 0) + ($canModify ? 1 : 0) ?>">
                                <div class="asr-empty">
                                    <div class="asr-empty-icon">📋</div>
                                    <h4 style="margin:0 0 0.25rem; color:#0f172a; font-weight:700;">No ASR numbers found</h4>
                                    <p style="margin:0; font-size:0.85rem;">Click "Create New ASR No" to get started.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $asrSlNo = 1; ?>
                        <?php foreach ($asrList as $asr): ?>
                            <tr>
                                <td>
                                    <span class="asr-sl-no"><?= $asrSlNo++ ?></span>
                                </td>
                                <td>
                                    <span class="asr-no-badge">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                        <?= esc($asr['asr_no']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="asr-form-title"><?= esc($asr['form_name'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <div class="asr-user-tag">
                                        <span class="asr-user-avatar"><?= strtoupper(substr($asr['created_by_name'] ?? 'U', 0, 1)) ?></span>
                                        <span><?= esc($asr['created_by_name'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="asr-time-badge">
                                        <?= $asr['created_at'] ? date('j M Y g:i a', strtotime($asr['created_at'])) : '-' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="asr-time-badge">
                                        <?= $asr['updated_at'] ? date('j M Y g:i a', strtotime($asr['updated_at'])) : '-' ?>
                                    </span>
                                </td>
                                <?php if ($canViewAuditLog): ?>
                                    <td style="text-align: center;">
                                        <?php if (has_permission('view_audit_log')): ?>
                                            <a class="btn-action-icon btn-action-audit" title="View Audit Log" aria-label="View Audit Log"
                                                href="<?= base_url('asr-mapping/audit-log/' . $asr['id']) ?>">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <?php if ($canModify): ?>
                                    <td style="text-align: right;">
                                        <div class="action-btn-group" style="justify-content: flex-end;">
                                            <a class="btn-action-icon btn-action-open" title="Open Form" aria-label="Open Form"
                                                href="<?= base_url('form/' . $asr['form_key'] . '?asr=' . $asr['id']) ?>">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </a>
                                            <?php if (has_permission('update_asrno')): ?>
                                                <button type="button" class="btn-action-icon btn-action-edit" title="Edit ASR" aria-label="Edit ASR"
                                                    onclick="openAsrEditModal(<?= (int) $asr['id'] ?>, '<?= esc($asr['asr_no'], 'js') ?>', <?= (int) $asr['form_id'] ?>)">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (has_permission('delete_asrno')): ?>
                                                <button type="button" class="btn-action-icon btn-action-delete" title="Delete ASR" aria-label="Delete ASR"
                                                    onclick="openAsrDeleteModal(<?= (int) $asr['id'] ?>, '<?= esc($asr['asr_no'], 'js') ?>')">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
    <div class="modal-card">
        <div class="modal-header">
            <h3>Create ASR No.</h3>
            <button type="button" class="modal-close"
                onclick="document.getElementById('asrCreateModal').style.display='none'">&times;</button>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger mb-3" style="border-radius: 10px; padding: 0.75rem 1rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger mb-3" style="border-radius: 10px; padding: 0.75rem 1rem;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('asr-mapping/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group mb-3">
                <label for="asr_no" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">ASR Number</label>
                <input type="text" id="asr_no" name="asr_no" class="form-control" value="<?= old('asr_no') ?>" required
                    placeholder="e.g. ASR-101">
            </div>

            <div class="form-group mb-4">
                <label for="form_id" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">Form</label>
                <select id="form_id" name="form_id" class="form-select" required>
                    <option value="">Select Approved Form</option>
                    <?php foreach ($approvedForms as $form): ?>
                        <option value="<?= $form['id'] ?>" <?= old('form_id') == $form['id'] ? 'selected' : '' ?>>
                            <?= esc($form['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($approvedForms)): ?>
                    <small style="color:#e11d48; margin-top:0.35rem; display:block;">No approved forms available yet.</small>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-success" style="flex: 1; padding: 0.65rem 1rem; font-weight: 700;">Save</button>
                <button type="button" class="btn btn-light" style="flex: 1; border: 1px solid #cbd5e1; padding: 0.65rem 1rem; font-weight: 600;"
                    onclick="document.getElementById('asrCreateModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php if (has_permission('delete_asrno')): ?>
    <div id="asrDeleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Delete ASR No.</h3>
                <button type="button" class="modal-close" onclick="closeAsrDeleteModal()">&times;</button>
            </div>

            <p style="margin-top:0; color:#475569; font-size:0.92rem; margin-bottom:1.25rem;">
                Deleting <strong id="asrDeleteNoLabel" style="color:#0f172a;"></strong>. Please provide a reason for audit logging.
            </p>

            <form id="asrDeleteForm" method="POST">
                <?= csrf_field() ?>
                <div class="form-group mb-4">
                    <label for="delete_remark" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">Reason for deletion</label>
                    <textarea id="delete_remark" name="delete_remark" class="form-control" rows="3" required
                        placeholder="Why is this ASR No. being deleted?"></textarea>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-danger"
                        style="flex: 1; padding: 0.65rem 1rem; font-weight: 700;">Delete</button>
                    <button type="button" class="btn btn-light" style="flex: 1; border: 1px solid #cbd5e1; padding: 0.65rem 1rem; font-weight: 600;"
                        onclick="closeAsrDeleteModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (has_permission('update_asrno')): ?>
    <div id="asrEditModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Edit ASR No.</h3>
                <button type="button" class="modal-close" onclick="closeAsrEditModal()">&times;</button>
            </div>

            <form id="asrEditForm" method="POST">
                <?= csrf_field() ?>
                <div class="form-group mb-3">
                    <label for="edit_asr_no" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">ASR Number</label>
                    <input type="text" id="edit_asr_no" name="asr_no" class="form-control" required placeholder="Enter ASR number">
                </div>

                <div class="form-group mb-3">
                    <label for="edit_form_id" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">Form</label>
                    <select id="edit_form_id" name="form_id" class="form-select" required>
                        <option value="">Select Approved Form</option>
                        <?php foreach ($approvedForms as $form): ?>
                            <option value="<?= $form['id'] ?>"><?= esc($form['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label for="update_remark" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">Reason for change</label>
                    <textarea id="update_remark" name="update_remark" class="form-control" rows="3" required
                        placeholder="Why is this ASR No. being updated?"></textarea>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-success" style="flex: 1; padding: 0.65rem 1rem; font-weight: 700;">Save Changes</button>
                    <button type="button" class="btn btn-light" style="flex: 1; border: 1px solid #cbd5e1; padding: 0.65rem 1rem; font-weight: 600;"
                        onclick="closeAsrEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($actionSuccess): ?>
    <div id="asrActionSuccessModal" class="modal-overlay" style="display: flex;">
        <div class="modal-card" style="max-width: 380px; text-align: center; padding: 2rem;">
            <div style="width:56px; height:56px; border-radius:50%; background:#ecfdf5; color:#059669; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:1.5rem; border:1px solid #a7f3d0;">
                ✓
            </div>
            <h3 style="margin:0 0 0.5rem; color:#0f172a; font-weight:800;">Success</h3>
            <p style="color:#64748b; margin:0 0 1.5rem; font-size:0.92rem;"><?= esc($actionSuccess) ?></p>
            <button type="button" class="btn btn-success" style="width: 100%; padding: 0.65rem 1rem; font-weight: 700;"
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
