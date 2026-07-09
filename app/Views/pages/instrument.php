<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Instrument Method<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .instrument-page {
        width: 100%;
    }

    .instrument-card {
        background: #ffffff;
        border: 1px solid #dbe4ee;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .instrument-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.5rem;
        background: linear-gradient(180deg, #f4f8fc 0%, #eef3f9 100%);
        border-bottom: 1px solid #dbe4ee;
        color: #0f2742;
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .instrument-card__collapse {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 10px;
        color: #42546a;
        font-size: 1.3rem;
        line-height: 1;
    }

    .instrument-card__body {
        padding: 1.5rem;
    }

    .instrument-section {
        margin-bottom: 1.35rem;
    }

    .instrument-section:last-child {
        margin-bottom: 0;
    }

    .instrument-section__title {
        margin-bottom: 0.9rem;
        color: #0f2742;
        font-size: 0.96rem;
        font-weight: 800;
    }

    .instrument-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        border: 1px solid #dbe4ee;
        background: #fff;
    }

    .instrument-table th,
    .instrument-table td {
        border-right: 1px solid #dbe4ee;
        border-bottom: 1px solid #dbe4ee;
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
        background: #eef3ff;
        color: #0f2742;
        font-size: 0.92rem;
        font-weight: 700;
        text-align: left;
    }

    .instrument-field {
        padding: 0.72rem 0.8rem;
        background: #fff;
    }

    .instrument-input {
        width: 100%;
        min-height: 44px;
        padding: 0.68rem 0.85rem;
        border: 1px solid #c9d7e6;
        border-radius: 16px;
        background: #ffffff;
        color: #12263a;
        font-size: 0.92rem;
        outline: none;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .instrument-input:focus {
        border-color: #7ea6d8;
        box-shadow: 0 0 0 3px rgba(126, 166, 216, 0.16);
    }

    .instrument-input::placeholder {
        color: #8a98a8;
    }

    .instrument-grid-row {
        width: 100%;
    }

    .instrument-grid-row td {
        width: 25%;
    }

    .instrument-grid-row--wide td:first-child {
        width: 30%;
    }

    .instrument-grid-row--wide td:last-child {
        width: 70%;
    }

    .instrument-grid-row--single td:first-child {
        width: 30%;
    }

    .instrument-grid-row--single td:last-child {
        width: 70%;
    }

    @media (max-width: 980px) {
        .instrument-card__body {
            padding: 1rem;
        }

        .instrument-table,
        .instrument-table tbody,
        .instrument-table tr,
        .instrument-table th,
        .instrument-table td {
            display: block;
            width: 100%;
        }

        .instrument-table th,
        .instrument-table td {
            border-right: 0;
        }

        .instrument-table tr {
            border-bottom: 1px solid #dbe4ee;
        }

        .instrument-table tr:last-child {
            border-bottom: 0;
        }

        .instrument-label {
            min-width: 0;
            border-bottom: 0;
        }

        .instrument-field {
            border-bottom: 1px solid #dbe4ee;
        }

        .instrument-grid-row--wide td:last-child,
        .instrument-grid-row--single td:last-child {
            width: 100%;
        }
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
        . '<input class="instrument-input" type="text" name="instrument[' . esc($name) . ']" value="' . esc($value) . '">'
        . '</td>';
};
?>

<div class="instrument-page">
    <div class="instrument-card">
        <div class="instrument-card__header">
            <span>3. <?= esc($instrumentTitle) ?></span>
            <span class="instrument-card__collapse" aria-hidden="true">-</span>
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
