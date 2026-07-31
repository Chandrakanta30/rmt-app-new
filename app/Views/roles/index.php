<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Roles & Permissions Management<?= $this->endSection() ?>

<?php
$canViewAuditLog = has_permission('view_audit_log');
?>

<?= $this->section('content') ?>
<style>
    /* ==========================
   ROLES PAGE
========================== */

.roles-page{
    padding:.5rem 0;
}

/* Header Card */

.roles-header{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:24px;
    margin-bottom:25px;
    box-shadow:0 4px 20px rgba(15,23,42,.05);
}

.roles-header .eyebrow{
    display:inline-block;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    color:#10b981;
    background:#ecfdf5;
    border:1px solid #a7f3d0;
    padding:4px 10px;
    border-radius:6px;
    margin-bottom:10px;
}

.roles-header h2{
    margin:0;
    font-size:30px;
    font-weight:800;
    color:#0f172a;
}

.roles-header p{
    margin-top:6px;
    color:#64748b;
    font-size:15px;
}

/* ==========================
   SEARCH ROW
========================== */

.roles-toolbar{
    margin-top:22px;
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap:20px;
    flex-wrap:wrap;
}

/* Left */

.toolbar-left{
    display:flex;
    align-items:flex-end;
    gap:15px;
    flex:1;
    min-width:300px;
}

.search-wrapper{
    flex:1;
}

.search-wrapper label,
.filter-wrapper label{
    display:block;
    font-size:11px;
    font-weight:700;
    color:#334155;
    text-transform:uppercase;
    margin-bottom:6px;
}

.search-input{
    width:100%;
    height:46px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    padding:0 16px;
    font-size:14px;
    transition:.25s;
    outline:none;
}

.search-input:focus{
    border-color:#10b981;
    box-shadow:0 0 0 3px rgba(16,185,129,.15);
}

/* Dropdown */

.filter-wrapper{
    width:220px;
}

.status-filter{
    width:100%;
    height:46px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    padding:0 15px;
    font-size:14px;
    background:#fff;
    outline:none;
}

.status-filter:focus{
    border-color:#10b981;
    box-shadow:0 0 0 3px rgba(16,185,129,.15);
}

/* ==========================
   RIGHT BUTTONS
========================== */

.toolbar-right{
    display:flex;
    align-items:flex-end;
    gap:12px;
}

/* Permission */

.btn-permissions{
    display:inline-flex;
    align-items:center;
    gap:8px;
    height:46px;
    padding:0 20px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    color:#2563eb;
    border:1px solid #bfdbfe;
    background:#eff6ff;
    transition:.2s;
}

.btn-permissions:hover{
    background:#2563eb;
    color:#fff;
}

/* Create */

.btn-create{
    display:inline-flex;
    align-items:center;
    gap:8px;
    height:46px;
    padding:0 22px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#10b981,#059669);
    color:#fff;
    text-decoration:none;
    font-weight:700;
    transition:.25s;
    box-shadow:0 10px 20px rgba(16,185,129,.25);
}

.btn-create:hover{
    color:#fff;
    transform:translateY(-2px);
}

/* ==========================
   TABLE CARD
========================== */

.roles-table-card{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(15,23,42,.05);
}

.roles-table-scroll{
    overflow-x:auto;
}

.roles-table{
    width:100%;
    border-collapse:collapse;
}

.roles-table thead{
    background:#0f172a;
}

.roles-table th{
    padding:16px;
    color:#fff;
    text-transform:uppercase;
    font-size:12px;
}

.roles-table td{
    padding:16px;
    border-bottom:1px solid #edf2f7;
}

.roles-table tbody tr:hover{
    background:#f8fafc;
}

/* ==========================
   BADGES
========================== */

.roles-id-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:30px;
    height:30px;
    border-radius:8px;
    background:#f1f5f9;
    font-weight:700;
}

.role-badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:8px;
    background:#f1f5f9;
    font-weight:700;
    border:1px solid #cbd5e1;
}

.role-badge.admin{
    background:#fee2e2;
    color:#991b1b;
}

.role-badge.reviewer{
    background:#dcfce7;
    color:#15803d;
}

.role-badge.editor{
    background:#fef3c7;
    color:#b45309;
}

/* ==========================
   ACTION BUTTONS
========================== */

.btn-action-icon{
    width:36px;
    height:36px;
    display:inline-flex;
    justify-content:center;
    align-items:center;
    border-radius:50%;
    text-decoration:none;
    transition:.2s;
}

.btn-action-key{
    background:#f3e8ff;
    color:#7e22ce;
}

.btn-action-key:hover{
    background:#7e22ce;
    color:#fff;
}

.btn-action-audit{
    background:#eff6ff;
    color:#2563eb;
}

.btn-action-audit:hover{
    background:#2563eb;
    color:#fff;
}

.btn-action-delete{
    background:#fee2e2;
    color:#dc2626;
}

.btn-action-delete:hover{
    background:#dc2626;
    color:#fff;
}

/* ==========================
   PAGINATION
========================== */

.roles-pagination{
    padding:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:15px;
    border-top:1px solid #edf2f7;
}

/* ==========================
   RESPONSIVE
========================== */

@media(max-width:992px){

    .roles-toolbar{
        flex-direction:column;
        align-items:stretch;
    }

    .toolbar-left{
        width:100%;
        flex-direction:column;
    }

    .toolbar-right{
        width:100%;
        flex-direction:column;
    }

    .filter-wrapper{
        width:100%;
    }

    .btn-permissions,
    .btn-create{
        width:100%;
        justify-content:center;
    }
}

@media(max-width:576px){

    .roles-header{
        padding:18px;
    }

    .roles-header h2{
        font-size:24px;
    }
}
</style>
<div class="roles-header">

    <div class="roles-header-left">
        <span class="eyebrow">ROLE MANAGEMENT</span>

        <h2>Roles & Permissions Directory</h2>

        <p>Create, track, and manage roles with permission-based access control.</p>
    </div>

    <!-- Search & Buttons -->
    <div class="roles-toolbar">

        <!-- Left -->
        <div class="toolbar-left">

            <div class="search-wrapper">
                <label>Search Roles</label>
                <input
                    type="text"
                    id="roleSearch"
                    class="search-input"
                    placeholder="Search role...">
            </div>

            <div class="filter-wrapper">
                <label>Filter by Role</label>

                <select id="roleFilter" class="status-filter">
                    <option value="">All Roles</option>

                    <?php foreach($roles as $role): ?>
                        <option value="<?= strtolower($role['name']) ?>">
                            <?= esc($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <!-- Right -->
        <div class="toolbar-right">

            <a href="<?= base_url('permissions') ?>" class="btn-permissions">
                Permissions
            </a>

            <a href="<?= base_url('roles/create') ?>" class="btn-create">
                + Create New Role
            </a>

        </div>

    </div>

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

    <div class="roles-table-scroll">
    <table id="rolesTable" class="roles-table">
        <thead>
            <tr>
                <th style="width: 70px;">ID</th>
                <th>Role Name</th>
                <th>Description</th>

                <?php if ($canViewAuditLog): ?>
                    <th style="text-align: center; width: 100px;">Audit Log</th>
                <?php endif; ?>

                <th style="text-align: right; width: 120px;">Actions</th>
            </tr>
        </thead>

        <tbody>

            <?php if(empty($roles)): ?>

                <tr>
                    <td colspan="<?= 4 + ($canViewAuditLog ? 1 : 0) ?>">
                        <div style="padding:3.5rem 2rem;text-align:center;color:#94a3b8;">
                            <div style="font-size:2rem;margin-bottom:.5rem;">🛠️</div>
                            <h4 style="margin:0 0 .25rem;color:#0f172a;font-weight:700;">
                                No roles found
                            </h4>
                            <p style="margin:0;font-size:.85rem;">
                                Click "+ Create New Role" to add a role.
                            </p>
                        </div>
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach($roles as $role): ?>
                    <tr
                        data-role="<?= strtolower($role['name']) ?>"
                        data-description="<?= strtolower($role['description'] ?? '') ?>">

                        <td>
                            <span class="roles-id-badge"><?= esc($role['id']) ?></span>
                        </td>

                        <td>
                            <span class="role-badge <?= strtolower(esc($role['name'])) ?>">
                                <?= esc($role['name']) ?>
                            </span>
                        </td>

                        <td>
                            <span style="color:#64748b;font-size:.9rem;">
                                <?= esc($role['description'] ?: 'No description provided.') ?>
                            </span>
                        </td>

                        <?php if ($canViewAuditLog): ?>
                            <td style="text-align:center;">
                                <a class="btn-action-icon btn-action-audit"
                                   title="View Audit Log"
                                   href="<?= base_url('roles/audit-log/'.$role['id']) ?>">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
                                    </svg>
                                </a>
                            </td>
                        <?php endif; ?>

                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:.4rem;justify-content:flex-end;">

                                <a href="<?= base_url('roles/edit/'.$role['id']) ?>"
                                   class="btn-action-icon btn-action-key"
                                   title="Edit Role">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </a>

                                <?php if($role['name'] !== 'Admin'): ?>
                                    <a href="<?= base_url('roles/delete/'.$role['id']) ?>"
                                       class="btn-action-icon btn-action-delete"
                                       title="Delete Role"
                                       onclick="return confirm('Are you sure you want to delete this role?')">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>
    </table>
</div>
        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="roles-pagination">
                <div style="font-weight: 600; color: #475569;">
                    Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?> • <?= $pagination['total'] ?> total roles
                </div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <?php if ($pagination['page'] > 1): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('roles') ?>?page=1">⏮ First</a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('roles') ?>?page=<?= $pagination['page'] - 1 ?>">← Previous</a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i == $pagination['page']): ?>
                            <span class="btn btn-sm btn-primary"><?= $i ?></span>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('roles') ?>?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('roles') ?>?page=<?= $pagination['page'] + 1 ?>">Next →</a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('roles') ?>?page=<?= $pagination['totalPages'] ?>">Last ⏭</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<script>

const search=document.getElementById("roleSearch");

const filter=document.getElementById("roleFilter");

function filterRoles(){

const keyword=search.value.toLowerCase();

const role=filter.value.toLowerCase();

const rows=document.querySelectorAll("#rolesTable tbody tr");

rows.forEach(row=>{

const roleName=row.dataset.role.toLowerCase();

const description=row.dataset.description.toLowerCase();

const searchMatch=

roleName.includes(keyword) ||

description.includes(keyword);

const filterMatch=

role=="" ||

roleName===role;

row.style.display=(searchMatch && filterMatch) ? "" : "none";

});

}

search.addEventListener("keyup",filterRoles);

filter.addEventListener("change",filterRoles);

</script>

<?= $this->endSection() ?>