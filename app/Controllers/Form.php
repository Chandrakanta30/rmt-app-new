<?php

namespace App\Controllers;

use App\Models\FormModel;
use App\Models\SectionModel;
use App\Models\FieldModel;
use CodeIgniter\Controller;

class Form extends Controller
{
    public function listing()
    {
        $formModel = new FormModel();
        $sectionModel = new SectionModel();

        $forms = $formModel
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($forms as &$form) {
            $form['section_count'] = $sectionModel
                ->where('form_id', $form['id'])
                ->countAllResults();
        }

        return view('forms/list', [
            'forms' => $forms,
            'breadcrumb' => 'Forms',
        ]);
    }

    public function index($formKey = 'accuracyform')
    {

        // return "coming";
        $formModel = new FormModel();
        $sectionModel = new SectionModel();

        // 1. Get form
        $form = $formModel->where('form_key', $formKey)->first();



        if (!$form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }


        // 2. Check composite
        $db = \Config\Database::connect();

        // return $db;

        $childIds = $db->table('form_compositions')
            ->select('child_form_id')
            ->where('parent_form_id', $form['id'])
            ->orderBy('order')
            ->get()
            ->getResultArray();

        if (empty($childIds)) {
            $formIds = [$form['id']];
        } else {
            $formIds = array_column($childIds, 'child_form_id');
        }

        $dataValues = [];

        $sections = $sectionModel->getSectionsWithFields($formIds);

        foreach ($sections as $section) {
            // Table-less sections store submissions in form_values (keyed by section_id);
            // sections bound to a real table read their own latest row.
            $table = !empty($section['table']) ? $section['table'] : 'form_values';

            if ($table === 'form_values') {
                $row = $db->table('form_values')
                    ->where('section_id', $section['id'])
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($row) {
                    // values is a JSON array of row objects for repeatable tables,
                    // or a single object for grid/inline sections.
                    $dataValues[$section['id']] = json_decode($row['values'], true);
                }
            } else {
                if (!in_array($table, $db->listTables(), true)) {
                    continue;
                }

                $row = $db->table($table)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($row) {
                    $dataValues[$section['id']] = $row;
                }
            }
        }

        return view('form_view', [
            'form' => $form,
            'sections' => $sections,
            'values' => $dataValues,
            'breadcrumb' => $form['name'] ?? 'Form',
        ]);
    }

    public function submit()
    {
        $request = service('request');
        $db = \Config\Database::connect();
        $fieldModel = new FieldModel();

        $sections = $request->getPost('sections');


        if (!$sections) {
            return redirect()->back()->with('error', 'No data submitted');
        }

        $specialCharPattern = '/[^A-Za-z0-9\s]/';

        // Checkboxes only submit a value ("1") when ticked; an unticked box is
        // simply absent from the POST. Force every checkbox field to an explicit
        // "1"/"0" so the stored value is never an ambiguous empty string.
        $normalizeCheckboxes = static function (array $row, array $checkboxFields): array {
            foreach ($checkboxFields as $cb) {
                $row[$cb] = (isset($row[$cb]) && (string) $row[$cb] === '1') ? '1' : '0';
            }

            return $row;
        };

        foreach ($sections as $sectionId => $fields) {

            $tableNames = $request->getPost('table_name');
            $form_id = $request->getPost('form_id');
            $table = is_array($tableNames) ? ($tableNames[$sectionId] ?? null) : $tableNames;

            // Row-action mode: group/editable store many rows as an array;
            // singular (and grid/inline) store a single record object.
            $actionFlags = $request->getPost('action_flag');
            $actionFlag  = is_array($actionFlags) ? strtolower($actionFlags[$sectionId] ?? '') : '';
            $storeAsArray = in_array($actionFlag, ['group', 'editable'], true);

            // Field definitions for this section: used to coerce checkboxes to an
            // explicit 1/0 and (for real tables) to validate text-like fields.
            $sectionFieldDefs = $fieldModel->where('section_id', $sectionId)->findAll();
            $checkboxFields   = [];
            foreach ($sectionFieldDefs as $fieldDef) {
                if (strtolower((string) ($fieldDef['type'] ?? '')) === 'checkbox') {
                    $checkboxFields[] = $fieldDef['name'];
                }
            }

            // Repeatable table layouts submit each column as an array
            // (sections[sid][field][] -> [val0, val1, ...]). Detect that and
            // transpose the columns back into one record per row.
            $isRepeatable = false;
            foreach ((array) $fields as $value) {
                if (is_array($value)) {
                    $isRepeatable = true;
                    break;
                }
            }

            if ($isRepeatable) {
                // Inputs are indexed by row: sections[sid][field][rowIndex].
                // Collect the actual row indexes used (an unchecked checkbox or a
                // deleted row leaves gaps — iterating real keys keeps rows aligned).
                $rowIndexes = [];
                foreach ($fields as $value) {
                    if (is_array($value)) {
                        foreach (array_keys($value) as $idx) {
                            $rowIndexes[$idx] = true;
                        }
                    }
                }
                $rowIndexes = array_keys($rowIndexes);
                sort($rowIndexes, SORT_NUMERIC);

                $rows = [];
                foreach ($rowIndexes as $idx) {
                    $row = [];
                    foreach ($fields as $fieldName => $value) {
                        // Array columns vary per row; scalar columns repeat on every row.
                        $row[$fieldName] = is_array($value) ? ($value[$idx] ?? '') : $value;
                    }

                    // Skip rows the user left completely blank.
                    $hasData = false;
                    foreach ($row as $cell) {
                        if (is_string($cell) ? trim($cell) !== '' : !empty($cell)) {
                            $hasData = true;
                            break;
                        }
                    }
                    if ($hasData) {
                        $rows[] = $row;
                    }
                }
            } else {
                $rows = [$fields]; // single record (grid / inline / fixed table)
            }

            if ($table === 'form_values' || empty($table)) {

                // group/editable -> store every row as a JSON array (input 0, input 1, ...).
                // singular / grid / inline -> keep a single record object.
                // Coerce checkboxes to "1"/"0" on the rows we actually keep (done
                // after the blank-row skip so an unticked box never revives a row).
                if ($storeAsArray) {
                    $payload = array_map(
                        static fn(array $r) => $normalizeCheckboxes($r, $checkboxFields),
                        $rows
                    );
                } else {
                    $payload = $normalizeCheckboxes($rows[0] ?? [], $checkboxFields);
                }

                // Replace this section's previous record so the saved set always
                // reflects the full current table (rows accumulate, no duplicates).
                $db->table('form_values')->where('section_id', $sectionId)->delete();

                $db->table('form_values')->insert([
                    'form_id'    => $form_id[$sectionId] ?? null,
                    'section_id' => $sectionId,
                    'values'     => json_encode($payload),
                ]);
            } else {

                // ⚠️ SECURITY: validate table name
                $allowedTables = $db->listTables();
                if (!in_array($table, $allowedTables, true)) {
                    continue;
                }

                $textLikeFieldNames = [];
                foreach ($sectionFieldDefs as $fieldDef) {
                    $fieldType = strtolower((string) ($fieldDef['type'] ?? ''));
                    if (in_array($fieldType, ['text', 'search', 'tel', 'url', 'email'], true)) {
                        $textLikeFieldNames[] = $fieldDef['name'];
                    }
                }

                // Insert one DB row per submitted row.
                foreach ($rows as $row) {
                    $row = $normalizeCheckboxes($row, $checkboxFields);
                    foreach ($row as $fieldName => $value) {
                        if (!in_array($fieldName, $textLikeFieldNames, true) || !is_string($value) || $value === '') {
                            continue;
                        }

                        if (preg_match($specialCharPattern, $value)) {
                            return redirect()->back()->withInput()->with(
                                'error',
                                'Special characters are not allowed in "' . $fieldName . '". Use only letters, numbers, and spaces.'
                            );
                        }
                    }

                    $db->table($table)->insert($row);
                }
            }
        }


        return redirect()->back()->with('success', 'Saved successfully');
        // return redirect('http://localhost:8888/code4/public/index.php/form')->with('success', 'Saved successfully');
        // return redirect()->back()->with('success', 'Saved successfully');
    }
    public function intermediateList()
    {
        $sections = $this->instrumentSections();

        return view('pages/instrument', [
            'breadcrumb' => 'Intermediate Precision',
            'instrumentTitle' => 'Instrument Method',
            'instrumentSections' => $sections,
            'instrumentValues' => $this->instrumentValues(),
        ]);
    }

    private function instrumentValues(): array
    {
        return [];
    }

    private function instrumentSections(): array
    {
        $field = static function (string $label, string $name, string $default, int $span = 1): array {
            return [
                'label' => $label,
                'name' => $name,
                'default' => $default,
                'span' => $span,
            ];
        };

        return [
            [
                'title' => 'Particle Type',
                'rows' => [
                    [
                        $field('Non-spherical particle mode', 'non_spherical_particle_mode', 'Yes'),
                        $field('Is Fraunhofer type', 'is_fraunhofer_type', 'No'),
                    ],
                ],
            ],
            [
                'title' => 'Material Properties',
                'rows' => [
                    [
                        $field('Material name', 'material_name', 'Pantoprazole sodium USP (PAS)'),
                        $field('Refractive index', 'refractive_index', '1.600'),
                    ],
                    [
                        $field('Absorption index', 'absorption_index', '0.001'),
                        $field('Particle density', 'particle_density', '1.05 g/cm3'),
                    ],
                    [
                        $field('Different optical properties in blue light', 'different_optical_properties_in_blue_light', 'Yes'),
                        $field('Refractive index (in blue light)', 'refractive_index_blue_light', '1.600'),
                    ],
                    [
                        $field('Absorption index (in blue light)', 'absorption_index_blue_light', '0.001', 3),
                    ],
                ],
            ],
            [
                'title' => 'Dispersant Properties',
                'rows' => [
                    [
                        $field('Dispersant name', 'dispersant_name', 'n-Hexane'),
                        $field('Refractive index', 'dispersant_refractive_index', '1.380'),
                    ],
                    [
                        $field('Level sensor threshold', 'level_sensor_threshold', '5.000', 3),
                    ],
                ],
            ],
            [
                'title' => 'Measurement Duration',
                'rows' => [
                    [
                        $field('Background measurement duration (red)', 'background_measurement_duration_red', '5.00 sec'),
                        $field('Sample measurement duration (red)', 'sample_measurement_duration_red', '30 sec'),
                    ],
                    [
                        $field('Perform blue light measurement?', 'perform_blue_light_measurement', 'Yes'),
                        $field('Background measurement duration (blue)', 'background_measurement_duration_blue', '5.00 sec'),
                    ],
                    [
                        $field('Sample measurement duration (blue)', 'sample_measurement_duration_blue', 'Yes'),
                        $field('Assess light background stability', 'assess_light_background_stability', 'No'),
                    ],
                ],
            ],
            [
                'title' => 'Measurement Sequence',
                'rows' => [
                    [
                        $field('Aliquots', 'aliquots', '5.00 sec'),
                        $field('Automatic number of measurements', 'automatic_number_of_measurements', '30 sec'),
                    ],
                    [
                        $field('Pre-alignment delay', 'pre_alignment_delay', 'Yes'),
                        $field('Number of measurements', 'number_of_measurements', '5.00 sec'),
                    ],
                    [
                        $field('SDelay between measurements', 'delay_between_measurements', 'Yes'),
                        $field('Pre-measurement delay', 'pre_measurement_delay', 'No'),
                    ],
                    [
                        $field('Close measurement window after measurement', 'close_measurement_window_after_measurement', 'No', 3),
                    ],
                ],
            ],
            [
                'title' => 'Measurement Obscuration Settings',
                'rows' => [
                    [
                        $field('Auto start measurement', 'auto_start_measurement', '5.00 sec'),
                        $field('Obscuration low limit', 'obscuration_low_limit', '30 sec'),
                    ],
                    [
                        $field('Obscuration high limit', 'obscuration_high_limit', 'Yes'),
                        $field('Enable obscuration filtering', 'enable_obscuration_filtering', '5.00 sec'),
                    ],
                ],
            ],
            [
                'title' => 'Measurement Alarms',
                'rows' => [
                    [
                        $field('Use Background Check', 'use_background_check', '5.00 sec'),
                        $field('Background Check Limits', 'background_check_limits', '30 sec'),
                    ],
                ],
            ],
            [
                'title' => 'Accessory Control Settings',
                'rows' => [
                    [
                        $field('Accessory name', 'accessory_name', '5.00 sec'),
                        $field('Is accessory dry?', 'is_accessory_dry', '30 sec'),
                    ],
                    [
                        $field('Stirrer speed', 'stirrer_speed', 'Yes'),
                        $field('Ultrasound percentage', 'ultrasound_percentage', '5.00 sec'),
                    ],
                    [
                        $field('Fill dispersant source identifier', 'fill_dispersant_source_identifier', 'Yes'),
                        $field('Manual tank fill?', 'manual_tank_fill', 'No'),
                    ],
                    [
                        $field('Degas after tank and cell fill', 'degas_after_tank_and_cell_fill', 'Yes'),
                        $field('Sonicate to stability?', 'sonicate_to_stability', 'No'),
                    ],
                    [
                        $field('Ultrasound mode', 'ultrasound_mode', 'No', 3),
                    ],
                ],
            ],
            [
                'title' => 'Pre-Measurement Clean Sequence Settings',
                'rows' => [
                    [
                        $field('Pre-clean sequence type', 'pre_clean_sequence_type', '5.00 sec'),
                        $field('Sonicate During Pre-Clean?', 'sonicate_during_pre_clean', '30 sec'),
                    ],
                    [
                        $field('Manually Fill Tank During Pre-Clean?', 'manually_fill_tank_during_pre_clean', 'Yes'),
                        $field('Pre-Clean Dispersant Source Identifier', 'pre_clean_dispersant_source_identifier', '5.00 sec'),
                    ],
                    [
                        $field('Pre-clean Dispersant Level Sensor Threshold', 'pre_clean_dispersant_level_sensor_threshold', 'Yes'),
                        $field('Degas After Pre-Clean?', 'degas_after_pre_clean', 'No'),
                    ],
                    [
                        $field('Drain Valve Flush?', 'drain_valve_flush_pre_clean', 'Yes'),
                        $field('Tank Overfill?', 'tank_overfill_pre_clean', 'No'),
                    ],
                ],
            ],
            [
                'title' => 'Post-Measurement Clean Sequence Settings',
                'rows' => [
                    [
                        $field('Clean sequence type', 'clean_sequence_type', '5.00 sec'),
                        $field('Sonicate during clean?', 'sonicate_during_clean', '30 sec'),
                    ],
                    [
                        $field('Manually Fill Tank During Clean?', 'manually_fill_tank_during_clean', 'Yes'),
                        $field('Pre-clean Dispersant Source Identifier', 'pre_clean_dispersant_source_identifier_clean', '5.00 sec'),
                    ],
                    [
                        $field('Pre-Clean Dispersant Level Sensor Threshold', 'pre_clean_dispersant_level_sensor_threshold_clean', 'Yes'),
                        $field('Degas After Clean?', 'degas_after_clean', 'No'),
                    ],
                    [
                        $field('Drain Valve Flush?', 'drain_valve_flush_post_clean', 'No'),
                        $field('Tank Overfill?', 'tank_overfill_post_clean', 'No'),
                    ],
                ],
            ],
            [
                'title' => 'Analysis Settings',
                'rows' => [
                    [
                        $field('Analysis model', 'analysis_model', 'No', 3),
                    ],
                    [
                        $field('Blue light detectors excluded', 'blue_light_detectors_excluded', '5.00 sec'),
                        $field('Fine powder mode', 'fine_powder_mode', '30 sec'),
                    ],
                    [
                        $field('Analysis sensitivity', 'analysis_sensitivity', 'Yes'),
                        $field('Analysed as Mastersizer 3000E?', 'analysed_as_mastersizer_3000e', '5.00 sec'),
                    ],
                    [
                        $field('Close measurement window after measurement', 'close_measurement_window_after_measurement_analysis', 'No', 3),
                    ],
                ],
            ],
            [
                'title' => 'Result Settings',
                'rows' => [
                    [
                        $field('Result range is limited', 'result_range_is_limited', '5.00 sec'),
                        $field('Result Unit', 'result_unit', '30 sec'),
                    ],
                    [
                        $field('Extend Result', 'extend_result', 'Yes'),
                        $field('Result Emulation', 'result_emulation', '5.00 sec'),
                    ],
                ],
            ],
            [
                'title' => 'User Sizes for Histograms and Tables',
                'rows' => [
                    [
                        $field('Use user sizes', 'use_user_sizes', 'No', 3),
                    ],
                ],
            ],
            [
                'title' => 'Data Export Output',
                'rows' => [
                    [
                        $field('Enabled?', 'data_export_enabled', 'No', 3),
                    ],
                ],
            ],
            [
                'title' => 'Averaging',
                'rows' => [
                    [
                        $field('Averaging enabled?', 'averaging_enabled', 'No', 3),
                    ],
                ],
            ],
            [
                'title' => 'Printing Options',
                'rows' => [
                    [
                        $field('Printing enabled?', 'printing_enabled', 'No', 3),
                    ],
                ],
            ],
        ];
    }
}
