<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?><?= esc($form['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Only plain text/search inputs are restricted to letters/numbers/spaces.
// email, url, tel legitimately need @ : + . etc., so they are NOT restricted.
$isTextLikeType = static function (string $type): bool {
    return in_array(strtolower($type), ['text', 'search'], true);
};

$specialCharAttrs = static function (array $field) use ($isTextLikeType): string {
    if (!$isTextLikeType((string) ($field['type'] ?? 'text'))) {
        return '';
    }

    return ' pattern="[A-Za-z0-9\\s]*" title="Only letters, numbers, and spaces are allowed." data-no-special="1"';
};

// Normalize a field's stored options (JSON) into a [{value, label}, ...] list.
// Handles both [{id,label}] objects (from the builder) and plain ["a","b"] arrays.
$parseFieldOptions = static function ($raw): array {
    $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    $options = [];
    foreach ($decoded as $opt) {
        if (is_array($opt)) {
            $label = (string) ($opt['label'] ?? $opt['value'] ?? $opt['id'] ?? '');
            $value = (string) ($opt['value'] ?? $opt['label'] ?? $opt['id'] ?? $label);
        } else {
            $label = (string) $opt;
            $value = (string) $opt;
        }
        if ($label === '' && $value === '') {
            continue;
        }
        $options[] = ['value' => $value, 'label' => $label];
    }

    return $options;
};

// Renders one form control for a field.
// $rowIndex: when set (repeatable table rows) the input name is indexed
//   sections[sid][field][0], [1], ... so each row submits as its own record
//   and an unchecked checkbox simply leaves a gap instead of shifting rows.
// $rowValues: the saved values for this specific row (used to prefill).
$renderTemplateInput = static function (array $field, array $section, array $values, ?int $rowIndex = null, ?array $rowValues = null) use ($specialCharAttrs, $parseFieldOptions): string {
    $validation = json_decode($field['validation'] ?? 'null', true) ?? [];

    if ($rowValues !== null) {
        // Repeatable table row: take the value for this specific row.
        $value = $rowValues[$field['name']] ?? '';
    } else {
        $value = old('sections.' . $section['id'] . '.' . $field['name'])
            ?? ($values[$section['id']][$field['name']] ?? '');
    }

    // Never echo an array as a scalar.
    if (is_array($value)) {
        $value = '';
    }
    $value = (string) $value;

    $label    = esc($field['label'] ?? $field['name']);
    $required = !empty($validation['required']) ? ' required' : '';
    $type     = strtolower($field['type'] ?? 'text');

    $name = 'sections[' . esc($section['id']) . '][' . esc($field['name']) . ']'
        . ($rowIndex !== null ? '[' . (int) $rowIndex . ']' : '');

    // --- DROPDOWN SELECT ---
    if ($type === 'select') {
        $options = $parseFieldOptions($field['options'] ?? null);

        $html = '<span class="template-field template-field--select"><select name="' . $name . '"' . $required . '>';
        $html .= '<option value="">-- Select --</option>';
        foreach ($options as $opt) {
            $selected = ($value !== '' && $value === $opt['value']) ? ' selected' : '';
            $html .= '<option value="' . esc($opt['value']) . '"' . $selected . '>' . esc($opt['label']) . '</option>';
        }
        $html .= '</select></span>';

        return $html;
    }

    // --- CHECKBOX --- (value "1" when checked; absent when unchecked)
    if ($type === 'checkbox') {
        $checked = ($value !== '' && $value !== '0') ? ' checked' : '';

        return '<span class="template-field template-field--checkbox">'
            . '<input type="checkbox" value="1" name="' . $name . '"' . $checked . $required . '></span>';
    }

    // --- TEXTAREA ---
    if ($type === 'textarea') {
        return '<span class="template-field template-field--textarea"><textarea name="' . $name . '"' . $required . '>' . esc($value) . '</textarea></span>';
    }

    // --- TEXT / NUMBER / EMAIL / DATE / etc. ---
    $allowedTypes = ['text', 'number', 'email', 'date', 'tel', 'url', 'search', 'time', 'datetime-local', 'password'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'text';
    }

    $specialValidation = $specialCharAttrs($field);

    return '<span class="template-field">(' . $label . ')<input type="' . esc($type) . '" value="' . esc($value) . '" name="' . $name . '"' . $required . $specialValidation . '></span>';
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
    $fieldMap = [];

    foreach ($section['fields'] as $field) {
        $fieldMap[$field['name']] = $field;
    }

    // Renders a single cell, prefilling input values from $rowValues (this row's saved data).
    // $rowIndex indexes the input name for repeatable rows (null = single record).
    $renderCell = static function (string $raw, array $rowValues, ?int $rowIndex) use ($fieldMap, $section, $values, $renderTemplateInput): string {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return '';
        }

        // {fieldName} curly-brace syntax (e.g. {input_1})
        if (preg_match('/^\{(.+)\}$/', $trimmed, $m)) {
            $field = $fieldMap[trim($m[1])] ?? null;
            if ($field) {
                return $renderTemplateInput($field, $section, $values, $rowIndex, $rowValues);
            }
        }

        // [fieldName] or [fieldName|type] syntax
        if (preg_match('/^\[(.+)\]$/', $trimmed, $m)) {
            $parts    = array_map('trim', explode('|', $m[1]));
            $fieldKey = $parts[0];
            $cellType = strtolower($parts[1] ?? 'input');

            if ($cellType === 'label' || $cellType === 'header') {
                return esc($fieldKey);
            }

            $field = $fieldMap[$fieldKey] ?? null;

            if ($field) {
                return $renderTemplateInput($field, $section, $values, $rowIndex, $rowValues);
            }
        }

        // Plain field name without brackets (e.g. "input_1" stored directly)
        $field = $fieldMap[$trimmed] ?? null;

        if ($field) {
            return $renderTemplateInput($field, $section, $values, $rowIndex, $rowValues);
        }

        return esc($trimmed);
    };

    $lines = array_values(array_filter(
        explode("\n", str_replace("\r\n", "\n", $template)),
        fn($l) => trim($l) !== ''
    ));

    if (empty($lines)) {
        return '';
    }

    // Normalize saved data into a list of row objects:
    //  - repeatable table  -> [ {..row0..}, {..row1..} ]
    //  - single record     -> [ {..fields..} ]
    $saved     = $values[$section['id']] ?? [];
    $savedRows = [];
    if (is_array($saved) && !empty($saved)) {
        $isList    = array_keys($saved) === range(0, count($saved) - 1);
        $savedRows = $isList ? array_values($saved) : [$saved];
    }

    // Row-action mode for this section:
    //   editable -> user can add / delete rows (shows + Add Row and the Action column)
    //   group    -> multiple rows saved together, but no add/delete UI
    //   singular -> a single record; anything else behaves the same (no add UI)
    $actionFlag = strtolower($section['action_flag'] ?? '');
    $editable   = $actionFlag === 'editable';
    $group      = $actionFlag === 'group';

    $bodyLines   = array_slice($lines, 1);
    $isSingleRow = count($bodyLines) === 1;

    // editable/group repeat a single-row template once per saved row; everything
    // else (singular / unset / multi-row matrices) renders the template once.
    $repeatRows   = ($editable || $group) && $isSingleRow;
    $rowInstances = $repeatRows ? max(1, count($savedRows)) : 1;

    // data-next-index seeds the "Add Row" JS so cloned rows get a fresh index.
    $html        = '<div class="repeatable-table" data-section="' . esc($section['id']) . '" data-next-index="' . $rowInstances . '">';
    $html       .= '<table>';
    $headerCells = $parseCsvLine($lines[0]);
    $html       .= '<thead><tr>';

    foreach ($headerCells as $cell) {
        $html .= '<th>' . esc(trim($cell)) . '</th>';
    }

    if ($editable) {
        $html .= '<th class="rt-action-col">Action</th>';
    }
    $html .= '</tr></thead>';

    $html .= '<tbody>';

    for ($instance = 0; $instance < $rowInstances; $instance++) {
        $rowValues = $repeatRows ? ($savedRows[$instance] ?? []) : ($savedRows[0] ?? []);
        if (!is_array($rowValues)) {
            $rowValues = [];
        }

        // Repeatable rows are indexed (0,1,2,...); single records use null.
        $rowIndex = $repeatRows ? $instance : null;

        foreach ($bodyLines as $line) {
            $cells = $parseCsvLine($line);
            $html .= '<tr class="rt-row">';

            foreach ($cells as $cell) {
                $html .= '<td>' . $renderCell($cell, $rowValues, $rowIndex) . '</td>';
            }

            if ($editable) {
                $html .= '<td class="rt-action-col"><button type="button" class="rt-del" title="Remove row">&times;</button></td>';
            }
            $html .= '</tr>';
        }
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Only editable tables let the end user add rows.
    if ($editable) {
        $html .= '<div class="rt-add-wrap"><button type="button" class="rt-add">+ Add Row</button></div>';
    }

    $html .= '</div>';

    return $html;
};

$renderSectionTemplate = static function (string $template, array $section, array $values) use ($renderTemplateInput): string {
    $fieldMap = [];

    foreach ($section['fields'] as $field) {
        $fieldMap[$field['name']] = $field;
    }

    return preg_replace_callback('/\{(.*?)\}/', static function ($matches) use ($fieldMap, $section, $values, $renderTemplateInput) {
        $name = trim($matches[1]);

        // Exact match
        if (isset($fieldMap[$name])) {
            return $renderTemplateInput($fieldMap[$name], $section, $values);
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

                <input type="hidden" name="form_id[<?= esc($section['id']) ?>]" value="<?= esc($form['id']) ?>">


                <input type="hidden" name="table_name[<?= esc($section['id']) ?>]" value="<?= esc($form['table'] ?? 'form_values') ?>">

                <input type="hidden" name="action_flag[<?= esc($section['id']) ?>]" value="<?= esc($section['action_flag'] ?? '') ?>">

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
                                    <?= $specialCharAttrs($field) ?>>
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
    (function() {
        const specialCharRegex = /[^A-Za-z0-9\s]/g;
        const inputs = document.querySelectorAll('input[data-no-special="1"]');

        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
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

    // Repeatable table rows: clone the last row (empty) on "Add Row", delete on "×".
    (function() {
        function clearRow(row) {
            row.querySelectorAll('input, textarea, select').forEach(function(el) {
                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = false;
                } else {
                    el.value = '';
                }
                el.setCustomValidity('');
            });
        }

        // Re-point a cloned row's field names to a fresh row index, e.g.
        // sections[5][qty][2] -> sections[5][qty][7]. Only the trailing [n] changes.
        function reindexRow(row, index) {
            row.querySelectorAll('[name]').forEach(function(el) {
                el.name = el.name.replace(/\[\d+\]$/, '[' + index + ']');
            });
        }

        document.querySelectorAll('.repeatable-table').forEach(function(wrap) {
            var tbody = wrap.querySelector('tbody');
            var addBtn = wrap.querySelector('.rt-add');
            if (!tbody) return;

            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    var rows = tbody.querySelectorAll('.rt-row');
                    if (!rows.length) return;

                    // Use a monotonic counter so indexes stay unique even after deletes.
                    var nextIndex = parseInt(wrap.getAttribute('data-next-index') || rows.length, 10);
                    var clone = rows[rows.length - 1].cloneNode(true);
                    clearRow(clone);
                    reindexRow(clone, nextIndex);
                    tbody.appendChild(clone);
                    wrap.setAttribute('data-next-index', nextIndex + 1);
                });
            }

            tbody.addEventListener('click', function(e) {
                var btn = e.target.closest('.rt-del');
                if (!btn) return;
                var rows = tbody.querySelectorAll('.rt-row');
                if (rows.length <= 1) {
                    clearRow(rows[0]); // keep at least one row
                    return;
                }
                btn.closest('.rt-row').remove();
            });
        });
    })();
</script>
<style>
    .rt-add-wrap {
        margin-top: 10px;
    }

    .rt-add {
        background: #1f7a44;
        color: #fff;
        border: 0;
        border-radius: 6px;
        padding: 8px 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .rt-add:hover {
        background: #195f36;
    }

    .rt-del {
        background: #d9534f;
        color: #fff;
        border: 0;
        border-radius: 6px;
        width: 30px;
        height: 30px;
        line-height: 1;
        font-size: 18px;
        cursor: pointer;
    }

    .rt-del:hover {
        background: #b52b27;
    }

    td.rt-action-col,
    th.rt-action-col {
        text-align: center;
        white-space: nowrap;
    }
</style>
<?= $this->endSection() ?>