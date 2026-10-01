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
            // Form Builder persists the visible section/subsection sequence in
            // `position`.  `order` is a legacy creation/order value and can
            // remain stale after a builder reorder.
            ->orderBy('position', 'ASC')
            ->orderBy('order', 'ASC')
            ->orderBy('id', 'ASC')
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
        // Break the reference created by the loop above. Without this, the
        // following loops overwrite the last subsection with the previous
        // section's data (for example, 2.2 "hen" becomes a second "cat").
        unset($section);

        // Form Builder represents a subsection by setting parent_section_id.
        // Keep RMT's existing flat renderer, but place every parent immediately
        // before its children and expose the relationship for the view to render.
        $byParent = [];
        $sectionIds = [];

        foreach ($sections as $section) {
            $sectionIds[(int) $section['id']] = true;
            $parentId = (int) ($section['parent_section_id'] ?? 0);
            $byParent[$parentId][] = $section;
        }

        $ordered = [];
        $appendSection = static function (array $section, int $depth = 0, string $sectionNumber = '') use (&$appendSection, &$ordered, &$byParent, $sectionIds): void {
            $parentId = (int) ($section['parent_section_id'] ?? 0);
            // A missing parent is treated as a top-level section, so no section
            // disappears if a builder record has been removed.
            $section['is_subsection'] = $parentId > 0 && isset($sectionIds[$parentId]);
            $section['section_depth'] = $depth;
            $section['section_number'] = $sectionNumber;
            $ordered[] = $section;

            $children = $byParent[(int) $section['id']] ?? [];
            $childNumber = 0;
            foreach ($children as $child) {
                $childNumber++;
                $appendSection($child, $depth + 1, $sectionNumber . '.' . $childNumber);
            }
        };

        $rootNumber = 0;
        foreach ($byParent[0] ?? [] as $section) {
            $rootNumber++;
            $appendSection($section, 0, (string) $rootNumber);
        }

        // Include records whose parent is not in this form's result set.
        foreach ($sections as $section) {
            $parentId = (int) ($section['parent_section_id'] ?? 0);
            if ($parentId > 0 && !isset($sectionIds[$parentId])) {
                $rootNumber++;
                $appendSection($section, 0, (string) $rootNumber);
            }
        }

        return $ordered;
    }
}
