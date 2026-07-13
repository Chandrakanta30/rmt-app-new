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

if (!function_exists('audit_format_field_value')) {
    function audit_format_field_value($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_array($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $json === false ? '-' : $json;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}

if (!function_exists('audit_diff_fields')) {
    /**
     * Compares two section snapshots field-by-field and returns only the
     * fields whose value actually changed between them.
     */
    function audit_diff_fields($previous, $current): array
    {
        $previous = audit_normalize_body($previous) ?? [];
        $current  = audit_normalize_body($current) ?? [];

        $previous = audit_redact_sensitive_keys($previous);
        $current  = audit_redact_sensitive_keys($current);

        $keys = array_unique(array_merge(array_keys($previous), array_keys($current)));

        $diffs = [];

        foreach ($keys as $key) {
            $oldValue = $previous[$key] ?? null;
            $newValue = $current[$key] ?? null;

            if (json_encode($oldValue) === json_encode($newValue)) {
                continue;
            }

            $diffs[] = [
                'input'    => $key,
                'previous' => audit_format_field_value($oldValue),
                'current'  => audit_format_field_value($newValue),
            ];
        }

        return $diffs;
    }
}
