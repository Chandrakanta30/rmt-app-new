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
            // Builder stores {id, label} where `id` is the value to save and
            // `label` is shown. Prefer id (or an explicit `value`) for the value
            // so we never accidentally save the label as the stored value.
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

    // --- MEASUREMENT --- free-form technical value: decimals, units, slashes,
    // scientific notation (40.5 Kv, Kv/Ma, 1.54098×10⁰, "about 09 minutes").
    // Rendered as text WITHOUT the letters/numbers/spaces restriction so any
    // symbol is accepted.
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

    // Number inputs need an explicit step or browsers default to step=1 and
    // reject decimals (23.45, 40.5). Honor an explicit step/min/max from the
    // field's validation, otherwise allow any decimal.
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

    // Extract the backing field name of an input cell ([field|...] / {field} /
    // bare name); returns null for label/header/plain-text cells.
    $cellFieldName = static function (string $raw) use ($fieldMap): ?string {
        $t = trim($raw);
        if (preg_match('/^\{(.+)\}$/', $t, $m)) {
            $k = trim($m[1]);

            return isset($fieldMap[$k]) ? $k : null;
        }
        if (preg_match('/^\[(.+)\]$/', $t, $m)) {
            $parts = array_map('trim', explode('|', $m[1]));
            $type  = strtolower($parts[1] ?? 'input');
            if ($type === 'label' || $type === 'header') {
                return null;
            }

            return isset($fieldMap[$parts[0]]) ? $parts[0] : null;
        }

        return isset($fieldMap[$t]) ? $t : null;
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
    // singular / unset / multi-row matrices render once. Editable tables use the
    // block-aware path below instead.
    $repeatRows   = $group && $isSingleRow;
    $rowInstances = $repeatRows ? max(1, count($savedRows)) : 1;

    // Build a grid of body cells with their resolved column position and spans so
    // both block detection and rendering agree on the geometry.
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

    // A body row is a "separator" (a full-width sub-header, e.g. a
    // [Method precision|header|c2] divider) when NONE of its cells is backed by
    // an input field. Separators render across the whole table, are never part
    // of a repeatable block, and get no "+ Add Row" / delete control — they just
    // break the data rows into independent groups.
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

    // Column geometry. The body can have MORE columns than the header row when a
    // header cell spans several body columns. Use the widest of header vs. body so the
    // <thead>, the data rows and the full-width separators/Add-Row all line up.
    $bodyCols = 0;
    foreach ($grid as $rowCells) {
        foreach ($rowCells as $c) {
            $bodyCols = max($bodyCols, $c['col'] + $c['colSpan'] - 1);
        }
    }

    // Build the header grid honoring colspan/rowspan across ALL header rows, so a
    // grouping headerspans its columns and the sub-header row
    // sits beneath it — matching the builder preview.
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
    // A block is a maximal run of consecutive rows where no rowspan reaches past
    // its end. Each block becomes one independently repeatable unit (its own
    // "+ Add Row" button); cloning a block duplicates ALL of its rows so a
    // spanning cell (e.g. a rowspan=3 cell over rows 1-3) is reproduced intact.
    // $segments is the ordered render plan: separator rows and repeatable blocks
    // interleaved in the order they appear, so a "Method precision" divider stays
    // between its groups instead of being swallowed into one.
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

        // Route each saved record to the block it belongs to so an edit-load
        // repeats each block once per saved instance. The submit transpose fills
        // EVERY field key (empty string outside the block), so match on a NON-EMPTY
        // value — each saved instance only fills its own block's fields.
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
        // cloned block instances get fresh, section-unique row indexes.
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
        $rowIndex = 0; // section-unique index; one per block instance, shared by its rows
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

    // Inline fields can be written either way:
    //   {field}            — classic builder (/forms/create)
    //   [field]            — drag & drop builder (/forms/builder)
    //   [field|label] etc. — modifiers; label/header render as static text
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

        // Unknown token -> leave it exactly as written
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
                                    <?= $specialCharAttrs($field) ?>>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!($readonly ?? false)): ?>
                    <div class="section-actions">
                        <button class="btn btn-primary" type="submit">Save section</button>
                    </div>
                <?php endif; ?>
                </fieldset>
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

        // The body is partitioned into blocks (one <tbody class="rt-block"> each).
        // A block is the repeatable unit: data-block-rows is how many template rows
        // it spans, so a rowspan group (e.g. 3 rows) clones/deletes as one unit.
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
</style>
<?= $this->endSection() ?>
