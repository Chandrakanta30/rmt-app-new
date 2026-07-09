<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h2 class="form-title">Welcome to SMS Lab Dashboard</h2>
<p style="color: #5a6e7c; margin-bottom: 1.5rem;">
    Select a validation parameter from the sidebar to view and fill the respective form.
</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
        <h3 style="color: #1e4668; margin-bottom: 0.5rem;">📋 Recent Activity</h3>
        <ul style="list-style: none;">
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">• Accuracy validation completed</li>
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">• Linearity test in progress</li>
            <li style="padding: 0.5rem 0;">• System suitability passed</li>
        </ul>
    </div>
    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
        <h3 style="color: #1e4668; margin-bottom: 0.5rem;">📊 Pending Tasks</h3>
        <ul style="list-style: none;">
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">• Method Precision verification</li>
            <li style="padding: 0.5rem 0;">• LOD establishment report</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h2 class="form-title">Welcome to SMS Lab Dashboard</h2>
<p style="color: #5a6e7c; margin-bottom: 1.5rem;">
    Select a validation parameter from the sidebar to view and fill the respective form.
</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
        <h3 style="color: #1e4668; margin-bottom: 0.5rem;">📋 Recent Activity</h3>
        <ul style="list-style: none;">
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">• Accuracy validation completed</li>
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">• Linearity test in progress</li>
            <li style="padding: 0.5rem 0;">• System suitability passed</li>
        </ul>
    </div>
    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
        <h3 style="color: #1e4668; margin-bottom: 0.5rem;">📊 Pending Tasks</h3>
        <ul style="list-style: none;">
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">• Method Precision verification</li>
            <li style="padding: 0.5rem 0;">• LOD establishment report</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>