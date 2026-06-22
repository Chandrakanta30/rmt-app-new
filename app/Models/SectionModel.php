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
}