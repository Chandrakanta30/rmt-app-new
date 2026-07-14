<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>ASR Audit Log<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">Audit Log &mdash; <?= esc($asr['asr_no']) ?></h2>
    <a class="btn btn-ghost" href="<?= base_url('asr-mapping') ?>">&larr; Back to ASR No.</a>
</div>

<div class="protocol-card" style="padding: 0;">
    <div style="max-height: 600px; overflow: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; table-layout: fixed;">
            <colgroup>
                <col style="width: 11%;">
                <col style="width: 13%;">
                <col style="width: 13%;">
                <col style="width: 21%;">
                <col style="width: 21%;">
                <col style="width: 11%;">
                <col style="width: 10%;">
            </colgroup>
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Form</th>
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Section</th>
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Input Name</th>
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Previous Value</th>
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Current Value</th>
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Updated By</th>
                    <th style="padding: 1rem; position: sticky; top: 0; background: #f8fafc;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($changes)): ?>
                    <tr>
                        <td colspan="7" style="padding: 2rem; text-align: center; color: #7f8c8d;">No audit history found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($changes as $change): ?>
                        <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;"
                            onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem; word-break: break-word;"><?= esc($change['form']) ?></td>
                            <td style="padding: 1rem; font-weight: 500; word-break: break-word;"><?= esc($change['section']) ?></td>
                            <td style="padding: 1rem; font-weight: 500; word-break: break-word;"><?= esc($change['input']) ?></td>
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

    <?php if ($pagination['totalPages'] > 1): ?>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-top: 1px solid #eef2f6; font-size: 0.85rem; color: #607184;">
            <span>Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?> (<?= $pagination['total'] ?> entries)</span>
            <div style="display: flex; gap: 8px;">
                <?php if ($pagination['page'] > 1): ?>
                    <a class="btn btn-ghost" style="padding: 0.4rem 0.9rem; min-height: auto;"
                        href="<?= base_url('asr-mapping/audit-log/' . $asr['id']) ?>?page=<?= $pagination['page'] - 1 ?>">&larr; Previous</a>
                <?php else: ?>
                    <span class="btn btn-ghost" style="padding: 0.4rem 0.9rem; min-height: auto; opacity: 0.5; pointer-events: none;">&larr; Previous</span>
                <?php endif; ?>

                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                    <a class="btn btn-ghost" style="padding: 0.4rem 0.9rem; min-height: auto;"
                        href="<?= base_url('asr-mapping/audit-log/' . $asr['id']) ?>?page=<?= $pagination['page'] + 1 ?>">Next &rarr;</a>
                <?php else: ?>
                    <span class="btn btn-ghost" style="padding: 0.4rem 0.9rem; min-height: auto; opacity: 0.5; pointer-events: none;">Next &rarr;</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
