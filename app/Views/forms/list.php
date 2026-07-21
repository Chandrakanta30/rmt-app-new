<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Forms<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php helper('workflow'); ?>
<style>
    .forms-table-wrap {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    table.forms-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 560px;
    }

    table.forms-table thead tr {
        background: #0f172a;
        color: #f8fafc;
    }

    table.forms-table thead th {
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #cbd5e1;
        font-weight: 800;
        padding: 1.05rem 1.25rem;
        border-bottom: 1px solid #1e293b;
    }

    table.forms-table tbody td {
        padding: 1.05rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    table.forms-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.forms-table tbody tr:hover {
        background: #f8fafc;
    }

    .form-name-cell {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        font-weight: 700;
        color: #0f172a;
    }

    .form-avatar-sm {
        width: 38px;
        height: 38px;
        flex: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        display: grid;
        place-items: center;
        font-weight: 800;
        font-size: 0.95rem;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }

    .form-name-cell small {
        display: block;
        color: #64748b;
        font-weight: 500;
        font-size: 0.78rem;
        margin-top: 2px;
        font-family: 'JetBrains Mono', monospace;
    }

    .status-badge {
        display: inline-block;
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .status-badge.status-created                  { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .status-badge.status-pending_review           { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-resubmitted_for_review   { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-pending_approval         { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-resubmitted_for_approval { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-badge.status-review_rejected          { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .status-badge.status-approval_rejected        { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .status-badge.status-review_completed         { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .status-badge.status-approved                 { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }

    .status-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .action-cell {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 9px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        outline: none;
    }

    .btn-action-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-action-view {
        background: #ecfdf5;
        color: #059669;
        border-color: #a7f3d0;
    }
    .btn-action-view:hover {
        background: #10b981;
        color: #ffffff;
        border-color: #10b981;
    }

    .btn-action-audit {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .btn-action-audit:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .forms-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .no-match-row td {
        text-align: center;
        color: #94a3b8;
        padding: 2.5rem 1rem;
    }
</style>

<div class="page-shell">
    <div class="page-heading mb-4">
        <div>
            <p class="eyebrow" style="color: #10b981; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Form library</p>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: #0f172a;">Validation Forms</h1>
            <p class="page-subtitle" style="color: #64748b; font-size: 0.9rem;">Open a form to enter, update, or review validation records.</p>
        </div>
    </div>

    <?php if (empty($forms)): ?>
        <div class="empty-state" style="background: white; border-radius: 16px; padding: 3rem; text-align: center; border: 1px solid #e2e8f0;">
            <h2 style="color: #0f172a; font-weight: 800;">No forms found</h2>
            <p style="color: #64748b;">Forms will appear here once they are available in the system.</p>
        </div>
    <?php else: ?>
        <div class="forms-toolbar">
            <label class="search-box mb-0" style="flex: 1;">
                <span style="font-weight: 700; font-size: 0.78rem; text-transform: uppercase; color: #475569; display: block; margin-bottom: 0.35rem;">Search forms</span>
                <input id="formSearch" type="search" class="form-control" placeholder="Type a form name...">
            </label>
            <label class="status-filter mb-0" style="width: 220px;">
                <span style="font-weight: 700; font-size: 0.78rem; text-transform: uppercase; color: #475569; display: block; margin-bottom: 0.35rem;">Filter by status</span>
                <select id="statusFilter" class="form-select">
                    <option value="">All statuses</option>
                    <?php foreach (workflow_statuses() as $slug => $label): ?>
                        <option value="<?= esc($slug) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="forms-total ms-auto" style="font-weight: 700; color: #475569; background: #f1f5f9; padding: 0.5rem 0.85rem; border-radius: 8px; font-size: 0.85rem;">
                <?= count($forms) ?> Total Forms
            </div>
        </div>

        <div class="forms-table-wrap">
            <table class="forms-table" id="formsTable">
                <thead>
                    <tr>
                        <th>Form Name</th>
                        <th>Status</th>
                        <th style="width: 110px; text-align: right;">Action</th>
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
                                <div class="action-cell" style="justify-content: flex-end;">
                                    <a class="btn-action-icon btn-action-view" title="View Form" aria-label="View Form"
                                        href="<?= base_url('form/' . $form['form_key'] . '?mode=view') ?>">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a class="btn-action-icon btn-action-audit" title="Audit Log" aria-label="Audit Log"
                                        href="<?= base_url('forms/logs/' . $form['id']) ?>">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </a>
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
