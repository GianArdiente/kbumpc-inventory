<?php

$license = [
    'product_id' => 'KBUMPC-IMS',
    'license_id' => 'KBUMPC-2026-001',
    'organization' => 'KBUMPC',
    'issued_at' => date('c'),
];

$privateKeyPath = __DIR__ . '/private/signing-private.pem';

if (! file_exists($privateKeyPath)) {
    throw new RuntimeException(
        'Private signing key was not found.'
    );
}

$privateKeyPem = file_get_contents($privateKeyPath);

$privateKey = openssl_pkey_get_private($privateKeyPem);

if ($privateKey === false) {
    throw new RuntimeException(
        'Unable to load the private signing key.'
    );
}

$payload = json_encode(
    $license,
    JSON_UNESCAPED_SLASHES
);

if ($payload === false) {
    throw new RuntimeException(
        'Unable to encode license payload.'
    );
}

$signature = '';

$signingSucceeded = openssl_sign(
    $payload,
    $signature,
    $privateKey,
    OPENSSL_ALGO_SHA256
);

if (! $signingSucceeded) {
    throw new RuntimeException(
        'Unable to sign license payload.'
    );
}

$output = [
    'license' => $license,
    'signature' => base64_encode($signature),
];

$licenseJson = json_encode(
    $output,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if ($licenseJson === false) {
    throw new RuntimeException(
        'Unable to encode signed license.'
    );
}

$outputDirectory = __DIR__ . '/issued';

if (! is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0755, true);
}

$outputPath = $outputDirectory
    . '/'
    . $license['license_id']
    . '.license';

file_put_contents(
    $outputPath,
    $licenseJson
);

echo "License generated successfully." . PHP_EOL;
echo "License ID: " . $license['license_id'] . PHP_EOL;
echo "Saved to: " . $outputPath . PHP_EOL;