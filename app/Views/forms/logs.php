<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Audit Log - <?= esc($form['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('workflow');

$human = static function (?string $ts): string {
    if (!$ts) {
        return '—';
    }
    return date('d M Y \a\t g:i a', strtotime($ts));
};
?>
<style>
    .status-badge {
        display: inline-block;
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .status-badge.status-created                  { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .status-badge.status-pending_review           { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-resubmitted_for_review   { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-pending_approval         { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-resubmitted_for_approval { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-review_rejected          { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .status-badge.status-approval_rejected        { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .status-badge.status-review_completed         { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .status-badge.status-approved                 { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }

    .history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .table-scroll { max-height: 600px; overflow: auto; -webkit-overflow-scrolling: touch; }
    .history-table { width: 100%; border-collapse: collapse; min-width: 900px; font-size: 0.9rem; }
    
    .history-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }
    
    .history-table thead th {
        text-align: left;
        padding: 1.05rem 1.25rem;
        border-bottom: 1px solid #1e293b;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #cbd5e1;
        position: sticky;
        top: 0;
        z-index: 5;
    }
    
    .history-table tbody td {
        padding: 1.05rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    
    .history-table tbody tr:last-child td { border-bottom: none; }
    .history-table tbody tr:hover { background: #f8fafc; }

    .who { font-weight: 700; color: #0f172a; }
    .reason { color: #475569; }
    .reason.none { color: #94a3b8; font-style: italic; }
    .arrow { color: #94a3b8; padding: 0 0.4rem; font-weight: bold; }
</style>

<div class="page-shell">
    <div class="page-heading d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="eyebrow" style="color: #10b981; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Audit Trail</p>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: #0f172a;"><?= esc($form['name']) ?></h1>
            <p class="page-subtitle" style="color: #64748b; font-size: 0.9rem;">Every status change and data save recorded for this form.</p>
        </div>
        <div>
            <a class="btn btn-outline-secondary font-weight-600" style="border-radius: 10px; padding: 0.6rem 1.2rem;" href="<?= base_url('forms') ?>">← Back to All Forms</a>
        </div>
    </div>

    <div class="history-card">
        <div class="table-scroll">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Status Transition</th>
                        <th>Created By</th>
                        <th>Created On</th>
                        <th>Last Updated By</th>
                        <th>Last Updated On</th>
                        <th>Reason / Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditLogs)): ?>
                        <tr><td colspan="7" style="padding: 3rem; text-align: center; color: #94a3b8;">Nothing recorded for this form yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td style="font-weight: 700; color: #0f172a;"><?= esc(workflow_action($log['action'])['label'] ?? ucfirst(str_replace('_', ' ', $log['action']))) ?></td>
                                <td>
                                    <?php if ($log['to_status']): ?>
                                        <span class="status-badge status-<?= esc($log['from_status']) ?>"><?= esc(workflow_status_label($log['from_status'])) ?></span>
                                        <span class="arrow">&rarr;</span>
                                        <span class="status-badge status-<?= esc($log['to_status']) ?>"><?= esc(workflow_status_label($log['to_status'])) ?></span>
                                    <?php else: ?>
                                        <span class="reason none">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="who"><?= esc($log['created_by_name'] ?: '—') ?></span></td>
                                <td><span style="color: #64748b; font-size: 0.85rem;"><?= esc($human($log['created_on'])) ?></span></td>
                                <td><span class="who"><?= esc($log['updated_by_name'] ?: 'Unknown user') ?></span></td>
                                <td><span style="color: #64748b; font-size: 0.85rem;"><?= esc($human($log['updated_on'])) ?></span></td>
                                <td>
                                    <?php if (!empty($log['remark'])): ?>
                                        <span class="reason"><?= esc($log['remark']) ?></span>
                                    <?php else: ?>
                                        <span class="reason none">No comment</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['totalPages'] > 1): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; border-top: 1px solid #f1f5f9; font-size: 0.88rem; color: #64748b;">
                <span>Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?> (<?= $pagination['total'] ?> total entries)</span>
                <div style="display: flex; gap: 8px;">
                    <?php if ($pagination['page'] > 1): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('forms/logs/' . $form['id']) ?>?page=<?= $pagination['page'] - 1 ?>">&larr; Previous</a>
                    <?php endif; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('forms/logs/' . $form['id']) ?>?page=<?= $pagination['page'] + 1 ?>">Next &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

