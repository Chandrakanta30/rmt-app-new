

<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?><?= esc($form['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // ─────────────────────────────────────────────────────────────────────────
    // Helper: render a single field as an HTML input/select/textarea.
    //
    // $section here is the DISPLAY section (primary, whose id is the POST key).
    // $subSection is the constituent sub-section whose id we use for value lookup
    // when loading pre-filled data from $values.
    // ─────────────────────────────────────────────────────────────────────────
    $renderField = static function (
        array  $field,
        array  $displaySection,   // primary section — used in POST name
        array  $subSection,       // constituent section — used to look up saved value
        array  $values,
        ?int   $originalSectionId = null  // For merged sections: nest by original section ID
    ): string {
        $validation = json_decode($field['validation'] ?? '[]', true) ?? [];
        $required   = !empty($validation['required']) ? ' required' : '';
        $label      = esc($field['label'] ?? $field['name']);
        $type       = esc($field['type'] ?? 'text');

        // Try old() first (after failed POST), then pre-filled DB value.
        if ($originalSectionId !== null) {
            // For merged sections, use values from the original subsection only.
            $value = old('sections.' . $displaySection['id'] . '.' . $originalSectionId . '.' . $field['name'])
                  ?? ($values[$originalSectionId][$field['name']] ?? '');
        } else {
            $value = old('sections.' . $displaySection['id'] . '.' . $field['name'])
                  ?? ($values[$displaySection['id']][$field['name']] ?? '');
        }

        // For merged sections, nest by original section ID to distinguish field ownership
        if ($originalSectionId !== null) {
            $postName = 'sections[' . esc($displaySection['id']) . '][' . esc($originalSectionId) . '][' . esc($field['name']) . ']';
        } else {
            $postName = 'sections[' . esc($displaySection['id']) . '][' . esc($field['name']) . ']';
        }

        if ($type === 'textarea') {
            return '<label class="template-field">'
                . '<span>' . $label . '</span>'
                . '<textarea name="' . $postName . '"' . $required . '>'
                . esc($value)
                . '</textarea>'
                . '</label>';
        }

        if ($type === 'select') {
            $options = json_decode($field['options'] ?? '[]', true) ?? [];
            $opts    = '';
            $opts   .= '<option value="">— select —</option>';
            foreach ($options as $opt) {
                $optVal   = is_array($opt) ? ($opt['id'] ?? $opt['value'] ?? '') : $opt;
                $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['name'] ?? $optVal) : $opt;
                $sel      = (string)$value === (string)$optVal ? ' selected' : '';
                $opts    .= '<option value="' . esc($optVal) . '"' . $sel . '>' . esc($optLabel) . '</option>';
            }
            return '<label class="template-field">'
                . '<span>' . $label . '</span>'
                . '<select name="' . $postName . '"' . $required . '>' . $opts . '</select>'
                . '</label>';
        }

        // Default: text / number / date / checkbox / email …
        return '<label class="template-field">'
            . '<span>' . $label . '</span>'
            . '<input type="' . $type . '"'
            . ' value="' . esc($value) . '"'
            . ' name="' . $postName . '"'
            . $required . '>'
            . '</label>';
    };

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: render one constituent sub-section using its OWN layout.
    //
    // $displaySection  – the primary/merged section (id used for POST names)
    // $subSection      – the constituent section being rendered right now
    //                    (may be the same object for normal/primary sections)
    // $values          – pre-filled DB data
    // ─────────────────────────────────────────────────────────────────────────
    $renderSubSection = static function (
        array  $displaySection,
        array  $subSection,
        array  $values,
        bool   $isMerged = false
    ) use ($renderField): void {

        // For merged sections, pass the original section ID for proper field nesting
        $originalSectionId = $isMerged ? ($subSection['id'] ?? null) : null;

        $layout   = strtolower($subSection['layout'] ?? 'grid');
        $fields   = $subSection['fields'];

        // Build field map for template token replacement
        $fieldMap = [];
        foreach ($fields as $f) {
            $fieldMap[$f['name']] = $f;
        }

        if ($layout === 'inline') {
            // Replace {token} placeholders with rendered inputs
            $template = $subSection['inline_template'] ?? '';
            $rendered = preg_replace_callback(
                '/\{(.*?)\}/',
                static function ($matches) use ($fieldMap, $displaySection, $subSection, $values, $renderField, $originalSectionId) {
                    $name = trim($matches[1]);
                    if (!isset($fieldMap[$name])) {
                        return $matches[0]; // unknown token — leave as-is
                    }
                    return $renderField($fieldMap[$name], $displaySection, $subSection, $values, $originalSectionId);
                },
                $template
            );
            echo '<div class="inline-template">' . nl2br($rendered) . '</div>';

            // ── CRITICAL FIX ──────────────────────────────────────────────────
            // After rendering the inline template, output any fields that are
            // NOT referenced by a {token} in the template.
            // This is exactly what was missing: absorbed-section fields that have
            // no matching {token} in the primary template were silently dropped.
            $tokenNames = [];
            preg_match_all('/\{(.*?)\}/', $template, $tokenMatches);
            foreach ($tokenMatches[1] as $t) {
                $tokenNames[] = trim($t);
            }

            $unmatched = array_filter($fields, fn($f) => !in_array($f['name'], $tokenNames, true));

            if (!empty($unmatched)) {
                echo '<div class="field-grid field-grid--extra" style="margin-top:1rem;">';
                foreach ($unmatched as $field) {
                    echo $renderField($field, $displaySection, $subSection, $values, $originalSectionId);
                }
                echo '</div>';
            }

        } elseif (in_array($layout, ['table', 'tabular'], true)) {
            $template = $subSection['table_template']
                     ?? $subSection['inline_template']
                     ?? '';
            $rendered = preg_replace_callback(
                '/\{(.*?)\}/',
                static function ($matches) use ($fieldMap, $displaySection, $subSection, $values, $renderField, $originalSectionId) {
                    $name = trim($matches[1]);
                    if (!isset($fieldMap[$name])) {
                        return $matches[0];
                    }
                    return $renderField($fieldMap[$name], $displaySection, $subSection, $values, $originalSectionId);
                },
                $template
            );
            echo '<div class="table-template">' . $rendered . '</div>';

            // Same unmatched-field fallback for table layout
            $tokenNames = [];
            preg_match_all('/\{(.*?)\}/', $template, $tokenMatches);
            foreach ($tokenMatches[1] as $t) {
                $tokenNames[] = trim($t);
            }
            $unmatched = array_filter($fields, fn($f) => !in_array($f['name'], $tokenNames, true));
            if (!empty($unmatched)) {
                echo '<div class="field-grid field-grid--extra" style="margin-top:1rem;">';
                foreach ($unmatched as $field) {
                    echo $renderField($field, $displaySection, $subSection, $values, $originalSectionId);
                }
                echo '</div>';
            }

        } else {
            // grid / full / default — render every field as a label+input
            echo '<div class="field-grid">';
            foreach ($fields as $field) {
                echo $renderField($field, $displaySection, $subSection, $values, $originalSectionId);
            }
            echo '</div>';
        }
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
            <?php
                $mergedTitles   = $section['_merged_titles']   ?? [];
                $mergedSections = $section['_merged_sections'] ?? [$section];
                $mergedTables   = $section['_merged_tables']   ?? [
                    ['id' => $section['id'], 'table' => $section['table'] ?? '']
                ];
                $isMerged = count($mergedSections) > 1;
            ?>

            <form class="section-panel" method="post" action="<?= site_url('form/submit') ?>">
                <?= csrf_field() ?>

                <?php /* form_id lets submit() look up field ownership */ ?>
                <input type="hidden" name="form_id"
                       value="<?= esc($form['id']) ?>">

                <?php /* Primary table (for normal / single-table sections) */ ?>
                <input type="hidden"
                       name="table_name[<?= esc($section['id']) ?>]"
                       value="<?= esc($section['table'] ?? '') ?>">

                <?php /* Full _merged_tables JSON for the submit() handler */ ?>
                <input type="hidden"
                       name="merged_tables[<?= esc($section['id']) ?>]"
                       value="<?= esc(json_encode($mergedTables)) ?>">

                <?php /* Pass record IDs for update operations */ ?>
                <?php
                    $recordId = $recordIds[$section['id']] ?? null;
                    if (is_array($recordId)) {
                        // Merged section: store per-table IDs
                        foreach ($recordId as $table => $id) {
                            ?>
                            <input type="hidden"
                                   name="record_ids[<?= esc($section['id']) ?>][<?= esc($table) ?>]"
                                   value="<?= esc((string)$id) ?>">
                            <?php
                        }
                    } elseif (!empty($recordId)) {
                        // Normal section: single ID
                        ?>
                        <input type="hidden"
                               name="record_ids[<?= esc($section['id']) ?>]"
                               value="<?= esc((string)$recordId) ?>">
                        <?php
                    }
                ?>

                <div class="section-panel-header">
                    <div>
                        <span class="section-kicker">Section <?= $index + 1 ?></span>
                        <h2><?= esc($section['title']) ?></h2>
                        <?php if ($isMerged && !empty($mergedTitles)): ?>
                            <p class="section-merge-note"
                               style="font-size:0.8em;color:#888;margin:2px 0 0;">
                                (merged with: <?= esc(implode(', ', $mergedTitles)) ?>)
                            </p>
                        <?php endif; ?>
                    </div>
                    <span class="section-count"><?= count($section['fields']) ?> fields</span>
                </div>

                <?php
                    /*
                     * Render each constituent sub-section with its OWN layout.
                     *
                     * Examples:
                     *   Section A (inline) → renders the Step-1/Step-2/Step-3
                     *                        template, then any unmatched fields
                     *                        fall into a grid below it.
                     *   Section B (grid)   → renders its own fields as a grid.
                     *
                     * For merged sections, fields are nested by original section ID
                     * to avoid data collision when both tables have similar field names.
                     */
                    foreach ($mergedSections as $subIdx => $subSection):
                        // Add a subtle divider between sub-sections
                        if ($subIdx > 0):
                ?>
                    <hr style="border:none;border-top:1px dashed #d0d5dd;margin:1.5rem 0;">
                    <?php if (!empty($subSection['title'])): ?>
                        <p style="font-size:0.85em;font-weight:600;color:#555;margin:0 0 0.75rem;">
                            <?= esc($subSection['title']) ?>
                        </p>
                    <?php endif; ?>
                <?php
                        endif;

                        $renderSubSection($section, $subSection, $values, $isMerged);
                    endforeach;
                ?>

                <div class="section-actions">
                    <button class="btn btn-primary" type="submit">Save section</button>
                </div>
            </form>

        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>