<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Audit log<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('workflow');

// "13 Jul 2026 at 2:41 pm" reads better than a raw DATETIME in a history list.
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
        border-radius: 999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .status-badge.status-created                  { background: #eef2f7; color: #475569; }
    .status-badge.status-pending_review           { background: #fff5e6; color: #b26a00; }
    .status-badge.status-resubmitted_for_review   { background: #fff5e6; color: #b26a00; }
    .status-badge.status-pending_approval         { background: #fff5e6; color: #b26a00; }
    .status-badge.status-resubmitted_for_approval { background: #fff5e6; color: #b26a00; }
    .status-badge.status-review_rejected          { background: #fdeeee; color: #b42318; }
    .status-badge.status-approval_rejected        { background: #fdeeee; color: #b42318; }
    .status-badge.status-review_completed         { background: #eef4ff; color: #1d4ed8; }
    .status-badge.status-approved                 { background: #edf8f3; color: #15704e; }

    .history-card {
        background: white;
        border: 1px solid #dfe7ee;
        border-radius: 8px;
        box-shadow: 0 16px 40px rgba(15,23,42,0.06);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .history-card > h2 {
        margin: 0;
        padding: 0.95rem 1.2rem;
        font-size: 0.95rem;
        color: #12263a;
        border-bottom: 1px solid #e6edf3;
        background: #f7fafc;
    }

    /* seven columns don't fit a narrow window — let the table scroll, not the page */
    .table-scroll { overflow-x: auto; }
    .history-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .history-table thead th {
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #46596b;
        font-weight: 700;
        padding: 0.75rem 1.2rem;
        border-bottom: 1px solid #e6edf3;
    }
    .history-table tbody td {
        padding: 0.85rem 1.2rem;
        border-bottom: 1px solid #eef3f7;
        color: #28394b;
        font-size: 0.9rem;
        vertical-align: top;
    }
    .history-table tbody tr:last-child td { border-bottom: none; }

    .who { font-weight: 600; color: #12263a; }
    .who small { display: block; font-weight: 500; color: #7a8a99; font-size: 0.76rem; }
    .reason { color: #46596b; }
    .reason.none { color: #a3b0bc; font-style: italic; }
    .arrow { color: #9aa8b5; padding: 0 0.3rem; }
    .empty-row td { text-align: center; color: #7a8a99; padding: 1.6rem 1rem; }
</style>

<div class="page-shell">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Audit log</p>
            <h1><?= esc($form['name']) ?></h1>
            <p class="page-subtitle">Every status change and data save recorded for this form.</p>
        </div>
        <div>
            <a class="btn btn-secondary" href="<?= base_url('forms') ?>">All forms</a>
        </div>
    </div>

    <!-- Ownership + current state, then the trail of everything that got it here -->
    <div class="history-card">
        <h2>Audit log</h2>

        <div class="table-scroll">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Change</th>
                        <th>Created by</th>
                        <th>Created on</th>
                        <th>Last updated by</th>
                        <th>Last updated on</th>
                        <th>Reason / comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditLogs)): ?>
                        <tr class="empty-row"><td colspan="7">Nothing recorded for this form yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td><?= esc(workflow_action($log['action'])['label'] ?? ucfirst(str_replace('_', ' ', $log['action']))) ?></td>
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
                                <td><?= esc($human($log['created_on'])) ?></td>
                                <td><span class="who"><?= esc($log['updated_by_name'] ?: 'Unknown user') ?></span></td>
                                <td><?= esc($human($log['updated_on'])) ?></td>
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
    </div>
</div>
<?= $this->endSection() ?>
