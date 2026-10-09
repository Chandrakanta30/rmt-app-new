<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table = 'sections';
    protected $returnType = 'array';

    private const DEFAULT_FORMATTING = [
        'font_family' => 'times_new_roman',
        'font_size_pt' => 12,
        'table_header_font_size_pt' => 12,
        'table_label_font_size_pt' => 12,
        'text_alignment' => 'justify',
    ];

    private const MIN_TABLE_TEXT_SIZE = 6;
    private const MAX_TABLE_TEXT_SIZE = 30;
    private const TEXT_ALIGNMENTS = ['left', 'center', 'right', 'justify'];

    private function normalizeFormatting($formatting): array
    {
        if (is_string($formatting)) {
            $formatting = json_decode($formatting, true);
        }

        if (!is_array($formatting)) {
            $formatting = [];
        }

        $headerFontSize = (int) ($formatting['table_header_font_size_pt'] ?? self::DEFAULT_FORMATTING['table_header_font_size_pt']);
        $labelFontSize = (int) ($formatting['table_label_font_size_pt'] ?? self::DEFAULT_FORMATTING['table_label_font_size_pt']);
        $textAlignment = (string) ($formatting['text_alignment'] ?? self::DEFAULT_FORMATTING['text_alignment']);

        return [
            'font_family' => self::DEFAULT_FORMATTING['font_family'],
            'font_size_pt' => self::DEFAULT_FORMATTING['font_size_pt'],
            'table_header_font_size_pt' => max(self::MIN_TABLE_TEXT_SIZE, min(self::MAX_TABLE_TEXT_SIZE, $headerFontSize)),
            'table_label_font_size_pt' => max(self::MIN_TABLE_TEXT_SIZE, min(self::MAX_TABLE_TEXT_SIZE, $labelFontSize)),
            'text_alignment' => in_array($textAlignment, self::TEXT_ALIGNMENTS, true)
                ? $textAlignment
                : self::DEFAULT_FORMATTING['text_alignment'],
        ];
    }

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

            $section['formatting'] = $this->normalizeFormatting($section['formatting'] ?? null);

            $fields = $db->table('fields')
                ->where('section_id', $section['id'])
                ->orderBy('order')
                ->get()
                ->getResultArray();

            $section['fields'] = $fields;
        }

        return $sections;
    }
}
