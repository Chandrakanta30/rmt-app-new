<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Forms<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php helper('workflow'); ?>
<style>
    .forms-table-wrap {
        background: white;
        border: 1px solid #dfe7ee;
        border-radius: 8px;
        box-shadow: 0 16px 40px rgba(15,23,42,0.06);
        overflow-x: auto;
    }

    table.forms-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 560px;
    }

    table.forms-table thead th {
        text-align: left;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #46596b;
        font-weight: 700;
        padding: 0.9rem 1.1rem;
        border-bottom: 1px solid #e6edf3;
        background: #f7fafc;
    }

    table.forms-table tbody td {
        padding: 0.85rem 1.1rem;
        border-bottom: 1px solid #eef3f7;
        color: #28394b;
        font-size: 0.92rem;
        vertical-align: middle;
    }

    table.forms-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.forms-table tbody tr:hover {
        background: #fafcfe;
    }

    .form-name-cell {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        font-weight: 600;
        color: #12263a;
    }

    .form-avatar-sm {
        width: 34px;
        height: 34px;
        flex: none;
        border-radius: 8px;
        background: #edf8f3;
        color: #15704e;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .form-name-cell small {
        display: block;
        color: #7a8a99;
        font-weight: 500;
        font-size: 0.76rem;
    }

    .status-badge {
        display: inline-block;
        border-radius: 999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    /* neutral = not started, amber = waiting on somebody, red = sent back, green = done */
    .status-badge.status-created                  { background: #eef2f7; color: #475569; }
    .status-badge.status-pending_review           { background: #fff5e6; color: #b26a00; }
    .status-badge.status-resubmitted_for_review   { background: #fff5e6; color: #b26a00; }
    .status-badge.status-pending_approval         { background: #fff5e6; color: #b26a00; }
    .status-badge.status-resubmitted_for_approval { background: #fff5e6; color: #b26a00; }
    .status-badge.status-review_rejected          { background: #fdeeee; color: #b42318; }
    .status-badge.status-approval_rejected        { background: #fdeeee; color: #b42318; }
    .status-badge.status-review_completed         { background: #eef4ff; color: #1d4ed8; }
    .status-badge.status-approved                 { background: #edf8f3; color: #15704e; }

    .status-cell {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .action-cell {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .status-filter {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .status-filter span {
        color: #46596b;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .status-filter select {
        min-height: 42px;
        padding: 0.5rem 0.7rem;
        border: 1px solid #cbd7e2;
        border-radius: 8px;
        background: #fbfdff;
        color: #172033;
        font-size: 0.92rem;
    }

    .status-filter select:focus {
        outline: none;
        border-color: #289672;
        box-shadow: 0 0 0 3px rgba(40,150,114,0.14);
        background: white;
    }

    .forms-toolbar {
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .no-match-row td {
        text-align: center;
        color: #7a8a99;
        padding: 1.6rem 1rem;
    }

    .icon-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fbfdff;
        color: #34495e;
        font-size: 0.95rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
    }

    .icon-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15,23,42,0.1);
    }

    .icon-action.icon-view {
        color: #1f6fb2;
        border-color: rgba(31,111,178,0.25);
        background: rgba(31,111,178,0.08);
    }

    .icon-action.icon-audit {
        color: #153e5c;
        border-color: rgba(21,62,92,0.25);
        background: rgba(21,62,92,0.08);
    }
</style>

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
            <label class="status-filter">
                <span>Filter by status</span>
                <select id="statusFilter">
                    <option value="">All statuses</option>
                    <?php foreach (workflow_statuses() as $slug => $label): ?>
                        <option value="<?= esc($slug) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="forms-total"><?= count($forms) ?> total</div>
        </div>

        <div class="forms-table-wrap">
            <table class="forms-table" id="formsTable">
                <thead>
                    <tr>
                        <th>Form name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <?php $status = strtolower($form['status'] ?? 'created'); ?>
                        <tr data-form-row
                            data-form-name="<?= esc(strtolower($form['name'] ?? '')) ?>"
                            data-form-status="<?= esc($status) ?>">
                            <td>
                                <div class="form-name-cell">
                                    <span class="form-avatar-sm"><?= esc(strtoupper(substr($form['name'] ?? 'F', 0, 1))) ?></span>
                                    <span>
                                        <?= esc($form['name']) ?>
                                        <small><?= esc($form['form_key']) ?></small>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="status-cell">
                                    <span class="status-badge status-<?= esc($status) ?>"><?= esc(workflow_status_label($status)) ?></span>
                                    <?= workflow_action_buttons($form) ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a class="icon-action icon-view" title="View" aria-label="View"
                                        href="<?= base_url('form/' . $form['form_key'] . '?mode=view') ?>">&#128065;</a>
                                    <a class="icon-action icon-audit" title="Audit log" aria-label="Audit log"
                                        href="<?= base_url('forms/logs/' . $form['id']) ?>">&#128337;</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="no-match-row" id="noMatchRow" hidden>
                        <td colspan="3">No forms match your filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('partials/workflow_modal') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const searchInput = document.getElementById('formSearch');
        const statusSelect = document.getElementById('statusFilter');
        const rows = Array.from(document.querySelectorAll('[data-form-row]'));
        const noMatchRow = document.getElementById('noMatchRow');

        function applyFilters() {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const status = statusSelect?.value || '';
            let visible = 0;

            rows.forEach(function (row) {
                const matchesName = row.dataset.formName.includes(query);
                const matchesStatus = !status || row.dataset.formStatus === status;
                const show = matchesName && matchesStatus;
                row.hidden = !show;
                if (show) visible++;
            });

            if (noMatchRow) noMatchRow.hidden = visible !== 0;
        }

        searchInput?.addEventListener('input', applyFilters);
        statusSelect?.addEventListener('change', applyFilters);
    })();
</script>
<?= $this->endSection() ?>
