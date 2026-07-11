<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR No.<?= $this->endSection() ?>

<?php $hasModalError = session()->getFlashdata('error') || session()->getFlashdata('errors'); ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">ASR No.</h2>
    <button type="button" class="btn btn-success" onclick="document.getElementById('asrCreateModal').style.display='flex'">+ Create New ASR No</button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(40,150,114,0.15); border: 1px solid rgba(40,150,114,0.3); color: #1e6f5c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="protocol-card" style="padding: 0; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                <th style="padding: 1rem;">ID</th>
                <th style="padding: 1rem;">ASR No.</th>
                <th style="padding: 1rem;">Form Name</th>
                <th style="padding: 1rem;">Created At</th>
                <th style="padding: 1rem;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($asrList)): ?>
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; color: #7f8c8d;">No ASR numbers found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($asrList as $asr): ?>
                    <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem;"><?= esc($asr['id']) ?></td>
                        <td style="padding: 1rem; font-weight: 500;"><?= esc($asr['asr_no']) ?></td>
                        <td style="padding: 1rem;"><?= esc($asr['form_name'] ?? '-') ?></td>
                        <td style="padding: 1rem; color: #7f8c8d;"><?= $asr['created_at'] ? date('d-m-Y H:i', strtotime($asr['created_at'])) : '-' ?></td>
                        <td style="padding: 1rem;">
                            <?php if (!empty($asr['form_key'])): ?>
                                <a class="btn btn-primary"
   href="<?= base_url('form/' . $asr['form_key'] . '?asr_id=' . $asr['id']) ?>">
    Open
</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="asrCreateModal" style="display: <?= $hasModalError ? 'flex' : 'none' ?>; position: fixed; inset: 0; background: rgba(15,23,42,0.5); align-items: center; justify-content: center; z-index: 1100;">
    <div class="protocol-card" style="width: 100%; max-width: 480px; margin: 0 1rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1.25rem;">
            <h3 style="margin:0;">Create ASR No.</h3>
            <button type="button" onclick="document.getElementById('asrCreateModal').style.display='none'" style="border:none; background:none; font-size:1.25rem; cursor:pointer; color:#7f8c8d;">&times;</button>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('asrno/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="asr_no">ASR Number</label>
                <input type="text" id="asr_no" name="asr_no" value="<?= old('asr_no') ?>" required placeholder="Enter ASR number">
            </div>

            <div class="form-group">
                <label for="form_id">Form</label>
                <select id="form_id" name="form_id" required>
                    <option value="">Select Approved Form</option>
                    <?php foreach ($approvedForms as $form): ?>
                        <option value="<?= $form['id'] ?>" <?= old('form_id') == $form['id'] ? 'selected' : '' ?>><?= esc($form['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($approvedForms)): ?>
                    <small style="color:#c0392b;">No approved forms available yet.</small>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Save</button>
                <button type="button" class="btn btn-ghost" style="flex: 1;" onclick="document.getElementById('asrCreateModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
