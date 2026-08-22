<?php

function getMailConfig(): array
{
    $defaults = [
        'host' => getenv('SMTP_HOST') ?: '',
        'port' => (int)(getenv('SMTP_PORT') ?: 587),
        'username' => getenv('SMTP_USERNAME') ?: '',
        'password' => getenv('SMTP_PASSWORD') ?: '',
        'encryption' => strtolower(trim((string)(getenv('SMTP_ENCRYPTION') ?: 'tls'))),
        'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'no-reply@asistencias.speeddev.mx',
        'from_name' => getenv('SMTP_FROM_NAME') ?: 'Control de Asistencias',
    ];

    $localFile = __DIR__ . '/mail.local.php';
    if (is_file($localFile)) {
        $localConfig = require $localFile;
        if (is_array($localConfig)) {
            return array_merge($defaults, array_filter($localConfig, static fn ($value) => $value !== null && $value !== ''));
        }
    }

    return $defaults;
}
