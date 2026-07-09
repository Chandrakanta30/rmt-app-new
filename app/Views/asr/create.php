<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Create ASR No.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h2 class="form-title" style="margin-bottom: 0;">Create ASR No.</h2>
        <a href="<?= base_url('asrno') ?>" class="btn btn-ghost">← Back to List</a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #c0392b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="protocol-card">
        <form action="<?= base_url('asrno/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="group_name">Group Name</label>
                <input type="text" id="group_name" name="group_name" value="<?= old('group_name') ?>" required placeholder="Enter group name">
            </div>

            <div class="form-group">
                <label for="asr_no">ASR Number</label>
                <input type="text" id="asr_no" name="asr_no" value="<?= old('asr_no') ?>" required placeholder="Enter ASR number">
            </div>

            <div class="form-group">
                <label for="form_id">Form</label>
                <select id="form_id" name="form_id" required>
                    <option value="">Select Approved Form</option>
                    <?php foreach ($approvedForms as $form): ?>
                        <option value="<?= $form['id'] ?>" <?= old('form_id') == $form['id'] ? 'selected' : '' ?>><?= esc($form['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($approvedForms)): ?>
                    <small style="color:#c0392b;">No approved forms available yet.</small>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 2rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Save</button>
                <a href="<?= base_url('asrno') ?>" class="btn btn-ghost" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
