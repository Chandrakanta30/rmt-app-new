<?php

namespace App\Controllers;

use Config\Database;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = Database::connect();

        // 1. No. of forms created
        $formsCount = $db->table('forms')->countAllResults();

        // 2. No. of ASR created
        $asrCount = $db->table('form_asr_mapping')
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        // 3. No. of ASR forms created (Total form_values entries)
        $asrFormsCount = $db->table('form_values')->countAllResults();

        // 4. No. of ASR forms in draft
        $draftCount = $db->table('form_values')
            ->groupStart()
                ->where('status', 'draft')
                ->orWhere('status', '')
                ->orWhere('status IS NULL', null, false)
            ->groupEnd()
            ->countAllResults();

        // 5. No. of ASR forms in review
        $reviewCount = $db->table('form_values')
            ->whereIn('status', ['under_review', 'submitted', 'pending_review'])
            ->countAllResults();

        // 6. No. of ASR forms in approved
        $approvedCount = $db->table('form_values')
            ->where('status', 'approved')
            ->countAllResults();

        // 7. No. of ASR forms in rejected
        $rejectedCount = $db->table('form_values')
            ->whereIn('status', ['rejected', 'review_rejected', 'approval_rejected'])
            ->countAllResults();

        $data = [
            'formsCount'     => $formsCount,
            'asrCount'       => $asrCount,
            'asrFormsCount'  => $asrFormsCount,
            'draftCount'     => $draftCount,
            'reviewCount'    => $reviewCount,
            'approvedCount'  => $approvedCount,
            'rejectedCount'  => $rejectedCount,
        ];

        return view('pages/dashboard', $data);
    }
}