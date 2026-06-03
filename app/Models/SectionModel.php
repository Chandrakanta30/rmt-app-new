<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table      = 'sections';
    protected $returnType = 'array';

    /**
     * getSectionsWithFields
     *
     * 1. Fetches all sections for the given form IDs (ordered by `order`).
     * 2. Attaches each section's fields.
     * 3. Groups sections that share the same non-empty `merge_tag` into ONE
     *    display section.
     *
     * KEY CHANGE vs old version:
     *   Each absorbed section is stored inside `_merged_sections[]` with its
     *   OWN layout, inline_template, table, and fields intact.
     *   The view iterates `_merged_sections` to render every constituent
     *   section correctly — so Section B's grid fields appear even when
     *   Section A uses an inline template.
     *
     * Returned structure per primary section:
     *
     *   id                – primary section id  (POST key)
     *   title             – primary section title
     *   layout            – primary section layout
     *   table             – primary section table
     *   inline_template   – primary section template
     *   merge_tag         – shared tag (or null)
     *   fields            – ALL fields combined (for field-count badge etc.)
     *   _merged_tables    – [ ['id'=>, 'table'=>], … ] for every section
     *   _merged_titles    – titles of absorbed sections (display hint)
     *   _merged_sections  – FULL section rows for every member of the group
     *                       (primary first), each with their own layout/
     *                       inline_template/table/fields — used by form_view
     *                       to render each sub-section correctly.
     */
    public function getSectionsWithFields(array $formIds): array
    {
        $db = \Config\Database::connect();

        // ── 1. Raw sections ordered by `order` ───────────────────────────────
        $rows = $db->table('sections')
            ->whereIn('form_id', $formIds)
            ->orderBy('order', 'ASC')
            ->get()
            ->getResultArray();

        // ── 2. Attach fields ─────────────────────────────────────────────────
        foreach ($rows as &$row) {
            $row['fields'] = $db->table('fields')
                ->where('section_id', $row['id'])
                ->orderBy('order', 'ASC')
                ->get()
                ->getResultArray();
        }
        unset($row);

        // ── 3. Group by merge_tag ─────────────────────────────────────────────
        $output   = [];
        $tagIndex = [];

        foreach ($rows as $section) {
            $tag = trim((string)($section['merge_tag'] ?? ''));

            if ($tag === '') {
                // ── Normal section: wrap in _merged_sections so the view has
                //    one consistent structure to iterate over.
                $section['_merged_tables']   = [
                    ['id' => $section['id'], 'table' => $section['table'] ?? '']
                ];
                $section['_merged_titles']   = [];
                $section['_merged_sections'] = [$section]; // itself
                $output[] = $section;
                continue;
            }

            if (!isset($tagIndex[$tag])) {
                // ── First section in this merge group → becomes primary
                $section['_merged_tables']   = [
                    ['id' => $section['id'], 'table' => $section['table'] ?? '']
                ];
                $section['_merged_titles']   = [];
                $section['_merged_sections'] = [$section]; // starts with itself
                $tagIndex[$tag] = count($output);
                $output[]       = $section;
            } else {
                // ── Subsequent section → absorb into primary
                $idx = $tagIndex[$tag];

                // Combined field list (used for the "N fields" badge)
                $output[$idx]['fields'] = array_merge(
                    $output[$idx]['fields'],
                    $section['fields']
                );

                // Track table for data load/save
                $output[$idx]['_merged_tables'][] = [
                    'id'    => $section['id'],
                    'table' => $section['table'] ?? '',
                ];

                // Title hint
                $output[$idx]['_merged_titles'][] = $section['title'];

                // *** KEY: store the FULL absorbed section so the view can
                //     render it with its own layout and template ***
                $output[$idx]['_merged_sections'][] = $section;
            }
        }

        return $output;
    }
}