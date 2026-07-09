<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Edit User<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h2 class="form-title" style="margin-bottom: 0;">Edit User</h2>
        <a href="<?= base_url('users') ?>" class="btn btn-ghost">← Back to List</a>
    </div>

    <?php if(session()->getFlashdata('errors')): ?>
        <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="protocol-card">
        <form action="<?= base_url('users/update/' . $user['id']) ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= old('name', $user['name']) ?>" required placeholder="Enter full name">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= old('email', $user['email']) ?>" required placeholder="Enter email address">
            </div>

            <div class="form-group">
                <label for="password">Password <span style="font-size: 0.75rem; color: #7f8c8d; text-transform: none;">(Leave blank to keep current password)</span></label>
                <input type="password" id="password" name="password" placeholder="Enter new password to change">
            </div>

            <div class="form-group">
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id">
                    <option value="">Select Role</option>
                    <?php foreach($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= old('role_id', $user['role_id']) == $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 2rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Update User</button>
                <a href="<?= base_url('users') ?>" class="btn btn-ghost" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
