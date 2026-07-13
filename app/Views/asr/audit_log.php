<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR Audit Log<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">Audit Log &mdash; <?= esc($asr['asr_no']) ?></h2>
    <a class="btn btn-ghost" href="<?= base_url('asr-mapping') ?>">&larr; Back to ASR No.</a>
</div>

<div class="protocol-card" style="padding: 0; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; table-layout: fixed;">
        <colgroup>
            <col style="width: 12%;">
            <col style="width: 14%;">
            <col style="width: 25%;">
            <col style="width: 25%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
        </colgroup>
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                <th style="padding: 1rem;">Form</th>
                <th style="padding: 1rem;">Section</th>
                <th style="padding: 1rem;">Previous Value</th>
                <th style="padding: 1rem;">Current Value</th>
                <th style="padding: 1rem;">Updated By</th>
                <th style="padding: 1rem;">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($changes)): ?>
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: #7f8c8d;">No audit history found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($changes as $change): ?>
                    <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;"
                        onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem; word-break: break-word;"><?= esc($change['form']) ?></td>
                        <td style="padding: 1rem; font-weight: 500; word-break: break-word;"><?= esc($change['section']) ?></td>
                        <td style="padding: 1rem; color: #7f8c8d; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere;"><?= esc($change['previous']) ?></td>
                        <td style="padding: 1rem; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere;"><?= esc($change['current']) ?></td>
                        <td style="padding: 1rem; word-break: break-word;"><?= esc($change['updated_by']) ?></td>
                        <td style="padding: 1rem; color: #7f8c8d;">
                            <?= $change['date'] ? date('j M Y g.i a', strtotime($change['date'])) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
