<?php

/**
 * The form review/approval workflow.

 */

if (!function_exists('workflow_statuses')) {

    function workflow_statuses(): array
    {
        return [
            'created'                  => 'Created',
            'pending_review'           => 'Pending for Review',
            'review_rejected'          => 'Review Rejected',
            'resubmitted_for_review'   => 'Resubmitted for Review',
            'review_completed'         => 'Review Completed',
            'pending_approval'         => 'Pending for Approval',
            'approval_rejected'        => 'Approval Rejected',
            'resubmitted_for_approval' => 'Resubmitted for Approval',
            'approved'                 => 'Approved',
        ];
    }
}

if (!function_exists('workflow_status_label')) {
    function workflow_status_label(?string $status): string
    {
        return workflow_statuses()[$status] ?? ucfirst((string) $status);
    }
}

if (!function_exists('workflow_actions')) {

    function workflow_actions(): array
    {
        return [
            'send_for_review' => [
                'label'      => 'Send for review',
                'icon'       => '&#9993;',      // envelope
                'variant'    => 'primary',
                'from'       => ['created'],
                'to'         => 'pending_review',
                'permission' => 'submit_data',
                'remark'     => false,
            ],
            'resubmit_for_review' => [
                'label'      => 'Resubmit for review',
                'icon'       => '&#8635;',      // clockwise arrow
                'variant'    => 'primary',
                'from'       => ['review_rejected'],
                'to'         => 'resubmitted_for_review',
                'permission' => 'submit_data',
                'remark'     => false,
            ],
            'review_complete' => [
                'label'      => 'Mark review complete',
                'icon'       => '&#10003;',     // check
                'variant'    => 'success',
                'from'       => ['pending_review', 'resubmitted_for_review'],
                'to'         => 'review_completed',
                'permission' => 'review_form',
                'remark'     => false,
            ],
            'review_reject' => [
                'label'      => 'Reject at review',
                'icon'       => '&#10005;',     // cross
                'variant'    => 'danger',
                'from'       => ['pending_review', 'resubmitted_for_review'],
                'to'         => 'review_rejected',
                'permission' => 'review_form',
                'remark'     => true,
            ],
            'send_for_approval' => [
                'label'      => 'Send for approval',
                'icon'       => '&#9993;',
                'variant'    => 'primary',
                'from'       => ['review_completed'],
                'to'         => 'pending_approval',
                'permission' => 'submit_data',
                'remark'     => false,
            ],
            'resubmit_for_approval' => [
                'label'      => 'Resubmit for approval',
                'icon'       => '&#8635;',
                'variant'    => 'primary',
                'from'       => ['approval_rejected'],
                'to'         => 'resubmitted_for_approval',
                'permission' => 'submit_data',
                'remark'     => false,
            ],
            'approve' => [
                'label'      => 'Approve',
                'icon'       => '&#10003;',
                'variant'    => 'success',
                'from'       => ['pending_approval', 'resubmitted_for_approval'],
                'to'         => 'approved',
                'permission' => 'approve_form',
                'remark'     => false,
            ],
            'approve_reject' => [
                'label'      => 'Reject at approval',
                'icon'       => '&#10005;',
                'variant'    => 'danger',
                'from'       => ['pending_approval', 'resubmitted_for_approval'],
                'to'         => 'approval_rejected',
                'permission' => 'approve_form',
                'remark'     => true,
            ],
        ];
    }
}

if (!function_exists('workflow_available_actions')) {

    function workflow_available_actions(?string $status): array
    {
        helper('auth');

        $status = $status ?: 'created';
        $available = [];

        foreach (workflow_actions() as $name => $action) {
            if (!in_array($status, $action['from'], true)) {
                continue;
            }
            if (!has_permission($action['permission'])) {
                continue;
            }
            $available[$name] = $action;
        }

        return $available;
    }
}

if (!function_exists('workflow_action')) {
    function workflow_action(string $name): ?array
    {
        return workflow_actions()[$name] ?? null;
    }
}

if (!function_exists('workflow_action_buttons')) {

    function workflow_action_buttons(array $form): string
    {
        $actions = workflow_available_actions($form['status'] ?? 'created');

        if (empty($actions)) {
            return '<span class="wf-none" title="No action available to you at this stage">&mdash;</span>';
        }

        $html = '<div class="wf-actions">';

        foreach ($actions as $name => $action) {
            $html .= '<button type="button"'
                . ' class="wf-btn wf-' . esc($action['variant'], 'attr') . '"'
                . ' title="' . esc($action['label'], 'attr') . '"'
                . ' aria-label="' . esc($action['label'], 'attr') . '"'
                . ' data-wf-trigger'
                . ' data-wf-form-id="' . esc((string) $form['id'], 'attr') . '"'
                . ' data-wf-form-name="' . esc((string) ($form['name'] ?? ''), 'attr') . '"'
                . ' data-wf-action="' . esc($name, 'attr') . '"'
                . ' data-wf-label="' . esc($action['label'], 'attr') . '"'
                . ' data-wf-remark-required="' . ($action['remark'] ? '1' : '0') . '">'
                . '<span aria-hidden="true">' . $action['icon'] . '</span>'
                . '</button>';
        }

        return $html . '</div>';
    }
}
