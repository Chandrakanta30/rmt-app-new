<?php

namespace App\Libraries;

use App\Models\FormModel;
use App\Models\SectionModel;
use CodeIgniter\Database\BaseConnection;


class FormService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * All forms with their section count 
     */
    public function listForms(): array
    {
        $forms = (new FormModel())->orderBy('name', 'ASC')->findAll();
        $sectionModel = new SectionModel();

        foreach ($forms as &$form) {
            $form['section_count'] = $sectionModel
                ->where('form_id', $form['id'])
                ->countAllResults();
        }
        unset($form);

        return $forms;
    }

    public function getFormByKey(string $formKey): ?array
    {
        return (new FormModel())->where('form_key', $formKey)->first();
    }

    /**
     * A composite form renders its child forms' sections instead of its own.
     */
    public function resolveFormIds(int $formId): array
    {
        $children = $this->db->table('form_compositions')
            ->select('child_form_id')
            ->where('parent_form_id', $formId)
            ->orderBy('order')
            ->get()
            ->getResultArray();

        return empty($children) ? [$formId] : array_map('intval', array_column($children, 'child_form_id'));
    }

    /**
     * Sections with nested fields. JSON text columns (options, validation)
     * are decoded so API consumers receive real objects.
     */
    public function getStructure(array $formIds): array
    {
        $sections = (new SectionModel())->getSectionsWithFields($formIds);

        foreach ($sections as &$section) {
            foreach ($section['fields'] as &$field) {
                $field['options']    = $this->decodeJson($field['options'] ?? null, []);
                $field['validation'] = $this->decodeJson($field['validation'] ?? null, []);
            }
            unset($field);
        }
        unset($section);

        return $sections;
    }

    /**
     * Forms mapped to an ASR number (form_asr_mapping, soft-deletes excluded).
     */
    public function getAsrMappings(string $asrNo): array
    {
        return $this->db->table('form_asr_mapping m')
            ->select('m.id AS asr_id, m.asr_no, m.form_id, f.form_key, f.name AS form_name, f.status AS form_status, m.created_at')
            ->join('forms f', 'f.id = m.form_id', 'left')
            ->where('m.asr_no', $asrNo)
            ->where('m.deleted_at IS NULL')
            ->orderBy('m.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function asrMappingExists(int $asrId, int $formId): bool
    {
        return (bool) $this->db->table('form_asr_mapping')
            ->select('id')
            ->where('id', $asrId)
            ->where('form_id', $formId)
            ->get()
            ->getRow();
    }

    /**
     * Saved values keyed by section_id, exactly as Form::index() loads them.
     */
    public function getValues(array $sections, int $asrId): array
    {
        $values     = [];
        $tableCache = null;

        foreach ($sections as $section) {
            $row = $this->db->table('form_values')
                ->where('section_id', $section['id'])
                ->where('asr_id', $asrId)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($row) {
                $values[$section['id']] = json_decode($row['values'], true);
                continue;
            }

            // Legacy fallback (non-ASR only): a section bound to its own table.
            $table = !empty($section['table']) ? $section['table'] : null;
            if ($asrId <= 0 && $table) {
                $tableCache ??= $this->db->listTables();
                if (in_array($table, $tableCache, true)) {
                    $tableRow = $this->db->table($table)->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($tableRow) {
                        $values[$section['id']] = $tableRow;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Per-section status / author / reviewer block, same as the web view.
     */
    public function getSectionMetadata(array $form, array $sections, int $asrId): array
    {
        $metadata = [];

        $reviewLog = $this->db->table('audit_logs a')
            ->select('a.created_at, u.name as reviewer_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->where('a.entity_id', $form['id'])
            ->where('a.module', 'forms')
            ->whereIn('a.action', ['approve', 'review', 'submit_review', 'accept'])
            ->orderBy('a.id', 'DESC')
            ->get()
            ->getRowArray();

        $formReviewer   = $reviewLog['reviewer_name'] ?? null;
        $formReviewedAt = $reviewLog['created_at'] ?? null;
        $tableCache     = null;

        foreach ($sections as $sec) {
            $createdAt = $createdBy = $secReviewer = $secReviewedAt = null;

            $fvQuery = $this->db->table('form_values')->where('section_id', $sec['id']);
            if ($asrId > 0) {
                $fvQuery->where('asr_id', $asrId);
            }
            $fv = $fvQuery->orderBy('id', 'DESC')->get()->getRowArray();

            if ($fv) {
                $createdAt = $fv['created_at'] ?? null;
                $createdBy = !empty($fv['created_by']) ? $this->userName((int) $fv['created_by']) : null;

                if (!$createdAt || !$createdBy) {
                    $log = $this->db->table('audit_logs a')
                        ->select('a.created_at, u.name as user_name')
                        ->join('users u', 'u.id = a.user_id', 'left')
                        ->where('a.entity_id', $fv['id'])
                        ->where('a.module', 'form_values')
                        ->orderBy('a.id', 'DESC')
                        ->get()
                        ->getRowArray();

                    if ($log) {
                        $createdAt = $createdAt ?: $log['created_at'];
                        $createdBy = $createdBy ?: $log['user_name'];
                    }
                }

                if (array_key_exists('reviewed_by', $fv)) {
                    if (!empty($fv['reviewed_by'])) {
                        $secReviewer   = $this->userName((int) $fv['reviewed_by']);
                        $secReviewedAt = $fv['reviewed_at'] ?? null;
                    }
                } else {
                    $secReviewer   = $formReviewer;
                    $secReviewedAt = $formReviewedAt;
                }
            }

            if (!$createdAt && !empty($sec['table'])) {
                $tableCache ??= $this->db->listTables();
                if (in_array($sec['table'], $tableCache, true)) {
                    $dtQuery = $this->db->table($sec['table']);
                    if ($asrId > 0 && in_array('asr_no', $this->db->getFieldNames($sec['table']), true)) {
                        $dtQuery->where('asr_no', $asrId);
                    }
                    $dt = $dtQuery->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($dt) {
                        $createdAt = $dt['created_at'] ?? null;
                        if (!empty($dt['created_by'])) {
                            $createdBy = $this->userName((int) $dt['created_by']);
                        }
                    }
                }
            }

            $metadata[$sec['id']] = [
                'table_name'        => !empty($sec['table']) ? $sec['table'] : 'form_values',
                'status'            => $fv['status'] ?? 'draft',
                'rejection_comment' => $fv['rejection_comment'] ?? null,
                'created_at'        => $createdAt ? date('d-m-Y H:i', strtotime($createdAt)) : 'N/A',
                'created_by'        => $createdBy ?: 'N/A',
                'reviewed_at'       => $secReviewedAt ? date('d-m-Y H:i', strtotime($secReviewedAt)) : 'N/A',
                'reviewed_by'       => $secReviewer ?: 'N/A',
            ];
        }

        return $metadata;
    }

    /**
     * Full payload for one form: the same data the web view receives.
     *
     * @throws \InvalidArgumentException when the ASR id is not mapped to the form.
     */
    public function buildPayload(array $form, int $asrId = 0, bool $includeData = true): array
    {
        if ($asrId > 0 && !$this->asrMappingExists($asrId, (int) $form['id'])) {
            throw new \InvalidArgumentException('Invalid ASR mapping for this form.');
        }

        $formIds  = $this->resolveFormIds((int) $form['id']);
        $sections = $this->getStructure($formIds);

        // ASR-scoped entries are always editable; direct access is gated by approval.
        $canEdit = $asrId > 0 ? true : (strtolower((string) $form['status']) === 'approved');

        return [
            'form'             => $form,
            'form_ids'         => $formIds,
            'is_composite'     => count($formIds) > 1 || $formIds[0] !== (int) $form['id'],
            'asr_id'           => $asrId,
            'can_edit'         => $canEdit,
            'sections'         => $sections,
            // Cast to object so an empty result serialises as {} (not []) for API consumers.
            'values'           => (object) ($includeData ? $this->getValues($sections, $asrId) : []),
            'section_metadata' => (object) ($includeData ? $this->getSectionMetadata($form, $sections, $asrId) : []),
        ];
    }

    private function userName(int $userId): ?string
    {
        $u = $this->db->table('users')->select('name')->where('id', $userId)->get()->getRowArray();

        return $u['name'] ?? null;
    }

    private function decodeJson(?string $json, $default)
    {
        if ($json === null || $json === '') {
            return $default;
        }
        $decoded = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }
}
