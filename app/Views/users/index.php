<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Users Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">Users Management</h2>
    <a href="<?= base_url('users/create') ?>" class="btn btn-success">+ Add New User</a>
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
                <th style="padding: 1rem;">ID</th>
                <th style="padding: 1rem;">Name</th>
                <th style="padding: 1rem;">Email</th>
                <th style="padding: 1rem;">Role</th>
                <th style="padding: 1rem;">Created At</th>
                <th style="padding: 1rem; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($users)): ?>
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: #7f8c8d;">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach($users as $user): ?>
                    <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem;"><?= esc($user['id']) ?></td>
                        <td style="padding: 1rem; font-weight: 500;"><?= esc($user['name']) ?></td>
                        <td style="padding: 1rem; color: #5a6e7c;"><?= esc($user['email']) ?></td>
                        <td style="padding: 1rem;">
                            <span style="background: <?= $user['role_name'] === 'Admin' ? 'rgba(231,76,60,0.1)' : ($user['role_name'] === 'Reviewer' ? 'rgba(40,150,114,0.1)' : 'rgba(21,62,92,0.1)') ?>; color: <?= $user['role_name'] === 'Admin' ? '#e74c3c' : ($user['role_name'] === 'Reviewer' ? '#289672' : '#153e5c') ?>; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                <?= esc($user['role_name'] ?: 'No Role') ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; color: #7f8c8d;"><?= date('d-m-Y H:i', strtotime($user['created_at'])) ?></td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-ghost" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; min-height: auto;">Edit</a>
                                <a href="<?= base_url('users/delete/' . $user['id']) ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="btn btn-ghost" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; min-height: auto; border-color: rgba(231,76,60,0.3); color: #e74c3c;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
