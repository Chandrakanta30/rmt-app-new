<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?><?= esc($form['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $isTextLikeType = static function (string $type): bool {
        return in_array(strtolower($type), ['text', 'search', 'tel', 'url', 'email'], true);
    };

    $specialCharAttrs = static function (array $field) use ($isTextLikeType): string {
        if (!$isTextLikeType((string) ($field['type'] ?? 'text'))) {
            return '';
        }

        return ' pattern="[A-Za-z0-9\\s]*" title="Only letters, numbers, and spaces are allowed." data-no-special="1"';
    };

    $renderTemplateInput = static function (array $field, array $section, array $values) use ($specialCharAttrs): string {
        $validation = json_decode($field['validation'], true) ?? [];
        $value = old('sections.' . $section['id'] . '.' . $field['name'])
            ?? ($values[$section['id']][$field['name']] ?? '');

        $required = !empty($validation['required']) ? ' required' : '';
        $name     = 'sections[' . esc($section['id']) . '][' . esc($field['name']) . ']';
        $type     = strtolower($field['type'] ?? 'text');

        if ($type === 'textarea') {
            return '<span class="template-field template-field--textarea"><textarea name="' . $name . '"' . $required . '>' . esc($value) . '</textarea></span>';
        }

        $specialValidation = $specialCharAttrs($field);
        return '<span class="template-field"><input type="' . esc($type) . '" value="' . esc($value) . '" name="' . $name . '"' . $required . $specialValidation . '></span>';
    };

    // Parses one CSV line respecting quoted fields
    $parseCsvLine = static function (string $line): array {
        $cells  = [];
        $cell   = '';
        $quoted = false;
        $len    = strlen($line);

        for ($i = 0; $i < $len; $i++) {
            $char = $line[$i];
            $next = $line[$i + 1] ?? '';

            if ($char === '"' && $next === '"') {
                $cell .= '"';
                $i++;
            } elseif ($char === '"') {
                $quoted = !$quoted;
            } elseif ($char === ',' && !$quoted) {
                $cells[] = trim($cell);
                $cell    = '';
            } else {
                $cell .= $char;
            }
        }

        $cells[] = trim($cell);
        return $cells;
    };

    // Renders a CSV table template as an HTML <table>
    // Cell syntax: plain text → <th>/<td> label
    //              [fieldName] or [fieldName|input] → input field
    //              [fieldName|label] or [fieldName|header] → read-only text
    $renderTableTemplate = static function (string $template, array $section, array $values) use ($renderTemplateInput, $parseCsvLine): string {
        $fieldMap      = [];
        $fieldMapLower = [];

        foreach ($section['fields'] as $field) {
            $fieldMap[$field['name']]                  = $field;
            $fieldMapLower[strtolower($field['name'])] = $field;
        }

        $renderCell = static function (string $raw) use ($fieldMap, $fieldMapLower, $section, $values, $renderTemplateInput): string {
            $trimmed = trim($raw);

            if ($trimmed === '') {
                return '';
            }

            // [fieldName] or [fieldName|type] syntax
            if (preg_match('/^\[(.+)\]$/', $trimmed, $m)) {
                $parts    = array_map('trim', explode('|', $m[1]));
                $fieldKey = $parts[0];
                $cellType = strtolower($parts[1] ?? 'input');

                if ($cellType === 'label' || $cellType === 'header') {
                    return esc($fieldKey);
                }

                $field = $fieldMap[$fieldKey]
                    ?? $fieldMapLower[strtolower($fieldKey)]
                    ?? null;

                if ($field) {
                    return $renderTemplateInput($field, $section, $values);
                }
            }

            // {fieldName} curly-brace syntax (e.g. {input_1})
            if (preg_match('/^\{(.+)\}$/', $trimmed, $m)) {
                $fieldKey = trim($m[1]);
                $field = $fieldMap[$fieldKey]
                    ?? $fieldMapLower[strtolower($fieldKey)]
                    ?? null;
                if ($field) {
                    return $renderTemplateInput($field, $section, $values);
                }
            }

            // Plain field name without brackets (e.g. "input_1" stored directly)
            $field = $fieldMap[$trimmed]
                ?? $fieldMapLower[strtolower($trimmed)]
                ?? null;

            if ($field) {
                return $renderTemplateInput($field, $section, $values);
            }

            return esc($trimmed);
        };

        $lines = array_values(array_filter(
            explode("\n", str_replace("\r\n", "\n", $template)),
            fn ($l) => trim($l) !== ''
        ));

        if (empty($lines)) {
            return '';
        }

        $html        = '<table>';
        $headerCells = $parseCsvLine($lines[0]);
        $html       .= '<thead><tr>';

        foreach ($headerCells as $cell) {
            $html .= '<th>' . esc(trim($cell)) . '</th>';
        }

        $html .= '</tr></thead>';

        if (count($lines) > 1) {
            $html .= '<tbody>';

            for ($i = 1; $i < count($lines); $i++) {
                $cells  = $parseCsvLine($lines[$i]);
                $html  .= '<tr>';

                foreach ($cells as $cell) {
                    $html .= '<td>' . $renderCell($cell) . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody>';
        }

        $html .= '</table>';

        return $html;
    };

    $renderSectionTemplate = static function (string $template, array $section, array $values) use ($renderTemplateInput): string {
        $fieldMap      = [];
        $fieldMapLower = [];

        foreach ($section['fields'] as $field) {
            $fieldMap[$field['name']]                    = $field;
            $fieldMapLower[strtolower($field['name'])]   = $field;
        }

        return preg_replace_callback('/\{(.*?)\}/', static function ($matches) use ($fieldMap, $fieldMapLower, $section, $values, $renderTemplateInput) {
            $name = trim($matches[1]);

            // Exact match
            if (isset($fieldMap[$name])) {
                return $renderTemplateInput($fieldMap[$name], $section, $values);
            }

            // Case-insensitive match: {INPUT_1} → field name "input_1"
            if (isset($fieldMapLower[strtolower($name)])) {
                return $renderTemplateInput($fieldMapLower[strtolower($name)], $section, $values);
            }

            return $matches[0];
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
                            echo $renderTableTemplate($template, $section, $values);
                            //   echo $renderSectionTemplate($template, $section, $values);
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
                                    <?= $specialCharAttrs($field) ?>
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

<?= $this->section('scripts') ?>
<script>
    (function () {
        const specialCharRegex = /[^A-Za-z0-9\s]/g;
        const inputs = document.querySelectorAll('input[data-no-special="1"]');

        inputs.forEach(function (input) {
            input.addEventListener('input', function () {
                const cleaned = input.value.replace(specialCharRegex, '');
                if (cleaned !== input.value) {
                    input.value = cleaned;
                    input.setCustomValidity('Only letters, numbers, and spaces are allowed.');
                    input.reportValidity();
                    return;
                }

                input.setCustomValidity('');
            });
        });
    })();
</script>
<?= $this->endSection() ?>
