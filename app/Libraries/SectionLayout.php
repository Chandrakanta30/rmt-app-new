<?php

namespace App\Libraries;

/** Builds display groups without changing section IDs or stored values. */
final class SectionLayout
{
    public function build(array $sections): array
    {
        $formOrder = [];
        foreach ($sections as $section) {
            $formOrder[$section['form_id']] ??= count($formOrder);
        }
        usort($sections, static fn (array $a, array $b): int =>
            [$formOrder[$a['form_id']], $a['position'] ?? $a['order'] ?? 0, $a['order'] ?? 0, $a['id']]
            <=> [$formOrder[$b['form_id']], $b['position'] ?? $b['order'] ?? 0, $b['order'] ?? 0, $b['id']]);

        $byId = array_column($sections, null, 'id');
        $children = [];
        foreach ($sections as $section) {
            $parent = $section['parent_section_id'] ?? null;
            $seen = [$section['id'] => true];
            $ancestor = $parent;
            while ($ancestor !== null) {
                // Keep invalid/orphaned relationships visible as root sections.
                if (isset($seen[$ancestor]) || !isset($byId[$ancestor])
                    || $byId[$ancestor]['form_id'] != $section['form_id']) {
                    $parent = null;
                    break;
                }
                $seen[$ancestor] = true;
                $ancestor = $byId[$ancestor]['parent_section_id'] ?? null;
            }
            $children[$parent ?? 0][] = $section;
        }

        $groupSiblings = static function (array $siblings): array {
            $groups = [];
            foreach ($siblings as $section) {
                $tag = trim((string) ($section['merge_tag'] ?? ''));
                $key = $section['form_id'] . ':' . ($tag === '' ? 'id:' . $section['id'] : 'tag:' . $tag);
                $groups[$key]['title'] ??= $tag !== '' ? $tag : $section['title'];
                $groups[$key]['members'][] = $section;
            }
            return array_values($groups);
        };

        $append = function (array $group, string $number, int $depth, array &$entries) use (&$append, $children, $groupSiblings): void {
            $merged = count($group['members']) > 1;
            $childOrdinal = 1;
            foreach ($group['members'] as $index => $section) {
                $sectionNumber = $number;
                if ($depth > 0) {
                    $parts = explode('.', $number);
                    $parts[count($parts) - 1] = (string) ((int) end($parts) + $index);
                    $sectionNumber = implode('.', $parts);
                    $childOrdinal = 1;
                }
                $entries[] = [
                    'section' => $section,
                    'number' => $sectionNumber,
                    'depth' => $depth,
                    'merge_heading' => $merged && $index === 0 && $depth > 0 ? $group['title'] : null,
                    'show_metadata' => $index === count($group['members']) - 1,
                    'save_section_ids' => array_column($group['members'], 'id'),
                ];
                foreach ($groupSiblings($children[$section['id']] ?? []) as $childGroup) {
                    $append($childGroup, $sectionNumber . '.' . $childOrdinal, $depth + 1, $entries);
                    $childOrdinal += count($childGroup['members']);
                }
            }
        };

        $result = [];
        foreach ($groupSiblings($children[0] ?? []) as $index => $group) {
            $entries = [];
            $append($group, (string) ($index + 1), 0, $entries);
            $result[] = ['title' => $group['title'], 'number' => (string) ($index + 1), 'entries' => $entries];
        }
        return $result;
    }
}
