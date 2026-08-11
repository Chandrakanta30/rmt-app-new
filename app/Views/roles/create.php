<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Create Role<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.role-container{
    max-width:850px;
    margin:35px auto;
}

.role-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.role-header h2{
    margin:0;
    font-size:32px;
    font-weight:700;
    color:#0f172a;
}

.btn-back{
    padding:10px 18px;
    border:1px solid #d1d5db;
    border-radius:10px;
    background:#fff;
    color:#334155;
    text-decoration:none;
    font-weight:600;
    transition:.2s;
}

.btn-back:hover{
    background:#f8fafc;
}

.role-card{
    background:#fff;
    border-radius:18px;
    border:1px solid #e2e8f0;
    box-shadow:0 20px 40px rgba(15,23,42,.08);
    padding:30px;
}

.form-group{
    margin-bottom:22px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:700;
    color:#475569;
    text-transform:uppercase;
}

.form-group input[type=text],
.form-group textarea{
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:10px;
    padding:12px 15px;
    font-size:15px;
    outline:none;
    transition:.2s;
    box-sizing:border-box;
}

.form-group input:focus,
.form-group textarea:focus{
    border-color:#10b981;
    box-shadow:0 0 0 3px rgba(16,185,129,.15);
}

.permission-title{
    display:block;
    margin-bottom:15px;
    font-size:13px;
    font-weight:700;
    color:#475569;
    text-transform:uppercase;
}

.permission-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:14px;
}

.permission-item{
    display:flex;
    gap:10px;
    align-items:flex-start;
    padding:14px;
    border:1px solid #e2e8f0;
    border-radius:12px;
    cursor:pointer;
    transition:.2s;
}

.permission-item:hover{
    background:#f8fafc;
    border-color:#10b981;
}

.permission-item input{
    margin-top:3px;
}

.permission-name{
    font-size:14px;
    font-weight:600;
    color:#0f172a;
}

.permission-desc{
    font-size:12px;
    color:#64748b;
    margin-top:4px;
}

.form-footer{
    display:flex;
    gap:14px;
    margin-top:30px;
}

.btn-create{
    flex:1;
    border:none;
    border-radius:10px;
    padding:13px;
    font-size:15px;
    font-weight:700;
    color:#fff;
    cursor:pointer;
    background:linear-gradient(135deg,#10b981,#059669);
    box-shadow:0 10px 20px rgba(16,185,129,.25);
    transition:.2s;
}

.btn-create:hover{
    transform:translateY(-2px);
}

.btn-cancel{
    flex:1;
    text-align:center;
    text-decoration:none;
    padding:13px;
    border-radius:10px;
    border:1px solid #d1d5db;
    background:#fff;
    color:#334155;
    font-weight:700;
}

.btn-cancel:hover{
    background:#f8fafc;
}
</style>

<div class="role-container">

    <div class="role-header">
        <h2>Create Role</h2>

        <a href="<?= base_url('roles') ?>" class="btn-back">
            ← Back to List
        </a>
    </div>

    <?php if(session()->getFlashdata('errors')): ?>

        <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:15px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:20px;">
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

    <div class="role-card">

        <form action="<?= base_url('roles/store') ?>" method="POST">

            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Role Name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= old('name') ?>"
                    placeholder="e.g. Quality Analyst"
                    required>
            </div>

            <div class="form-group">

                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Describe this role's access level..."><?= old('description') ?></textarea>

            </div>

            <label class="permission-title">
                Assign Permissions
            </label>

            <div class="permission-grid">

                <?php foreach($permissions as $perm): ?>

                    <label class="permission-item">

                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="<?= $perm['id'] ?>"
                            <?= is_array(old('permissions')) && in_array($perm['id'], old('permissions')) ? 'checked' : '' ?>>

                        <div>

                            <div class="permission-name">
                                <?= esc($perm['name']) ?>
                            </div>

                            <div class="permission-desc">
                                <?= esc($perm['description']) ?>
                            </div>

                        </div>

                    </label>

                <?php endforeach; ?>

            </div>

            <div class="form-footer">

                <button type="submit" class="btn-create">
                    Create
                </button>

                <a href="<?= base_url('roles') ?>" class="btn-cancel">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<?= $this->endSection() ?>