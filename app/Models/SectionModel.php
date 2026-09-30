<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table = 'sections';
    protected $returnType = 'array';

    public function getSectionsWithFields($formIds)
    {
        $db = \Config\Database::connect();

        $sections = $db->table('sections')
            ->whereIn('form_id', $formIds)
            // ->where('table IS NOT NULL')
            // ->orderBy('order')
            // ->where("table IS NOT NULL")
            ->get()
            ->getResultArray();

        foreach ($sections as &$section) {

            $fields = $db->table('fields')
                ->where('section_id', $section['id'])
                ->orderBy('order')
                ->get()
                ->getResultArray();

            $section['fields'] = $fields;
        }

        return $sections;
    }

    /**
     * Group sections for display
     * @return array<int, array{title: ?string, sections: array}>
     */
    public static function groupByMergeTag(array $sections): array
    {
        $blocks = [];
        $blockIndex = [];

        foreach ($sections as $section) {
            $tag = trim((string) ($section['merge_tag'] ?? ''));

            if ($tag === '') {
                $blocks[] = ['title' => null, 'sections' => [$section]];
                continue;
            }

            // Composite forms mix child forms, and a cloned child can reuse a tag.
            $key = $section['form_id'] . "\0" . $tag;

            if (!isset($blockIndex[$key])) {
                $blockIndex[$key] = count($blocks);
                $blocks[] = ['title' => $tag, 'sections' => []];
            }

            $blocks[$blockIndex[$key]]['sections'][] = $section;
        }

        // A block of one is just a section.
        foreach ($blocks as &$block) {
            if (count($block['sections']) === 1) {
                $block['title'] = null;
            }
        }
        unset($block);

        return $blocks;
    }

    // Least-advanced first: a block is only as far along as its weakest section.
    private const STATUS_ORDER = ['rejected', 'draft', 'submitted', 'under_review', 'approved'];

    /**
     * One metadata footer for a merged block, combined from its sections:
     * @param  array  $sections         the block's sections
     * @param  array  $sectionMetadata  per-section metadata, keyed by section id
     */
    public static function combineBlockMetadata(array $sections, array $sectionMetadata): array
    {
        $metas = [];
        foreach ($sections as $section) {
            $metas[] = $sectionMetadata[$section['id']] ?? [];
        }

        // Earliest save, with whoever made it.
        $created = null;
        foreach ($metas as $meta) {
            if (empty($meta['created_at_raw'])) {
                continue;
            }
            if ($created === null || strtotime($meta['created_at_raw']) < strtotime($created['created_at_raw'])) {
                $created = $meta;
            }
        }

        // Latest review — but only when every section has been reviewed.
        $reviewed = null;
        $reviewedCount = 0;
        foreach ($metas as $meta) {
            if (empty($meta['reviewed_at_raw'])) {
                continue;
            }
            $reviewedCount++;
            if ($reviewed === null || strtotime($meta['reviewed_at_raw']) > strtotime($reviewed['reviewed_at_raw'])) {
                $reviewed = $meta;
            }
        }
        $allReviewed = $reviewedCount === count($metas);

        $tables = array_values(array_unique(array_filter(array_column($metas, 'table_name'))));

        $ranks = [];
        foreach ($metas as $meta) {
            $rank = array_search($meta['status'] ?? 'draft', self::STATUS_ORDER, true);
            $ranks[] = $rank === false ? 0 : $rank;
        }

        return [
            'table_name'  => $tables ? implode(', ', $tables) : 'form_values',
            'status'      => self::STATUS_ORDER[$ranks ? min($ranks) : 1],
            'created_at'  => $created['created_at'] ?? 'N/A',
            'created_by'  => $created['created_by'] ?? 'N/A',
            'reviewed_at' => $allReviewed && $reviewed ? $reviewed['reviewed_at'] : ($reviewedCount ? 'Partially reviewed' : 'N/A'),
            'reviewed_by' => $allReviewed && $reviewed ? $reviewed['reviewed_by'] : 'N/A',
        ];
    }
}