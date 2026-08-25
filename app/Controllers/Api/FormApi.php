<?php

namespace App\Controllers\Api;

use App\Libraries\FormService;
use CodeIgniter\RESTful\ResourceController;

/**
 * GET api/v1/forms                      -> index()
 * GET api/v1/forms/{form_key}           -> show()   ?asr_id=N  ?data=0
 * GET api/v1/forms/{form_key}/values    -> values() ?asr_id=N
 * GET api/v1/asr/{asr_no}/forms         -> byAsr()
 */
class FormApi extends ResourceController
{
    protected $format = 'json';

    private FormService $service;

    public function __construct()
    {
        $this->service = new FormService();
    }

    /** List all forms (id, form_key, name, group, status, section_count). */
    public function index()
    {
        $forms = array_map(
            static fn(array $f): array => [
            'id'                    => (int) $f['id'],
            'form_key'              => $f['form_key'],
            'name'                  => $f['name'],
            'group_name'            => $f['Group_name'] ?? null,
            'status'                => $f['status'],
            'builder_review_status' => $f['builder_review_status'] ?? null,
            'section_count'         => (int) $f['section_count'],
            'created_at'            => $f['created_at'] ?? null,
            'updated_at'            => $f['updated_at'] ?? null,
        ],
         $this->service->listForms()
        );

        return $this->ok(['forms' => $forms, 'total' => count($forms)]);
    }

    /** One form: structure + (unless ?data=0) saved values and section metadata. */
    public function show($formKey = null)
    {
        $form = $this->service->getFormByKey((string) $formKey);
        if (!$form) {
            return $this->failNotFound("Form '{$formKey}' not found.");
        }

        $asrId       = (int) ($this->request->getGet('asr_id') ?? $this->request->getGet('asr') ?? 0);
        $includeData = $this->request->getGet('data') !== '0';

        try {
            $payload = $this->service->buildPayload($form, $asrId, $includeData);
        } catch (\InvalidArgumentException $e) {
            return $this->failNotFound($e->getMessage());
        }

        return $this->ok($payload);
    }

    /** Only the saved values for a form */
    public function values($formKey = null)
    {
        $form = $this->service->getFormByKey((string) $formKey);
        if (!$form) {
            return $this->failNotFound("Form '{$formKey}' not found.");
        }

        $asrId = (int) ($this->request->getGet('asr_id') ?? $this->request->getGet('asr') ?? 0);
        if ($asrId > 0 && !$this->service->asrMappingExists($asrId, (int) $form['id'])) {
            return $this->failNotFound('Invalid ASR mapping for this form.');
        }

        $sections = $this->service->getStructure($this->service->resolveFormIds((int) $form['id']));

        return $this->ok([
            'form_id'          => (int) $form['id'],
            'form_key'         => $form['form_key'],
            'asr_id'           => $asrId,
            'values'           => (object) $this->service->getValues($sections, $asrId),
            'section_metadata' => (object) $this->service->getSectionMetadata($form, $sections, $asrId),
        ]);
    }

    /** Forms attached to an ASR number, with the asr_id to use in show(). */
    public function byAsr($asrNo = null)
    {
        $mappings = $this->service->getAsrMappings((string) $asrNo);
        if (empty($mappings)) {
            return $this->failNotFound("No forms mapped to ASR '{$asrNo}'.");
        }

        return $this->ok(['asr_no' => $asrNo, 'forms' => $mappings]);
    }

    private function ok(array $data)
    {
        return $this->respond(['status' => true, 'data' => $data]);
    }
}
