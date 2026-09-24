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
}