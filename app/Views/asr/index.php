<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR No.<?= $this->endSection() ?>

<?php
$hasModalError = session()->getFlashdata('error') || session()->getFlashdata('errors');
$actionSuccess = session()->getFlashdata('action_success');
$canModify = has_permission('delete_asrno') || has_permission('update_asrno');
$canViewAuditLog = has_permission('view_audit_log');
?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">ASR No.</h2>
    <button type="button" class="btn btn-success"
        onclick="document.getElementById('asrCreateModal').style.display='flex'">+ Create New ASR No</button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div
        style="background: rgba(40,150,114,0.15); border: 1px solid rgba(40,150,114,0.3); color: #1e6f5c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('action_error')): ?>
    <div
        style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('action_error') ?>
    </div>
<?php endif; ?>

<div class="protocol-card" style="padding: 0; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                <th style="padding: 1rem;">ID</th>
                <th style="padding: 1rem;">ASR No.</th>
                <th style="padding: 1rem;">Form Name</th>
                <th style="padding: 1rem;">Created By</th>
                <th style="padding: 1rem;">Created At</th>
                <th style="padding: 1rem;">Updated At</th>
                <?php if ($canViewAuditLog): ?>
                    <th style="padding: 1rem;">Audit Log</th>
                <?php endif; ?>
                <?php if ($canModify): ?>
                    <th style="padding: 1rem;">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($asrList)): ?>
                <tr>
                    <td colspan="<?= 6 + ($canViewAuditLog ? 1 : 0) + ($canModify ? 1 : 0) ?>" style="padding: 2rem; text-align: center; color: #7f8c8d;">No ASR numbers found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($asrList as $asr): ?>
                    <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;"
                        onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem;"><?= esc($asr['id']) ?></td>
                        <td style="padding: 1rem; font-weight: 500;"><?= esc($asr['asr_no']) ?></td>
                        <td style="padding: 1rem;"><?= esc($asr['form_name'] ?? '-') ?></td>
                        <td style="padding: 1rem;"><?= esc($asr['created_by_name'] ?? '-') ?></td>
                        <td style="padding: 1rem; color: #7f8c8d;">
                            <?= $asr['created_at'] ? date('j M Y g.i a', strtotime($asr['created_at'])) : '-' ?></td>
                        <td style="padding: 1rem; color: #7f8c8d;">
                            <?= $asr['updated_at'] ? date('j M Y g.i a', strtotime($asr['updated_at'])) : '-' ?></td>
                        <?php if ($canViewAuditLog): ?>
                            <td style="padding: 1rem;">
                                <?php if (has_permission('view_audit_log')): ?>
                                    <a class="btn btn-ghost"
                                        href="<?= base_url('asr-mapping/audit-log/' . $asr['id']) ?>">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <?php if ($canModify): ?>
                            <td style="padding: 1rem;">
                                <a class="btn btn-primary"
                                    href="<?= base_url('form/' . $asr['form_key'] . '?asr=' . $asr['id']) ?>">Open form</a>
                                <?php if (has_permission('update_asrno')): ?>
                                    <button type="button" class="btn btn-ghost"
                                        onclick="openAsrEditModal(<?= (int) $asr['id'] ?>, '<?= esc($asr['asr_no'], 'js') ?>', <?= (int) $asr['form_id'] ?>)">Edit</button>
                                <?php endif; ?>
                                <?php if (has_permission('delete_asrno')): ?>
                                    <button type="button" class="btn btn-ghost" style="color:#c0392b;"
                                        onclick="openAsrDeleteModal(<?= (int) $asr['id'] ?>, '<?= esc($asr['asr_no'], 'js') ?>')">Delete</button>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="asrCreateModal"
    style="display: <?= $hasModalError ? 'flex' : 'none' ?>; position: fixed; inset: 0; background: rgba(15,23,42,0.5); align-items: center; justify-content: center; z-index: 1100;">
    <div class="protocol-card" style="width: 100%; max-width: 480px; margin: 0 1rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1.25rem;">
            <h3 style="margin:0;">Create ASR No.</h3>
            <button type="button" onclick="document.getElementById('asrCreateModal').style.display='none'"
                style="border:none; background:none; font-size:1.25rem; cursor:pointer; color:#7f8c8d;">&times;</button>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div
                style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div
                style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem;">
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
    <div id="asrDeleteModal"
        style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); align-items: center; justify-content: center; z-index: 1100;">
        <div class="protocol-card" style="width: 100%; max-width: 480px; margin: 0 1rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1.25rem;">
                <h3 style="margin:0;">Delete ASR No.</h3>
                <button type="button" onclick="closeAsrDeleteModal()"
                    style="border:none; background:none; font-size:1.25rem; cursor:pointer; color:#7f8c8d;">&times;</button>
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
    <div id="asrEditModal"
        style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); align-items: center; justify-content: center; z-index: 1100;">
        <div class="protocol-card" style="width: 100%; max-width: 480px; margin: 0 1rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1.25rem;">
                <h3 style="margin:0;">Edit ASR No.</h3>
                <button type="button" onclick="closeAsrEditModal()"
                    style="border:none; background:none; font-size:1.25rem; cursor:pointer; color:#7f8c8d;">&times;</button>
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
    <div id="asrActionSuccessModal"
        style="display: flex; position: fixed; inset: 0; background: rgba(15,23,42,0.5); align-items: center; justify-content: center; z-index: 1100;">
        <div class="protocol-card"
            style="width: 100%; max-width: 380px; margin: 0 1rem; text-align: center; padding: 2rem;">
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
