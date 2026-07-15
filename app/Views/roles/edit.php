<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Edit Role<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h2 class="form-title" style="margin-bottom: 0;">Edit Role: <?= esc($role['name']) ?></h2>
        <a href="<?= base_url('roles') ?>" class="btn btn-ghost">← Back to List</a>
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
        <form action="<?= base_url('roles/update/' . $role['id']) ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Role Name</label>
                <input type="text" id="name" name="name" value="<?= old('name', $role['name']) ?>" required placeholder="e.g. QualityAnalyst" <?= $role['name'] === 'Admin' ? 'readonly' : '' ?>>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Describe this role's access level..."><?= old('description', $role['description']) ?></textarea>
            </div>

            <div class="form-group" style="margin-top: 2rem;">
                <label style="font-weight: 600; font-size: 0.95rem; margin-bottom: 1rem; border-bottom: 1px solid #eef2f6; padding-bottom: 0.5rem;">Assign Permissions</label>
                <?php if($role['name'] === 'Admin'): ?>
                    <div style="background: rgba(40,150,114,0.1); border: 1px solid rgba(40,150,114,0.3); color: #1e6f5c; padding: 1rem; border-radius: 8px; font-size: 0.85rem;">
                        <strong>Note:</strong> The Admin role inherently has all permissions. Direct modifications are not required.
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                        <?php foreach($permissions as $perm): ?>
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-weight: normal; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>" style="width: auto; margin-top: 3px;" <?= in_array($perm['id'], $rolePermissionIds) ? 'checked' : '' ?>>
                                <div>
                                    <span style="font-weight: 500; font-size: 0.85rem; color: #1e4668;"><?= esc($perm['name']) ?></span>
                                    <div style="font-size: 0.75rem; color: #7f8c8d;"><?= esc($perm['description']) ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 2.5rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Update</button>
                <a href="<?= base_url('roles') ?>" class="btn btn-ghost" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
