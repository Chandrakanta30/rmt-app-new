<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Role Audit Log<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .audit-page {
        background: linear-gradient(180deg, #eef6f2 0%, #eef3f8 45%, #f6f8fb 100%);
        border-radius: 20px;
        padding: 1.75rem;
        border: 1px solid #e2ece7;
    }

    .audit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #e6edf3;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 6px 18px rgba(15,23,42,0.04);
    }

    .audit-header h2 {
        margin: 0;
        color: #12263a;
        font-size: 1.6rem;
    }

    .audit-header .subtitle {
        color: #607184;
        font-size: 0.88rem;
        margin-top: 0.4rem;
    }

    .audit-table-card {
        padding: 0;
        overflow: hidden;
        border-radius: 16px;
    }

    .audit-table-scroll {
        max-height: 600px;
        overflow: auto;
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.88rem;
        table-layout: fixed;
    }

    .audit-table thead tr {
        background: #f6f9fc;
    }

    .audit-table th {
        padding: 0.9rem 1.1rem;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #5a6e7c;
        border-bottom: 1px solid #eef2f6;
        position: sticky;
        top: 0;
        background: #f6f9fc;
        z-index: 10;
    }

    .audit-table td {
        padding: 0.9rem 1.1rem;
        vertical-align: middle;
    }

    .audit-table tbody tr {
        border-bottom: 1px solid #eef2f6;
        transition: background 0.15s;
    }

    .audit-table tbody tr:last-child {
        border-bottom: none;
    }

    .audit-table tbody tr:hover {
        background: #fafcfe;
    }

    .audit-empty {
        padding: 3rem 2rem;
        text-align: center;
        color: #8a99a8;
    }

    .audit-empty .empty-icon {
        font-size: 1.8rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.6;
    }

    .audit-value {
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .audit-value.previous {
        color: #7f8c8d;
    }

    .audit-role {
        font-weight: 500;
    }

    .audit-timestamp {
        color: #8a99a8;
        white-space: nowrap;
    }

    .audit-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-top: 1px solid #eef2f6;
        font-size: 0.85rem;
        color: #607184;
    }

    .audit-pagination .btn {
        padding: 0.4rem 0.9rem;
        min-height: auto;
    }

    .audit-pagination .btn.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .action-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .action-badge.create {
        background: rgba(40,150,114,0.12);
        color: #1e6f5c;
    }

    .action-badge.update {
        background: rgba(184,134,11,0.12);
        color: #b8860b;
    }

    .action-badge.delete {
        background: rgba(192,57,43,0.12);
        color: #c0392b;
    }

    .action-badge.permission {
        background: rgba(142,68,173,0.12);
        color: #8e44ad;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: rgba(21,62,92,0.10);
        color: #153e5c;
    }

    .role-badge.admin {
        background: rgba(231,76,60,0.12);
        color: #c0392b;
    }

    .role-badge.reviewer {
        background: rgba(40,150,114,0.12);
        color: #1e6f5c;
    }
</style>

<div class="audit-page">
    <div class="audit-header">
        <div>
            <h2>Audit Log &mdash; <?= esc($role['name']) ?></h2>
            <div class="subtitle">Role ID: #<?= esc($role['id']) ?> &bull; Description: <?= esc($role['description'] ?: 'No description') ?></div>
        </div>
        <a class="btn btn-ghost" href="<?= base_url('roles') ?>">&larr; Back to Roles</a>
    </div>

    <div class="protocol-card audit-table-card">
        <div class="audit-table-scroll">
            <table class="audit-table">
                <colgroup>
                    <col style="width: 12%;">
                    <col style="width: 15%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 18%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Field</th>
                        <th>Previous Value</th>
                        <th>Current Value</th>
                        <th>Performed By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($changes)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="audit-empty">
                                    <span class="empty-icon">&#128221;</span>
                                    No audit history found for this role.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($changes as $change): ?>
                            <tr>
                                <td>
                                    <span class="action-badge <?= strtolower(esc($change['action'])) ?>">
                                        <?= esc($change['action'] ?: 'Update') ?>
                                    </span>
                                    <?php if (!empty($change['remark'])): ?>
                                        <div style="font-size: 0.7rem; color: #607184; margin-top: 3px;">
                                            <?= esc($change['remark']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="audit-role"><?= esc($change['field'] ?? '-') ?></td>
                                <td class="audit-value previous"><?= esc($change['previous'] ?? '-') ?></td>
                                <td class="audit-value"><?= esc($change['current'] ?? '-') ?></td>
                                <td><?= esc($change['performed_by'] ?? '-') ?></td>
                                <td class="audit-timestamp">
                                    <?= $change['date'] ? date('j M Y g.i a', strtotime($change['date'])) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="audit-pagination">
                <span>Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?> (<?= $pagination['total'] ?> entries)</span>
                <div style="display: flex; gap: 8px;">
                    <?php if ($pagination['page'] > 1): ?>
                        <a class="btn btn-ghost" href="<?= base_url('roles/audit-log/' . $role['id']) ?>?page=<?= $pagination['page'] - 1 ?>">&larr; Previous</a>
                    <?php else: ?>
                        <span class="btn btn-ghost disabled">&larr; Previous</span>
                    <?php endif; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a class="btn btn-ghost" href="<?= base_url('roles/audit-log/' . $role['id']) ?>?page=<?= $pagination['page'] + 1 ?>">Next &rarr;</a>
                    <?php else: ?>
                        <span class="btn btn-ghost disabled">Next &rarr;</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>