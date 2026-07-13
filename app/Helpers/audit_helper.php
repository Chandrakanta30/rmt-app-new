<?php

if (!function_exists('audit_normalize_body')) {
    function audit_normalize_body($body)
    {
        if ($body === null) {
            return null;
        }

        if (is_string($body)) {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $body = $decoded;
            } else {
                return ['value' => $body];
            }
        } elseif (is_object($body)) {
            $body = json_decode(json_encode($body), true);
        }

        if (!is_array($body)) {
            return ['value' => $body];
        }

        return $body;
    }
}

if (!function_exists('audit_redact_sensitive_keys')) {
    function audit_redact_sensitive_keys(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirm', 'token', 'csrf_test_name'];

        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveKeys, true)) {
                unset($data[$key]);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = audit_redact_sensitive_keys($value);
            }
        }

        return $data;
    }
}

if (!function_exists('audit_encode_body')) {
    function audit_encode_body($body): ?string
    {
        $normalized = audit_normalize_body($body);

        if ($normalized === null) {
            return null;
        }

        if (is_array($normalized)) {
            $normalized = audit_redact_sensitive_keys($normalized);
        }

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('audit_decode_body')) {
    function audit_decode_body(?string $body): ?array
    {
        if ($body === null || $body === '') {
            return null;
        }

        $decoded = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : ['value' => $body];
    }
}

if (!function_exists('audit_pretty_body')) {
    function audit_pretty_body($body): string
    {
        $normalized = audit_normalize_body($body);

        if ($normalized === null || $normalized === []) {
            return '-';
        }

        if (is_array($normalized)) {
            $normalized = audit_redact_sensitive_keys($normalized);
        }

        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json === false ? '-' : $json;
    }
}
