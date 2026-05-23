<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?><?= esc($form['name']) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    /* ========== RECORD HEADER CARD ========== */
    .record-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 28px;
        border: 1px solid #e2edf2;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-table td, .header-table th {
        padding: 14px 18px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .product-title {
        background: #f8fafc;
        font-weight: 700;
        font-size: 1rem;
    }
    .lab-logo-cell {
        background: #eef2ff;
        border-radius: 14px;
        text-align: center;
        font-weight: bold;
        color: #1e4a6e;
    }

    /* ========== ACCORDION ========== */
    .fv-accordion {
        background: white;
        border-radius: 16px;
        margin-bottom: 18px;
        border: 1px solid #e2edf2;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .fv-accordion:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }
    .acc-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 22px;
        background: #ffffff;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.95rem;
        transition: background 0.2s;
        color: #0f172a;
        user-select: none;
    }
    .acc-head:hover {
        background: #f9fafb;
    }
    .acc-head-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .acc-head .section-badge {
        background: #edf8f3;
        color: #15704e;
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .acc-head .toggle {
        font-size: 1.4rem;
        font-weight: 600;
        color: #5b6e8c;
        transition: transform 0.2s;
    }
    .acc-body {
        display: none;
        padding: 20px 24px;
        border-top: 1px solid #edf2f7;
        background: #fefefe;
    }

    /* ========== SIGNATURE ROW ========== */
    .sig-row {
        display: flex;
        gap: 30px;
        margin: 22px 0 10px;
        flex-wrap: wrap;
    }
    .sig-block {
        flex: 1;
        min-width: 200px;
    }
    .sig-block label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
        font-size: 0.8rem;
        color: #2c3e50;
    }
    .sig-block input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.85rem;
        transition: 0.2s;
        background: white;
        margin-bottom: 6px;
    }
    .sig-block input:focus {
        outline: none;
        border-color: #289672;
        box-shadow: 0 0 0 3px rgba(40,150,114,0.18);
    }

    /* ========== SECTION ACTIONS IN ACCORDION ========== */
    .acc-section-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        border-top: 1px solid #edf2f7;
        margin-top: 18px;
        padding-top: 14px;
    }
    .acc-btn-save {
        padding: 8px 22px;
        border-radius: 40px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.82rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #153e5c;
        color: white;
    }
    .acc-btn-save:hover {
        background: #0b2b3b;
    }

    /* ========== FOOTER NOTE ========== */
    .footer-note {
        text-align: center;
        font-size: 0.72rem;
        border-top: 1px solid #e2e8f0;
        padding-top: 1.5rem;
        margin-top: 2rem;
        color: #5c6f87;
S

    /* ========== BACK BUTTON ========== */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #5a6e7c;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 20px;
        transition: color 0.2s;
    }
    .back-link:hover {
        color: #153e5c;
    }

    @media (max-width: 768px) {
        .sig-row { flex-direction: column; gap: 12px; }
        .acc-head { padding: 14px 16px; font-size: 0.88rem; }
        .acc-body { padding: 16px; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $renderTemplateInput = static function (array $field, array $section, array $values): string {
        $validation = json_decode($field['validation'], true) ?? [];
        $value = old('sections.' . $section['id'] . '.' . $field['name'])
            ?? ($values[$section['id']][$field['name']] ?? '');

        $required = !empty($validation['required']) ? ' required' : '';
        $label = esc($field['label'] ?? $field['name']);

        return '<label class="template-field">'
            . '<span>' . $label . '</span>'
            . '<input type="' . esc($field['type']) . '" value="' . esc($value) . '" name="sections[' . esc($section['id']) . '][' . esc($field['name']) . ']"' . $required . '>'
            . '</label>';
    };

    $renderSectionTemplate = static function (string $template, array $section, array $values) use ($renderTemplateInput): string {
        $fieldMap = [];

        foreach ($section['fields'] as $field) {
            $fieldMap[$field['name']] = $field;
        }

        return preg_replace_callback('/\{(.*?)\}/', static function ($matches) use ($fieldMap, $section, $values, $renderTemplateInput) {
            $name = trim($matches[1]);

            if (!isset($fieldMap[$name])) {
                return $matches[0];
            }

            return $renderTemplateInput($fieldMap[$name], $section, $values);
        }, $template);
    };
?>

<!-- BACK LINK -->
<a class="back-link" href="<?= base_url('forms') ?>"><i class="fas fa-arrow-left"></i> All forms</a>

<!-- ==================== HEADER RECORD CARD ==================== -->
<div class="record-card">
    <table class="header-table">
        <tr>
            <td style="width:22%; font-weight:bold;">SMS<br>Central Lab</td>
            <td style="width:22%; font-weight:bold;">Issued by<br>(Sign &amp; Date)</td>
            <td style="width:40%; font-weight:bold; text-align:center;">ANALYTICAL METHOD<br>VALIDATION RECORD<br><?= strtoupper(esc($form['name'])) ?></td>
           <td style="width: 33%; text-align: right; vertical-align: top;">
            <img src="rmt-app-new\public\images\smslogo.jpg" alt="SMS Pharma Logo" style="height: 10px;">
           </td>
        </tr>
        <tr><td colspan="4" class="product-title">PRODUCT NAME: <?= esc($form['name'] ?? '—') ?></td></tr>
        <tr style="background:#f8fafc;">
            <td><strong>Parameter</strong></td>
            <td><strong>Protocol No.</strong></td>
            <td><strong>Date of Analysis</strong></td>
            <td><strong>Page No.</strong></td>
        </tr>
        <tr>
            <td><?= esc($form['name']) ?></td>
            <td><?= esc($form['form_key'] ?? '—') ?></td>
            <td><input type="date" id="globalDate" style="width:auto;"></td>
            <td>01 of <?= str_pad(count($sections), 2, '0', STR_PAD_LEFT) ?></td>
        </tr>
    </table>
</div>

<!-- FLASH MESSAGES -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success" style="border-radius:12px; padding:0.85rem 1rem; margin-bottom:18px; background:#ecfdf5; border:1px solid #b7e4cf; color:#126246;">
        <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error" style="border-radius:12px; padding:0.85rem 1rem; margin-bottom:18px; background:#fff1f2; border:1px solid #fecdd3; color:#9f1239;">
        <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<!-- ==================== ACCORDION SECTIONS ==================== -->
<?php foreach ($sections as $index => $section): ?>
    <?php $layout = strtolower($section['layout'] ?? ''); ?>
    <div class="fv-accordion" data-section="section-<?= esc($section['id']) ?>">
        <div class="acc-head">
            <div class="acc-head-left">
                <span><?= ($index + 1) ?>. <?= esc($section['title']) ?></span>
                <span class="section-badge"><?= count($section['fields']) ?> fields</span>
            </div>
            <span class="toggle">+</span>
        </div>
        <div class="acc-body">
            <form method="post" action="<?= site_url('form/submit') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="table_name[<?= esc($section['id']) ?>]" value="<?= esc($section['table']) ?>">

                <?php if ($layout === 'inline'): ?>
                    <div class="inline-template">
                        <?php
                            echo nl2br($renderSectionTemplate($section['inline_template'] ?? '', $section, $values));
                        ?>
                    </div>
                <?php elseif (in_array($layout, ['tabular', 'table'], true)): ?>
                    <div class="table-template">
                        <?php
                            $template = $section['table_template'] ?? $section['inline_template'] ?? '';
                            echo $renderSectionTemplate($template, $section, $values);
                        ?>
                    </div>
                <?php else: ?>
                    <div class="field-grid">
                        <?php foreach ($section['fields'] as $field): ?>
                            <?php
                                $validation = json_decode($field['validation'], true) ?? [];
                                $value = old('sections.' . $section['id'] . '.' . $field['name'])
                                    ?? ($values[$section['id']][$field['name']] ?? '');
                            ?>
                            <label class="form-group">
                                <span><?= esc($field['label']) ?></span>
                                <input
                                    type="<?= esc($field['type']) ?>"
                                    name="sections[<?= esc($section['id']) ?>][<?= esc($field['name']) ?>]"
                                    value="<?= esc($value) ?>"
                                    <?= !empty($validation['required']) ? 'required' : '' ?>
                                >
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Signature Row -->
                <div class="sig-row">
                    <div class="sig-block"><label>Analyzed by:</label><input type="text" placeholder="Name"><input type="date"></div>
                    <div class="sig-block"><label>Checked by:</label><input type="text" placeholder="Name"><input type="date"></div>
                </div>

                <!-- Save Button -->
                <div class="acc-section-actions">
                    <button class="acc-btn-save" type="submit"><i class="fas fa-save"></i> Save Section</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php if (empty($sections)): ?>
    <div class="fv-accordion">
        <div class="acc-head">
            <span>No sections configured</span>
            <span class="toggle">+</span>
        </div>
        <div class="acc-body" style="display:block; text-align:center; color:#607184;">
            <p>This form has no sections yet. Add sections in the database to see them here.</p>
        </div>
    </div>
<?php endif; ?>

<!-- FOOTER NOTE -->
<div class="footer-note">
    <p>This is a controlled document of SMS Pharmaceuticals Ltd. Unauthorized copying or distribution is prohibited.</p>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ==================== ACCORDION INITIALIZATION ====================
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fv-accordion').forEach(acc => {
            const head = acc.querySelector('.acc-head');
            const body = acc.querySelector('.acc-body');
            const toggleSpan = acc.querySelector('.toggle');
            if (head && body) {
                // Start closed
                if (body.style.display !== 'block') {
                    body.style.display = 'none';
                }
                if (toggleSpan && body.style.display === 'none') {
                    toggleSpan.textContent = '+';
                }
                head.addEventListener('click', (e) => {
                    // Don't toggle if clicking inside a form element
                    if (e.target.closest('input, select, textarea, button')) return;
                    e.stopPropagation();
                    const isOpen = body.style.display === 'block';
                    body.style.display = isOpen ? 'none' : 'block';
                    if (toggleSpan) toggleSpan.textContent = isOpen ? '+' : '−';
                });
            }
        });

        // Restore saved global date
        const savedDate = localStorage.getItem('formview_globalDate');
        if (savedDate && document.getElementById('globalDate')) {
            document.getElementById('globalDate').value = savedDate;
        }
    });

    // Save global date
    document.getElementById('globalDate')?.addEventListener('change', (e) => {
        localStorage.setItem('formview_globalDate', e.target.value);
    });
</script>
<?= $this->endSection() ?>
