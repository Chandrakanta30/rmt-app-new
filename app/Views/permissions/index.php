<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Permissions Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="form-title" style="margin-bottom: 0;">Permissions Management</h2>
    <a href="<?= base_url('roles') ?>" class="btn btn-ghost">← Back to Roles</a>
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

<?php if(session()->getFlashdata('errors')): ?>
    <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach(session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
    <!-- Permissions Table -->
    <div class="protocol-card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #eef2f6;">
                    <th style="padding: 1rem; width: 60px;">ID</th>
                    <th style="padding: 1rem; width: 220px;">Permission Key</th>
                    <th style="padding: 1rem;">Description</th>
                    <th style="padding: 1rem; text-align: right; width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($permissions)): ?>
                    <tr>
                        <td colspan="4" style="padding: 2rem; text-align: center; color: #7f8c8d;">No permissions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($permissions as $perm): ?>
                        <tr style="border-bottom: 1px solid #eef2f6; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem;"><?= esc($perm['id']) ?></td>
                            <td style="padding: 1rem; font-weight: 600; color: #289672;"><?= esc($perm['name']) ?></td>
                            <td style="padding: 1rem; color: #5a6e7c; font-size: 0.85rem;"><?= esc($perm['description'] ?: 'No description.') ?></td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="<?= base_url('permissions/delete/' . $perm['id']) ?>" onclick="return confirm('Are you sure you want to delete this permission?')" class="btn btn-ghost" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; min-height: auto; border-color: rgba(231,76,60,0.3); color: #e74c3c;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Permission Card -->
    <div class="protocol-card" style="position: sticky; top: 100px;">
        <h3 style="color: #1e4668; margin-bottom: 1.5rem; font-size: 1.1rem; border-bottom: 2px solid #289672; padding-bottom: 0.5rem; display: inline-block;">+ Add Permission</h3>
        <form action="<?= base_url('permissions/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Permission Key</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" required placeholder="e.g. view_reports">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Describe what this permission allows..." required><?= old('description') ?></textarea>
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 1.5rem;">Create Permission</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
