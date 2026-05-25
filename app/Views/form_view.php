<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?><?= esc($form['name']) ?><?= $this->endSection() ?>

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
<div class="page-shell">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Validation form</p>
            <h1><?= esc($form['name']) ?></h1>
            <p class="page-subtitle">Capture and review analytical validation data section by section.</p>
        </div>
        <a class="btn btn-ghost" href="<?= base_url('forms') ?>">All forms</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="form-sections">
        <?php foreach ($sections as $index => $section): ?>
            <?php $layout = strtolower($section['layout'] ?? ''); ?>
            <form class="section-panel" method="post" action="<?= site_url('form/submit') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="table_name[<?= esc($section['id']) ?>]" value="<?= esc($section['table']) ?>">

                <div class="section-panel-header">
                    <div>
                        <span class="section-kicker">Section <?= $index + 1 ?></span>
                        <h2><?= esc($section['title']) ?></h2>
                    </div>
                    <span class="section-count"><?= count($section['fields']) ?> fields</span>
                </div>

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

                <div class="section-actions">
                    <button class="btn btn-primary" type="submit">Save section</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
