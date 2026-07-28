<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Instrument Method<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .instrument-page {
        width: 100%;
    }

    .instrument-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
    }

    .instrument-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.5rem;
        background: #0f172a;
        border-bottom: 1px solid #1e293b;
        color: #f8fafc;
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .instrument-card__collapse {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #cbd5e1;
        background: #1e293b;
        font-size: 1.2rem;
        line-height: 1;
    }

    .instrument-card__body {
        padding: 1.75rem;
    }

    .instrument-section {
        margin-bottom: 1.5rem;
    }

    .instrument-section:last-child {
        margin-bottom: 0;
    }

    .instrument-section__title {
        margin-bottom: 0.9rem;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
        border-left: 4px solid #10b981;
        padding-left: 0.75rem;
    }

    .instrument-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    .instrument-table th,
    .instrument-table td {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .instrument-table tr:last-child th,
    .instrument-table tr:last-child td {
        border-bottom: 0;
    }

    .instrument-table th:last-child,
    .instrument-table td:last-child {
        border-right: 0;
    }

    .instrument-label {
        width: 30%;
        min-width: 260px;
        padding: 0.95rem 1rem;
        background: #f8fafc;
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 700;
        text-align: left;
    }

    .instrument-field {
        padding: 0.65rem 0.8rem;
        background: #fff;
    }

    .instrument-input {
        width: 100%;
        min-height: 40px;
        padding: 0.5rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #0f172a;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.15s ease-in-out;
    }

    .instrument-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$instrumentTitle = $instrumentTitle ?? 'Instrument Method';
$instrumentSections = $instrumentSections ?? [];
$instrumentValues = $instrumentValues ?? [];

$renderField = static function (array $field, array $values): string {
    $name = (string) ($field['name'] ?? '');
    $value = old('instrument.' . $name) ?? ($values[$name] ?? ($field['default'] ?? ''));

    return '<th class="instrument-label">' . esc($field['label'] ?? '') . '</th>'
        . '<td class="instrument-field" colspan="' . max(1, (int) ($field['span'] ?? 1)) . '">'
        . '<input class="instrument-input form-control" type="text" name="instrument[' . esc($name) . ']" value="' . esc($value) . '">'
        . '</td>';
};
?>

<div class="instrument-page">
    <div class="instrument-card">
        <div class="instrument-card__header">
            <span>3. <?= esc($instrumentTitle) ?></span>
            <span class="instrument-card__collapse" aria-hidden="true">&minus;</span>
        </div>

        <div class="instrument-card__body">
            <?php foreach ($instrumentSections as $section): ?>
                <section class="instrument-section">
                    <h2 class="instrument-section__title"><?= esc($section['title'] ?? '') ?></h2>

                    <table class="instrument-table" role="presentation">
                        <tbody>
                            <?php foreach (($section['rows'] ?? []) as $row): ?>
                                <tr class="instrument-grid-row<?= count($row) === 1 ? ' instrument-grid-row--single' : '' ?>">
                                    <?php foreach ($row as $field): ?>
                                        <?= $renderField($field, $instrumentValues) ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

