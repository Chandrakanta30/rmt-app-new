<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>User Audit Log - <?= esc($user['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .audit-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .audit-table-scroll { max-height: 600px; overflow: auto; -webkit-overflow-scrolling: touch; }
    
    .audit-table { width: 100%; border-collapse: collapse; min-width: 900px; font-size: 0.9rem; table-layout: fixed; }
    
    .audit-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }
    
    .audit-table thead th {
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
        background: #0f172a;
    }
    
    .audit-table tbody td {
        padding: 1.05rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    
    .audit-table tbody tr:last-child td { border-bottom: none; }
    .audit-table tbody tr:hover { background: #f8fafc; }

    .action-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid transparent;
    }

    .action-badge.create { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
    .action-badge.update { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .action-badge.delete { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .action-badge.login  { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .action-badge.logout { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
</style>

<div class="page-shell">
    <div class="page-heading d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="eyebrow" style="color: #10b981; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Audit Trail</p>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: #0f172a;">User Audit Log &mdash; <?= esc($user['name']) ?></h1>
            <p class="page-subtitle" style="color: #64748b; font-size: 0.9rem;">User ID: #<?= esc($user['id']) ?> &bull; Email: <?= esc($user['email']) ?></p>
        </div>
        <div>
            <a class="btn btn-outline-secondary font-weight-600" style="border-radius: 10px; padding: 0.6rem 1.2rem;" href="<?= base_url('users') ?>">← Back to Users Directory</a>
        </div>
    </div>

    <div class="audit-card">
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
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($changes)): ?>
                        <tr>
                            <td colspan="6" style="padding: 3rem; text-align: center; color: #94a3b8;">No audit history found for this user.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($changes as $change): ?>
                            <tr>
                                <td>
                                    <span class="action-badge <?= strtolower(esc($change['action'])) ?>">
                                        <?= esc($change['action'] ?: 'Update') ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: #0f172a;"><?= esc($change['field'] ?? '-') ?></td>
                                <td style="color: #64748b; font-family: monospace; font-size: 0.85rem; word-break: break-word;"><?= esc($change['previous'] ?? '-') ?></td>
                                <td style="color: #059669; font-weight: 600; font-family: monospace; font-size: 0.85rem; word-break: break-word;"><?= esc($change['current'] ?? '-') ?></td>
                                <td style="font-weight: 600; color: #334155;"><?= esc($change['performed_by'] ?? '-') ?></td>
                                <td style="color: #64748b; font-size: 0.82rem; white-space: nowrap;">
                                    <?= $change['date'] ? date('j M Y g:i a', strtotime($change['date'])) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; border-top: 1px solid #f1f5f9; font-size: 0.88rem; color: #64748b;">
                <span>Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?> (<?= $pagination['total'] ?> total entries)</span>
                <div style="display: flex; gap: 8px;">
                    <?php if ($pagination['page'] > 1): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users/audit-log/' . $user['id']) ?>?page=<?= $pagination['page'] - 1 ?>">&larr; Previous</a>
                    <?php endif; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users/audit-log/' . $user['id']) ?>?page=<?= $pagination['page'] + 1 ?>">Next &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>