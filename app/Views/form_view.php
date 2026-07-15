<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?><?= esc($form['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (!($canEdit ?? true) && !($readonly ?? false)): ?>
    <div style="background: rgba(255, 152, 0, 0.1); border: 1px solid rgba(255, 152, 0, 0.3); color: #f57c00; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500;">
        ⓘ This form is awaiting ASR approval. Edit access will be available once the ASR number is created.
    </div>
<?php endif; ?>

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

            $value = (string) ($opt['value'] ?? $opt['id'] ?? $opt['label'] ?? '');
            $label = (string) ($opt['label'] ?? $opt['value'] ?? $opt['id'] ?? $value);
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

    // --- MEASUREMENT --- free-form technical value: decimals, units, slashes,
    if ($type === 'measurement') {
        return '<span class="template-field template-field--measurement">(' . $label . ')'
            . '<input type="text" inputmode="text" value="' . esc($value) . '" name="' . $name . '"' . $required . '></span>';
    }

    // --- TEXT / NUMBER / EMAIL / DATE / etc. ---
    $allowedTypes = ['text', 'number', 'email', 'date', 'tel', 'url', 'search', 'time', 'datetime-local', 'password'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'text';
    }

    $specialValidation = $specialCharAttrs($field);

    $numericAttrs = '';
    if ($type === 'number') {
        $step = $validation['step'] ?? 'any';
        $numericAttrs .= ' step="' . esc((string) $step) . '"';
        if (isset($validation['min']) && $validation['min'] !== '') {
            $numericAttrs .= ' min="' . esc((string) $validation['min']) . '"';
        }
        if (isset($validation['max']) && $validation['max'] !== '') {
            $numericAttrs .= ' max="' . esc((string) $validation['max']) . '"';
        }
    }

    return '<span class="template-field">(' . $label . ')<input type="' . esc($type) . '" value="' . esc($value) . '" name="' . $name . '"' . $required . $numericAttrs . $specialValidation . '></span>';
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

$renderTableTemplate = static function (string $template, array $section, array $values) use ($renderTemplateInput, $parseCsvLine): string {
    $fieldMap = [];

    foreach ($section['fields'] as $field) {
        $fieldMap[$field['name']] = $field;
    }

    $resolveInputField = static function (string $raw) use ($fieldMap): ?array {
        $t = trim($raw);

        $looksLikeKey = static fn(string $k): bool => (bool) preg_match('/^[A-Za-z][\w-]*$/', $k);

        $key = null;
        if (preg_match('/^\{(.+)\}$/', $t, $m)) {
            $key = trim($m[1]);
        } elseif (preg_match('/^\[(.+)\]$/', $t, $m)) {
            $parts = array_map('trim', explode('|', $m[1]));
            $type  = strtolower($parts[1] ?? 'input');
            if ($type === 'label' || $type === 'header') {
                return null; // explicitly static
            }
            $key = $parts[0];
        } else {
            // Bare word that exactly matches a saved field (e.g. "input_1").
            return $fieldMap[$t] ?? null;
        }

        if ($key === null || $key === '' || !$looksLikeKey($key)) {
            return null;
        }

        if (isset($fieldMap[$key])) {
            return $fieldMap[$key];
        }

        // Orphan token -> synthesize a text input keyed by the token name.
        return ['name' => $key, 'label' => $key, 'type' => 'text', 'validation' => null, 'options' => null];
    };

    $renderCell = static function (string $raw, array $rowValues, ?int $rowIndex) use ($resolveInputField, $section, $values, $renderTemplateInput): string {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return '';
        }

        $field = $resolveInputField($raw);
        if ($field !== null) {
            return $renderTemplateInput($field, $section, $values, $rowIndex, $rowValues);
        }

        // Static cell: strip a [text|label]/[text|header] wrapper down to its text.
        if (preg_match('/^\[(.+)\]$/', $trimmed, $m)) {
            $parts = array_map('trim', explode('|', $m[1]));

            return esc($parts[0]);
        }

        return esc($trimmed);
    };

    // Extract |c#|r# span markers from a [field|...] cell token (1 = no span).
    $parseSpan = static function (string $raw): array {
        $colSpan = 1;
        $rowSpan = 1;
        $trimmed = trim($raw);

        if (preg_match('/^\[(.+)\]$/', $trimmed, $m)) {
            foreach (array_map('trim', explode('|', $m[1])) as $part) {
                if (preg_match('/^c(\d+)$/i', $part, $cm)) {
                    $colSpan = max(1, min(12, (int) $cm[1]));
                } elseif (preg_match('/^r(\d+)$/i', $part, $rm)) {
                    $rowSpan = max(1, min(50, (int) $rm[1]));
                }
            }
        }

        return [$colSpan, $rowSpan];
    };

    $lines = array_values(array_filter(
        explode("\n", str_replace("\r\n", "\n", $template)),
        fn($l) => trim($l) !== ''
    ));

    if (empty($lines)) {
        return '';
    }


    $saved     = $values[$section['id']] ?? [];
    $savedRows = [];
    if (is_array($saved) && !empty($saved)) {
        $isList    = array_keys($saved) === range(0, count($saved) - 1);
        $savedRows = $isList ? array_values($saved) : [$saved];
    }


    $actionFlag = strtolower($section['action_flag'] ?? '');
    $editable   = $actionFlag === 'editable';
    $group      = $actionFlag === 'group';


    $cellFieldName = static function (string $raw) use ($resolveInputField): ?string {
        $field = $resolveInputField($raw);

        return $field['name'] ?? null;
    };

    // ---- Header rows: usually 1, but support a STACKED (multi-row) header -----
    $rowAllStatic = function (string $line) use ($cellFieldName, $parseCsvLine): bool {
        $blank = true;
        foreach ($parseCsvLine($line) as $c) {
            if (trim($c) === '') {
                continue;
            }
            $blank = false;
            if ($cellFieldName($c) !== null) {
                return false;
            }
        }

        return !$blank;
    };

    $firstHeaderSpans = false;
    foreach ($parseCsvLine($lines[0]) as $c) {
        [$cs, $rs] = $parseSpan($c);
        if ($cs > 1 || $rs > 1) {
            $firstHeaderSpans = true;
            break;
        }
    }

    $headerLineCount = 1;
    if ($firstHeaderSpans) {
        while ($headerLineCount < count($lines) && $rowAllStatic($lines[$headerLineCount])) {
            $headerLineCount++;
        }
    }
    $headerLines = array_slice($lines, 0, $headerLineCount);

    $bodyLines   = array_slice($lines, $headerLineCount);
    $isSingleRow = count($bodyLines) === 1;

    // group (non-editable) repeats a single-row template once per saved row;

    $repeatRows   = $group && $isSingleRow;
    $rowInstances = $repeatRows ? max(1, count($savedRows)) : 1;

    // Build a grid of body cells with their resolved column position and spans so

    $grid    = [];
    $covered = [];
    foreach ($bodyLines as $r => $line) {
        $grid[$r] = [];
        $col      = 0;
        foreach ($parseCsvLine($line) as $cell) {
            $col++;
            while (!empty($covered[$r . ':' . $col])) {
                $col++;
            }

            [$colSpan, $rowSpan] = $parseSpan($cell);
            $grid[$r][] = ['raw' => $cell, 'col' => $col, 'colSpan' => $colSpan, 'rowSpan' => $rowSpan];

            for ($ri = $r; $ri < $r + $rowSpan; $ri++) {
                for ($ci = $col; $ci < $col + $colSpan; $ci++) {
                    if ($ri !== $r || $ci !== $col) {
                        $covered[$ri . ':' . $ci] = true;
                    }
                }
            }

            $col += $colSpan - 1;
        }
    }
    $numBodyRows = count($bodyLines);


    $rowIsSeparator = [];
    for ($r = 0; $r < $numBodyRows; $r++) {
        $hasInput = false;
        foreach ($grid[$r] as $c) {
            if ($cellFieldName($c['raw']) !== null) {
                $hasInput = true;
                break;
            }
        }
        $rowIsSeparator[$r] = !$hasInput;
    }


    $bodyCols = 0;
    foreach ($grid as $rowCells) {
        foreach ($rowCells as $c) {
            $bodyCols = max($bodyCols, $c['col'] + $c['colSpan'] - 1);
        }
    }


    $headerGrid = [];
    $headerCols = 0;
    $hCovered   = [];
    foreach ($headerLines as $hr => $line) {
        $headerGrid[$hr] = [];
        $col             = 0;
        foreach ($parseCsvLine($line) as $cell) {
            $col++;
            while (!empty($hCovered[$hr . ':' . $col])) {
                $col++;
            }

            [$hcSpan, $hrSpan] = $parseSpan($cell);

            // Header display text: strip the [text|header|c2] / {name} wrappers.
            $text = trim($cell);
            if (preg_match('/^\{(.+)\}$/', $text, $hm)) {
                $text = trim($hm[1]);
            } elseif (preg_match('/^\[(.+)\]$/', $text, $hm)) {
                $parts = array_map('trim', explode('|', $hm[1]));
                $text  = $parts[0];
            }

            $headerGrid[$hr][] = ['text' => $text, 'col' => $col, 'colSpan' => $hcSpan, 'rowSpan' => $hrSpan];
            $headerCols        = max($headerCols, $col + $hcSpan - 1);

            for ($ri = $hr; $ri < $hr + $hrSpan; $ri++) {
                for ($ci = $col; $ci < $col + $hcSpan; $ci++) {
                    if ($ri !== $hr || $ci !== $col) {
                        $hCovered[$ri . ':' . $ci] = true;
                    }
                }
            }

            $col += $hcSpan - 1;
        }
    }

    $dataCols  = max($headerCols, $bodyCols, 1);
    $totalCols = $dataCols + ($editable ? 1 : 0);

    $emitRowCells = static function (array $rowCells, array $recValues, ?int $rowIndex) use ($renderCell): string {
        $out = '';
        foreach ($rowCells as $c) {
            $attrs = ($c['colSpan'] > 1 ? ' colspan="' . $c['colSpan'] . '"' : '')
                . ($c['rowSpan'] > 1 ? ' rowspan="' . $c['rowSpan'] . '"' : '');
            $out .= '<td' . $attrs . '>' . $renderCell($c['raw'], $recValues, $rowIndex) . '</td>';
        }

        return $out;
    };

    // ---- Editable: partition body rows into blocks bound together by rowspans ----

    $blocks      = [];
    $blockFields = [];
    $segments    = [];
    if ($editable) {
        $r = 0;
        while ($r < $numBodyRows) {
            // A separator row renders standalone and resets block partitioning.
            if ($rowIsSeparator[$r]) {
                $segments[] = ['type' => 'sep', 'row' => $r];
                $r++;
                continue;
            }

            $end = $r;
            for ($i = $r; $i <= $end && $i < $numBodyRows; $i++) {
                foreach ($grid[$i] as $c) {
                    $reach = $i + $c['rowSpan'] - 1;
                    if ($reach > $end) {
                        $end = $reach;
                    }
                }
            }
            if ($end >= $numBodyRows) {
                $end = $numBodyRows - 1;
            }
            // A block never crosses a separator row — stop just before one.
            for ($k = $r + 1; $k <= $end; $k++) {
                if ($rowIsSeparator[$k]) {
                    $end = $k - 1;
                    break;
                }
            }

            $bi          = count($blocks);
            $blocks[$bi] = ['start' => $r, 'end' => $end];
            $segments[]  = ['type' => 'block', 'index' => $bi];

            $names = [];
            for ($rr = $r; $rr <= $end; $rr++) {
                foreach ($grid[$rr] as $c) {
                    $fn = $cellFieldName($c['raw']);
                    if ($fn !== null) {
                        $names[$fn] = true;
                    }
                }
            }
            $blockFields[$bi] = array_keys($names);

            $r = $end + 1;
        }


        $blockInstances = array_fill(0, count($blocks), []);
        foreach ($savedRows as $rec) {
            if (!is_array($rec)) {
                continue;
            }
            foreach ($blocks as $bi => $b) {
                foreach ($blockFields[$bi] as $fn) {
                    if (isset($rec[$fn]) && trim((string) $rec[$fn]) !== '') {
                        $blockInstances[$bi][] = $rec;
                        continue 3;
                    }
                }
            }
        }

        // Total instances rendered = the starting point for the "Add Row" JS so
\
        $totalInstances = 0;
        foreach ($blocks as $bi => $b) {
            $totalInstances += max(1, count($blockInstances[$bi]));
        }
    } else {
        $totalInstances = $rowInstances;
    }

    $html  = '<div class="repeatable-table" data-section="' . esc($section['id']) . '" data-next-index="' . $totalInstances . '">';
    $html .= '<table>';
    $html .= '<thead>';
    foreach ($headerGrid as $hr => $cells) {
        $html .= '<tr>';
        foreach ($cells as $h) {
            $attrs = ($h['colSpan'] > 1 ? ' colspan="' . $h['colSpan'] . '"' : '')
                . ($h['rowSpan'] > 1 ? ' rowspan="' . $h['rowSpan'] . '"' : '');
            $html .= '<th' . $attrs . '>' . esc($h['text']) . '</th>';
        }
        // The Action column header spans every header row, once, on the first row.
        if ($editable && $hr === 0) {
            $rs = count($headerLines) > 1 ? ' rowspan="' . count($headerLines) . '"' : '';
            $html .= '<th class="rt-action-col"' . $rs . '>Action</th>';
        }
        $html .= '</tr>';
    }
    $html .= '</thead>';

    if ($editable) {
        $rowIndex = 0; 
        foreach ($segments as $seg) {
            // --- Separator: a full-width static sub-header (no block, no Add Row) ---
            if ($seg['type'] === 'sep') {
                $text = '';
                foreach ($grid[$seg['row']] as $c) {
                    $piece = $renderCell($c['raw'], [], null);
                    if (trim($piece) !== '') {
                        $text .= ($text === '' ? '' : ' ') . $piece;
                    }
                }
                $html .= '<tbody class="rt-sep"><tr>'
                    . '<td class="rt-sep-cell" colspan="' . $totalCols . '">' . $text . '</td>'
                    . '</tr></tbody>';
                continue;
            }

            $bi          = $seg['index'];
            $b           = $blocks[$bi];
            $blockHeight = $b['end'] - $b['start'] + 1;
            $instances   = max(1, count($blockInstances[$bi]));

            $html .= '<tbody class="rt-block" data-block-rows="' . $blockHeight . '">';
            for ($inst = 0; $inst < $instances; $inst++) {
                $rec = $blockInstances[$bi][$inst] ?? [];
                if (!is_array($rec)) {
                    $rec = [];
                }

                for ($rr = $b['start']; $rr <= $b['end']; $rr++) {
                    $html .= '<tr class="rt-row">';
                    $html .= $emitRowCells($grid[$rr], $rec, $rowIndex);

                    // One delete per block instance: an action cell on the first
                    // row, spanning the whole block.
                    if ($rr === $b['start']) {
                        $html .= '<td class="rt-action-col" rowspan="' . $blockHeight . '">'
                            . '<button type="button" class="rt-del" title="Remove">&times;</button></td>';
                    }
                    $html .= '</tr>';
                }
                $rowIndex++;
            }

            // One "Add Row" per block — clones this block's last instance intact.
            $html .= '<tr class="rt-add-row"><td colspan="' . $totalCols . '">'
                . '<button type="button" class="rt-add">+ Add Row</button></td></tr>';
            $html .= '</tbody>';
        }
    } else {
        $html .= '<tbody>';
        for ($instance = 0; $instance < $rowInstances; $instance++) {
            $rowValues = $repeatRows ? ($savedRows[$instance] ?? []) : ($savedRows[0] ?? []);
            if (!is_array($rowValues)) {
                $rowValues = [];
            }
            $rowIndex = $repeatRows ? $instance : null;

            foreach ($grid as $rowCells) {
                $html .= '<tr class="rt-row">' . $emitRowCells($rowCells, $rowValues, $rowIndex) . '</tr>';
            }
        }
        $html .= '</tbody>';
    }

    $html .= '</table>';
    $html .= '</div>';

    return $html;
};

$renderSectionTemplate = static function (string $template, array $section, array $values) use ($renderTemplateInput): string {
    $fieldMap = [];

    foreach ($section['fields'] as $field) {
        $fieldMap[$field['name']] = $field;
    }

     
    return preg_replace_callback('/\{([^}]+)\}|\[([^\]]+)\]/', static function ($matches) use ($fieldMap, $section, $values, $renderTemplateInput) {
        // group 1 = {...}, group 2 = [...]
        $token = ($matches[1] ?? '') !== '' ? $matches[1] : ($matches[2] ?? '');

        $parts    = array_map('trim', explode('|', $token));
        $fieldKey = $parts[0];
        $cellType = strtolower($parts[1] ?? 'input');

        // label/header modifier -> static text, not an input
        if ($cellType === 'label' || $cellType === 'header') {
            return esc($fieldKey);
        }

        if (isset($fieldMap[$fieldKey])) {
            return $renderTemplateInput($fieldMap[$fieldKey], $section, $values);
        }


        if (preg_match('/^[A-Za-z][\w-]*$/', $fieldKey)) {
            $synthetic = ['name' => $fieldKey, 'label' => $fieldKey, 'type' => 'text', 'validation' => null, 'options' => null];

            return $renderTemplateInput($synthetic, $section, $values);
        }

        // Anything else (incidental prose brackets) -> leave exactly as written.
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

    <?php
    helper(['auth', 'workflow']);

    $currentStatus    = $form['status'] ?? 'created';
    $viewMode         = isset($_GET['mode']) && $_GET['mode'] === 'view';
    $workflowActions  = workflow_available_actions($currentStatus);
    ?>


    <div class="form-sections <?= $viewMode ? 'view-mode' : '' ?>">
        <?php foreach ($sections as $index => $section): ?>
            <?php $layout = strtolower($section['layout'] ?? ''); ?>
            <form class="section-panel" method="post" action="<?= site_url('form/submit') ?>">
                <?= csrf_field() ?>

                <input type="hidden" name="form_id[<?= esc($section['id']) ?>]" value="<?= esc($form['id']) ?>">

                <?php if (!empty($asrId)): ?>
                    <input type="hidden" name="asr_id[<?= esc($section['id']) ?>]" value="<?= esc($asrId) ?>">
                <?php endif; ?>

                <input type="hidden" name="table_name[<?= esc($section['id']) ?>]" value="<?= esc($form['table'] ?? 'form_values') ?>">

                <input type="hidden" name="action_flag[<?= esc($section['id']) ?>]" value="<?= esc($section['action_flag'] ?? '') ?>">

                <fieldset class="section-fieldset" style="border:0;margin:0;padding:0;min-width:0;" <?= ($readonly ?? false) ? 'disabled' : '' ?>>
                <div class="section-panel-header">
                    <div>
                        <span class="section-kicker">Section <?= $index + 1 ?></span>
                        <h2><?= esc($section['title']) ?></h2>
                    </div>
                    <span class="section-count"><?= count($section['fields']) ?> fields</span>
                </div>

                <?php if ($viewMode): ?>
                    <div class="view-mode-overlay">
                        <span class="view-mode-badge">View Only</span>
                    </div>
                <?php endif; ?>

                <?php if ($layout === 'inline'): ?>
                    <div class="inline-template <?= $viewMode ? 'read-only' : '' ?>">
                        <?php
                        echo nl2br($renderSectionTemplate($section['inline_template'] ?? '', $section, $values));
                        ?>
                    </div>
                <?php elseif (in_array($layout, ['tabular', 'table'], true)): ?>
                    <div class="table-template <?= $viewMode ? 'read-only' : '' ?>">
                        <?php
                        $template = $section['table_template'] ?? $section['inline_template'] ?? '';
                        echo $renderTableTemplate($template, $section, $values);
                        ?>
                    </div>
                <?php else: ?>
                    <div class="field-grid <?= $viewMode ? 'read-only' : '' ?>">
                        <?php foreach ($section['fields'] as $field): ?>
                            <?php
                            $validation = json_decode($field['validation'] ?? 'null', true) ?? [];
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
                                    <?= $viewMode ? 'readonly disabled' : '' ?>>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$viewMode && !($readonly ?? false)): ?>
                    <div class="section-actions">
                        <button class="btn btn-primary" type="submit">Save section</button>
                    </div>
                <?php endif; ?>
                </fieldset>
            </form>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Action Bar with View button and Status Checkboxes (hidden when opened from an ASR mapping) -->
    <?php if (empty($asrId)): ?>
    <div class="bottom-action-bar">
        <div class="bottom-action-left">
            <?php if (!$viewMode): ?>
                <a href="<?= site_url('form/' . $form['form_key'] . '?mode=view') ?>" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 8s2-4 7-4 7 4 7 4-2 4-7 4-7-4-7-4z"/>
                        <circle cx="8" cy="8" r="2"/>
                    </svg>
                    View
                </a>
            <?php endif; ?>
        </div>

        <div class="bottom-action-right">
            <div class="status-section">
                <span class="status-label">Status:</span>
                <span class="status-badge status-<?= esc($currentStatus) ?>"><?= esc(workflow_status_label($currentStatus)) ?></span>

                <?= workflow_action_buttons($form) ?>

                <a class="btn btn-secondary" href="<?= site_url('forms/logs/' . $form['id']) ?>">Audit log</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= $this->include('partials/workflow_modal') ?>
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

        function reindexRow(row, index) {
            row.querySelectorAll('[name]').forEach(function(el) {
                el.name = el.name.replace(/\[\d+\]$/, '[' + index + ']');
            });
        }

        function instanceRows(blockTbody) {
            // The last instance = the last data-block-rows .rt-row elements.
            var rows = Array.prototype.slice.call(blockTbody.querySelectorAll('.rt-row'));
            var n = parseInt(blockTbody.getAttribute('data-block-rows') || '1', 10);

            return {
                rows: rows,
                height: n
            };
        }

        // Collect a block instance starting at firstRow, spanning `height` rt-rows.
        function rowsFrom(firstRow, height) {
            var out = [firstRow];
            var sib = firstRow;
            for (var i = 1; i < height; i++) {
                sib = sib.nextElementSibling;
                if (sib && sib.classList.contains('rt-row')) {
                    out.push(sib);
                }
            }

            return out;
        }

        document.querySelectorAll('.repeatable-table').forEach(function(wrap) {
            wrap.addEventListener('click', function(e) {
                // --- Add Row (per block): clone the block's last instance intact ---
                var addBtn = e.target.closest('.rt-add');
                if (addBtn) {
                    var block = addBtn.closest('.rt-block');
                    if (!block) return;
                    var info = instanceRows(block);
                    if (!info.rows.length) return;

                    var addRow = block.querySelector('.rt-add-row');
                    var nextIndex = parseInt(wrap.getAttribute('data-next-index') || '0', 10);
                    var lastInstance = info.rows.slice(info.rows.length - info.height);

                    lastInstance.forEach(function(row) {
                        var clone = row.cloneNode(true);
                        clearRow(clone);
                        reindexRow(clone, nextIndex); // all rows of an instance share one index
                        block.insertBefore(clone, addRow);
                    });
                    wrap.setAttribute('data-next-index', nextIndex + 1);

                    return;
                }

                // --- Delete (per block instance): remove all rows of that instance ---
                var delBtn = e.target.closest('.rt-del');
                if (delBtn) {
                    var blk = delBtn.closest('.rt-block');
                    if (!blk) return;
                    var height = parseInt(blk.getAttribute('data-block-rows') || '1', 10);
                    var firstRow = delBtn.closest('.rt-row');
                    var allRows = blk.querySelectorAll('.rt-row');
                    var instance = rowsFrom(firstRow, height);

                    // Keep at least one instance per block — clear it instead of removing.
                    if (allRows.length <= height) {
                        instance.forEach(clearRow);

                        return;
                    }
                    instance.forEach(function(r) {
                        r.remove();
                    });
                }
            });
        });
    })();

    // Alert on unauthorized status checkbox clicks.
    (function() {
        document.querySelectorAll('.status-checkbox[data-warning]').forEach(function(label) {
            label.addEventListener('click', function(event) {
                var warning = label.dataset.warning;
                var input = label.querySelector('input[type="checkbox"]');

                if (!input || !input.disabled || warning === '') {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                alert(warning);
            });
        });
    })();
</script>
<style>
    .rt-add-wrap {
        margin-top: 10px;
    }

    /* Per-block "Add Row" footer cell (one per rt-block tbody). */
    .rt-add-row td {
        padding: 8px;
        text-align: left;
        background: #f6f8fa;
        border-top: 1px solid #e2e6ea;
    }

    /* Keep block tbodies visually distinct from one another. */
    tbody.rt-block+tbody.rt-block {
        border-top: 2px solid #d0d7de;
    }

    /* Full-width sub-header divider inside a table body (e.g. "Method precision").
       Not a data row: no inputs, no Add Row, no delete. */
    .rt-sep-cell {
        background: #eef2f6;
        font-weight: 700;
        text-align: left;
        padding: 10px 12px;
        border-top: 2px solid #d0d7de;
        color: #1f2d3d;
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

    /* Compact, centered checkbox so it doesn't hog the cell. */
    .template-field--checkbox {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .template-field--checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin: 0;
        cursor: pointer;
        accent-color: #1f7a44;
    }

    /* Bottom Action Bar Styles */
    .bottom-action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 -1px 3px rgba(0,0,0,0.06);
        margin-top: 32px;
        border: 1px solid #e9ecf0;
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(8px);
    }

    .bottom-action-left {
        display: flex;
        gap: 8px;
    }

    .bottom-action-right {
        display: flex;
        align-items: center;
    }

    .status-section {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .status-label {
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        margin-right: 4px;
    }

    .status-checkboxes {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .status-checkbox {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
        color: #4a5568;
        position: relative;
        user-select: none;
    }

    .status-checkbox.disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }

    .status-note {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #b92b27;
        line-height: 1.2;
    }

    .status-checkbox input[type="checkbox"] {
        display: none;
    }

    .status-checkbox .checkmark {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e0;
        border-radius: 4px;
        display: inline-block;
        position: relative;
        transition: all 0.2s ease;
        flex-shrink: 0;
        background: #fff;
    }

    .status-checkbox.checked .checkmark {
        background: #1f7a44;
        border-color: #1f7a44;
    }

    .status-checkbox.checked .checkmark::after {
        content: "✓";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .status-checkbox:hover .checkmark {
        border-color: #1f7a44;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.created {
        background: #edf2f7;
        color: #4a5568;
    }

    .status-badge.reviewed {
        background: #ebf8ff;
        color: #2b6cb0;
    }

    .status-badge.approved {
        background: #f0fff4;
        color: #276749;
    }

    .status-form {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 0;
    }

    /* View mode styles */
    .view-mode .section-panel {
        opacity: 0.9;
        position: relative;
    }

    .view-mode-overlay {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 10;
    }

    .view-mode-badge {
        background: #4a5568;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .read-only input:not([type="checkbox"]),
    .read-only textarea,
    .read-only select {
        background: #f7fafc !important;
        cursor: not-allowed !important;
        opacity: 0.8;
    }

    .read-only input:not([type="checkbox"]):focus,
    .read-only textarea:focus,
    .read-only select:focus {
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        text-decoration: none;
        background: #edf2f7;
        color: #2d3748;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        border-color: #cbd5e0;
    }

    .btn-secondary svg {
        flex-shrink: 0;
    }

    @media (max-width: 640px) {
        .bottom-action-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .bottom-action-left,
        .bottom-action-right {
            justify-content: center;
        }

        .status-section {
            flex-wrap: wrap;
            justify-content: center;
        }

        .status-form {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>
<?= $this->endSection() ?>
