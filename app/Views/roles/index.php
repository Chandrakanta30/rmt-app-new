<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Roles & Permissions<?= $this->endSection() ?>

<?php
$canViewAuditLog = has_permission('view_audit_log');
?>

<?= $this->section('content') ?>
<style>
    .roles-page {
        background: linear-gradient(180deg, #eef6f2 0%, #eef3f8 45%, #f6f8fb 100%);
        border-radius: 20px;
        padding: 1.75rem;
        border: 1px solid #e2ece7;
    }

    .roles-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #e6edf3;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 6px 18px rgba(15,23,42,0.04);
    }

    .roles-header .eyebrow {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8a99a8;
    }

    .roles-header h2 {
        margin: 0;
        color: #12263a;
        font-size: 1.6rem;
    }

    .roles-header p {
        color: #607184;
        margin-top: 0.4rem;
        font-size: 0.88rem;
    }

    .btn-create {
        background: linear-gradient(135deg, #289672, #1e6f5c);
        color: white;
        box-shadow: 0 10px 24px rgba(40,150,114,0.28);
        border: none;
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #23875f, #185a4b);
        box-shadow: 0 14px 28px rgba(40,150,114,0.35);
        transform: translateY(-1px);
    }

    .roles-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.25rem;
        padding: 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .roles-alert.alert-success {
        background: rgba(40,150,114,0.15);
        border: 1px solid rgba(40,150,114,0.3);
        color: #1e6f5c;
    }

    .roles-alert.alert-error {
        background: rgba(231,76,60,0.15);
        border: 1px solid rgba(231,76,60,0.3);
        color: #c0392b;
    }

    .roles-table-card {
        padding: 0;
        overflow: hidden;
        border-radius: 16px;
    }

    .roles-table-scroll {
        overflow-x: auto;
    }

    .roles-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.88rem;
    }

    .roles-table thead tr {
        background: #f6f9fc;
    }

    .roles-table th {
        padding: 0.9rem 1.1rem;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #5a6e7c;
        border-bottom: 1px solid #eef2f6;
        white-space: nowrap;
    }

    .roles-table td {
        padding: 0.9rem 1.1rem;
        vertical-align: middle;
    }

    .roles-table tbody tr {
        border-bottom: 1px solid #eef2f6;
        transition: background 0.15s;
    }

    .roles-table tbody tr:last-child {
        border-bottom: none;
    }

    .roles-table tbody tr:hover {
        background: #fafcfe;
    }

    .roles-id {
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.82rem;
    }

    .roles-name {
        color: #28394b;
        font-weight: 500;
    }

    .roles-description {
        color: #607184;
    }

    .roles-empty {
        padding: 3rem 2rem;
        text-align: center;
        color: #8a99a8;
    }

    .roles-empty .empty-icon {
        font-size: 1.8rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.6;
    }

    .icon-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        margin-right: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fbfdff;
        color: #34495e;
        font-size: 0.95rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
    }

    .icon-action:last-child {
        margin-right: 0;
    }

    .icon-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15,23,42,0.1);
    }

    .icon-action.icon-edit-permissions {
        color: #8e44ad;
        border-color: rgba(142,68,173,0.25);
        background: rgba(142,68,173,0.08);
    }

    .icon-action.icon-delete {
        color: #c0392b;
        border-color: rgba(192,57,43,0.25);
        background: rgba(192,57,43,0.08);
    }

    .icon-action.icon-audit {
        color: #6c5b7b;
        border-color: rgba(108,91,123,0.25);
        background: rgba(108,91,123,0.08);
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
        font-size: 0.72rem;
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

    .role-badge.editor {
        background: rgba(184,134,11,0.12);
        color: #b8860b;
    }

    .btn-permissions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        border: 1px solid #8e44ad;
        color: #8e44ad;
        background: rgba(142,68,173,0.08);
        border-radius: 9px;
        text-decoration: none;
        transition: all 0.15s;
    }

    .btn-permissions:hover {
        background: rgba(142,68,173,0.15);
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15,23,42,0.1);
    }

    .roles-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-top: 1px solid #eef2f6;
        font-size: 0.85rem;
        color: #607184;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .roles-pagination .pagination-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .roles-pagination .btn {
        padding: 0.4rem 0.9rem;
        min-height: auto;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fbfdff;
        color: #34495e;
        text-decoration: none;
        transition: all 0.15s;
        font-size: 0.82rem;
    }

    .roles-pagination .btn:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
    }

    .roles-pagination .btn.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .roles-pagination .btn-active {
        background: #289672;
        color: white;
        border-color: #289672;
    }
</style>

<div class="roles-page">
    <div class="roles-header">
        <div>
            <span class="eyebrow">Role Management</span>
            <h2>Roles & Permissions</h2>
            <p>Create, track, and manage roles with permission-based access control.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= base_url('permissions') ?>" class="btn-permissions">&#128274; Permissions</a>
            <a href="<?= base_url('roles/create') ?>" class="btn btn-create">&#43; Create</a>
        </div>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="roles-alert alert-success">
            <span>&#9989;</span>
            <span><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="roles-alert alert-error">
            <span>&#9888;&#65039;</span>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="protocol-card roles-table-card">
        <div class="roles-table-scroll">
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <?php if ($canViewAuditLog): ?>
                            <th>Audit Log</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($roles)): ?>
                        <tr>
                            <td colspan="<?= 4 + ($canViewAuditLog ? 1 : 0) ?>">
                                <div class="roles-empty">
                                    <span class="empty-icon">&#128736;</span>
                                    No roles found.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($roles as $role): ?>
                            <tr>
                                <td class="roles-id"><?= esc($role['id']) ?></td>
                                <td class="roles-name">
                                    <span class="role-badge <?= strtolower(esc($role['name'])) ?>">
                                        <?= esc($role['name']) ?>
                                    </span>
                                </td>
                                <td class="roles-description"><?= esc($role['description'] ?: 'No description provided.') ?></td>
                                <?php if ($canViewAuditLog): ?>
                                    <td>
                                        <a class="icon-action icon-audit" title="View audit log" aria-label="View audit log"
                                            href="<?= base_url('roles/audit-log/' . $role['id']) ?>">&#128337;</a>
                                    </td>
                                <?php endif; ?>
                                <td style="white-space: nowrap;">
                                    <a href="<?= base_url('roles/edit/' . $role['id']) ?>" class="icon-action icon-edit-permissions" title="Edit & Permissions" aria-label="Edit & Permissions">&#128273;</a>
                                    <?php if($role['name'] !== 'Admin'): ?>
                                        <a href="<?= base_url('roles/delete/' . $role['id']) ?>" class="icon-action icon-delete" title="Delete" aria-label="Delete" onclick="return confirm('Are you sure you want to delete this role?')">&#128465;</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="roles-pagination">
                <div class="pagination-info">
                    <span>Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?></span>
                    <span style="color: #8a99a8;">•</span>
                    <span><?= $pagination['total'] ?> total roles</span>
                </div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <?php if ($pagination['page'] > 1): ?>
                        <a class="btn" href="<?= base_url('roles') ?>?page=1">⏮ First</a>
                        <a class="btn" href="<?= base_url('roles') ?>?page=<?= $pagination['page'] - 1 ?>">← Previous</a>
                    <?php else: ?>
                        <span class="btn disabled">⏮ First</span>
                        <span class="btn disabled">← Previous</span>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i == $pagination['page']): ?>
                            <span class="btn btn-active"><?= $i ?></span>
                        <?php else: ?>
                            <a class="btn" href="<?= base_url('roles') ?>?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a class="btn" href="<?= base_url('roles') ?>?page=<?= $pagination['page'] + 1 ?>">Next →</a>
                        <a class="btn" href="<?= base_url('roles') ?>?page=<?= $pagination['totalPages'] ?>">Last ⏭</a>
                    <?php else: ?>
                        <span class="btn disabled">Next →</span>
                        <span class="btn disabled">Last ⏭</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>