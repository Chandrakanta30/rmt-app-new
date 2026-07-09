<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR No.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">ASR No.</h2>
    <a href="<?= base_url('asrno/create') ?>" class="btn btn-success">+ Create New ASR No</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(40,150,114,0.15); border: 1px solid rgba(40,150,114,0.3); color: #1e6f5c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="protocol-card" style="padding: 0; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                <th style="padding: 1rem;">ID</th>
                <th style="padding: 1rem;">ASR No.</th>
                <th style="padding: 1rem;">Group Name</th>
                <th style="padding: 1rem;">Form Name</th>
                <th style="padding: 1rem;">Form Status</th>
                <th style="padding: 1rem;">Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($asrList)): ?>
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: #7f8c8d;">No ASR numbers found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($asrList as $asr): ?>
                    <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem;"><?= esc($asr['id']) ?></td>
                        <td style="padding: 1rem; font-weight: 500;"><?= esc($asr['asr_no']) ?></td>
                        <td style="padding: 1rem;"><?= esc($asr['group_name'] ?? '-') ?></td>
                        <td style="padding: 1rem;"><?= esc($asr['form_name'] ?? '-') ?></td>
                        <td style="padding: 1rem;">
                            <span style="background: rgba(40,150,114,0.1); color: #289672; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                <?= esc($asr['form_status'] ?? '-') ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; color: #7f8c8d;"><?= $asr['created_at'] ? date('d-m-Y H:i', strtotime($asr['created_at'])) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
