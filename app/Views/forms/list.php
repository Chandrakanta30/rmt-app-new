<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Forms<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-shell">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Form library</p>
            <h1>Validation forms</h1>
            <p class="page-subtitle">Open a form to enter, update, or review validation records.</p>
        </div>
    </div>

    <?php if (empty($forms)): ?>
        <div class="empty-state">
            <h2>No forms found</h2>
            <p>Forms will appear here once they are available in the system.</p>
        </div>
    <?php else: ?>
        <div class="forms-toolbar">
            <label class="search-box">
                <span>Search forms</span>
                <input id="formSearch" type="search" placeholder="Type a form name...">
            </label>
            <div class="forms-total"><?= count($forms) ?> total</div>
        </div>

        <div class="forms-grid" id="formsGrid">
            <?php foreach ($forms as $form): ?>
                <article class="form-card" data-form-card data-form-name="<?= esc(strtolower($form['name'] ?? '')) ?>">
                    <div class="form-card-top">
                        <div class="form-avatar"><?= esc(strtoupper(substr($form['name'] ?? 'F', 0, 1))) ?></div>
                        <span class="status-pill">Ready</span>
                    </div>
                    <h2><?= esc($form['name']) ?></h2>
                    <p><?= esc($form['form_key']) ?></p>
                    <div class="form-card-meta">
                        <span><?= (int) ($form['section_count'] ?? 0) ?> sections</span>
                        <span>ID <?= esc($form['id']) ?></span>
                    </div>
                    <a class="btn btn-primary" href="<?= base_url('form/' . $form['form_key']) ?>">Open form</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('formSearch')?.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        document.querySelectorAll('[data-form-card]').forEach(function (card) {
            card.hidden = !card.dataset.formName.includes(query);
        });
    });
</script>
<?= $this->endSection() ?>