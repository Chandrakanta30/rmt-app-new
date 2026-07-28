<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Users Management<?= $this->endSection() ?>

<?php
$canViewAuditLog = has_permission('view_audit_log');
$canModify = has_permission('delete_user') || has_permission('update_user');
?>

<?= $this->section('content') ?>
<style>
    .users-page {
        padding: 0.5rem 0;
    }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    }

    .users-header .eyebrow {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #10b981;
        background: #ecfdf5;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        border: 1px solid #a7f3d0;
    }

    .users-header h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .users-header p {
        color: #64748b;
        margin-top: 0.35rem;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .btn-create {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        border: none;
        padding: 0.75rem 1.35rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 14px 25px -5px rgba(16, 185, 129, 0.5);
        transform: translateY(-2px);
        color: white;
    }

    .users-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .users-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .users-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    .users-table th {
        padding: 1.05rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #cbd5e1;
        border-bottom: 1px solid #1e293b;
        white-space: nowrap;
    }

    .users-table td {
        padding: 1.05rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .users-table tbody tr {
        transition: all 0.15s ease-in-out;
    }

    .users-table tbody tr:last-child td {
        border-bottom: none;
    }

    .users-table tbody tr:hover {
        background: #f8fafc;
    }

    .users-id-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .users-name-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-weight: 700;
        color: #0f172a;
    }

    .users-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f172a, #334155);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.82rem;
    }

    .role-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .role-pill.admin {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecdd3;
    }

    .role-pill.reviewer {
        background: #ecfdf5;
        color: #059669;
        border-color: #a7f3d0;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 9px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        outline: none;
    }

    .btn-action-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-action-audit {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .btn-action-audit:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .btn-action-edit {
        background: #fffbeb;
        color: #d97706;
        border-color: #fde68a;
    }
    .btn-action-edit:hover {
        background: #d97706;
        color: #ffffff;
        border-color: #d97706;
    }

    .btn-action-delete {
        background: #fff1f2;
        color: #e11d48;
        border-color: #fecdd3;
    }
    .btn-action-delete:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
    }

    .users-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        font-size: 0.88rem;
        color: #64748b;
        flex-wrap: wrap;
        gap: 0.75rem;
        background: #ffffff;
    }
</style>

<div class="users-page">
    <div class="users-header">
        <div>
            <span class="eyebrow">User Management</span>
            <h2>Users Directory</h2>
            <p>Create, track, and manage user accounts with role-based permissions.</p>
        </div>
        <a href="<?= base_url('users/create') ?>" class="btn-create">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Create New User
        </a>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.1rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span style="font-weight: 600;"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius: 12px; background: #fef2f2; border: 1px solid #fecdd3; color: #991b1b; padding: 0.85rem 1.1rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span style="font-weight: 600;"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="users-table-card">
        <div class="users-table-scroll">
            <table class="users-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <?php if ($canViewAuditLog): ?>
                            <th style="text-align: center; width: 100px;">Audit Log</th>
                        <?php endif; ?>
                        <th style="text-align: right; width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="<?= 6 + ($canViewAuditLog ? 1 : 0) ?>">
                                <div style="padding: 3.5rem 2rem; text-align: center; color: #94a3b8;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                                    <h4 style="margin: 0 0 0.25rem; color: #0f172a; font-weight: 700;">No users found</h4>
                                    <p style="margin: 0; font-size: 0.85rem;">Click "+ Create New User" to add a user.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td>
                                    <span class="users-id-badge"><?= esc($user['id']) ?></span>
                                </td>
                                <td>
                                    <div class="users-name-cell">
                                        <span class="users-avatar-sm"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                                        <span><?= esc($user['name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: #64748b; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;"><?= esc($user['email']) ?></span>
                                </td>
                                <td>
                                    <span class="role-pill <?= strtolower(esc($user['role_name'] ?? 'user')) ?>">
                                        <?= esc($user['role_name'] ?: 'No Role') ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #64748b; font-size: 0.82rem; font-weight: 500;">
                                        <?= date('j M Y g:i a', strtotime($user['created_at'])) ?>
                                    </span>
                                </td>
                                <?php if ($canViewAuditLog): ?>
                                    <td style="text-align: center;">
                                        <a class="btn-action-icon btn-action-audit" title="View Audit Log" aria-label="View Audit Log"
                                            href="<?= base_url('users/audit-log/' . $user['id']) ?>">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </a>
                                    </td>
                                <?php endif; ?>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn-action-icon btn-action-edit" title="Edit User" aria-label="Edit User">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <a href="<?= base_url('users/delete/' . $user['id']) ?>" class="btn-action-icon btn-action-delete" title="Delete User" aria-label="Delete User" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="users-pagination">
                <div style="font-weight: 600; color: #475569;">
                    Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?> • <?= $pagination['total'] ?> total users
                </div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <?php if ($pagination['page'] > 1): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users') ?>?page=1">⏮ First</a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users') ?>?page=<?= $pagination['page'] - 1 ?>">← Previous</a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i == $pagination['page']): ?>
                            <span class="btn btn-sm btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users') ?>?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users') ?>?page=<?= $pagination['page'] + 1 ?>">Next →</a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('users') ?>?page=<?= $pagination['totalPages'] ?>">Last ⏭</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>