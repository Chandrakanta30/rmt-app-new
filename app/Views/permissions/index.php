<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Permissions Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .perm-header {
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

    .perm-header .eyebrow {
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

    .perm-header h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.02em;
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

    .btn-action-delete {
        background: #fff1f2;
        color: #e11d48;
        border-color: #fecdd3;
    }
    .btn-action-delete:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
        transform: translateY(-2px);
    }

    .perm-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .perm-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .perm-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    .perm-table th {
        padding: 1.05rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #cbd5e1;
        border-bottom: 1px solid #1e293b;
        white-space: nowrap;
    }

    .perm-table td {
        padding: 1.05rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .perm-table tbody tr:hover {
        background: #f8fafc;
    }

    .perm-key-badge {
        display: inline-flex;
        align-items: center;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-family: 'JetBrains Mono', monospace;
    }
</style>

<div class="perm-header">
    <div>
        <span class="eyebrow">System Security</span>
        <h2>Permissions Directory</h2>
    </div>
    <a href="<?= base_url('roles') ?>" class="btn btn-outline-secondary font-weight-600" style="border-radius: 10px; padding: 0.6rem 1.2rem;">← Back to Roles</a>
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

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.75rem; align-items: start;">
    <!-- Permissions Table -->
    <div class="perm-table-card">
        <table class="perm-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Permission Key</th>
                    <th>Description</th>
                    <th style="text-align: right; width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($permissions)): ?>
                    <tr>
                        <td colspan="4" style="padding: 3rem; text-align: center; color: #94a3b8;">No permissions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($permissions as $perm): ?>
                        <tr>
                            <td style="font-weight: 700; color: #475569;"><?= esc($perm['id']) ?></td>
                            <td>
                                <span class="perm-key-badge"><?= esc($perm['name']) ?></span>
                            </td>
                            <td>
                                <span style="color: #64748b; font-size: 0.88rem;"><?= esc($perm['description'] ?: 'No description.') ?></span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= base_url('permissions/delete/' . $perm['id']) ?>" onclick="return confirm('Are you sure you want to delete this permission?')" class="btn-action-icon btn-action-delete" title="Delete Permission">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Permission Card -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.75rem; box-shadow: 0 10px 30px -5px rgba(15,23,42,0.05); position: sticky; top: 100px;">
        <h3 style="color: #0f172a; margin-bottom: 1.25rem; font-size: 1.15rem; font-weight: 800; border-bottom: 2px solid #10b981; padding-bottom: 0.5rem; display: inline-block;">+ Add Permission</h3>
        <form action="<?= base_url('permissions/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group mb-3">
                <label for="name" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">Permission Key</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= old('name') ?>" required placeholder="e.g. view_reports">
            </div>

            <div class="form-group mb-4">
                <label for="description" class="form-label" style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #475569;">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Describe what this permission allows..." required><?= old('description') ?></textarea>
            </div>

            <button type="submit" class="btn btn-success font-weight-700" style="width: 100%; padding: 0.75rem 1rem; border-radius: 10px;">Create Permission</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

