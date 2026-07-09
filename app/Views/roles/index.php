<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Roles & Permissions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">Roles Management</h2>
    <div style="display: flex; gap: 10px;">
        <a href="<?= base_url('permissions') ?>" class="btn btn-ghost">Manage Permissions Direct</a>
        <a href="<?= base_url('roles/create') ?>" class="btn btn-success">+ Add New Role</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(40,150,114,0.15); border: 1px solid rgba(40,150,114,0.3); color: #1e6f5c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="protocol-card" style="padding: 0; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                <th style="padding: 1rem; width: 80px;">ID</th>
                <th style="padding: 1rem; width: 200px;">Role Name</th>
                <th style="padding: 1rem;">Description</th>
                <th style="padding: 1rem; text-align: right; width: 200px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($roles)): ?>
                <tr>
                    <td colspan="4" style="padding: 2rem; text-align: center; color: #7f8c8d;">No roles found.</td>
                </tr>
            <?php else: ?>
                <?php foreach($roles as $role): ?>
                    <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem;"><?= esc($role['id']) ?></td>
                        <td style="padding: 1rem; font-weight: 500;"><?= esc($role['name']) ?></td>
                        <td style="padding: 1rem; color: #5a6e7c;"><?= esc($role['description'] ?: 'No description provided.') ?></td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="<?= base_url('roles/edit/' . $role['id']) ?>" class="btn btn-ghost" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; min-height: auto;">Edit & Permissions</a>
                                <?php if($role['name'] !== 'Admin'): ?>
                                    <a href="<?= base_url('roles/delete/' . $role['id']) ?>" onclick="return confirm('Are you sure you want to delete this role?')" class="btn btn-ghost" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; min-height: auto; border-color: rgba(231,76,60,0.3); color: #e74c3c;">Delete</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
