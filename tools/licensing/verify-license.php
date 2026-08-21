<?php

$publicKeyPath = __DIR__ . '/signing-public.pem';

if (! file_exists($publicKeyPath)) {
    throw new RuntimeException(
        'Public verification key was not found.'
    );
}

$publicKeyPem = file_get_contents($publicKeyPath);

$publicKey = openssl_pkey_get_public($publicKeyPem);

if ($publicKey === false) {
    throw new RuntimeException(
        'Unable to load the public verification key.'
    );
}

echo "Public verification key loaded successfully." . PHP_EOL;

$licensePath = __DIR__ . '/issued/KBUMPC-2026-001.license';

if (! file_exists($licensePath)) {
    throw new RuntimeException(
        'License file was not found.'
    );
}

$licenseJson = file_get_contents($licensePath);

$licenseData = json_decode(
    $licenseJson,
    true
);


if (! is_array($licenseData)) {
    throw new RuntimeException(
        'License file contains invalid JSON.'
    );
}

if (
    ! isset($licenseData['license'])
    || ! isset($licenseData['signature'])
) {
    throw new RuntimeException(
        'License file is missing required data.'
    );
}

echo "License file loaded successfully." . PHP_EOL;
echo "License ID: "
    . ($licenseData['license']['license_id'] ?? 'Unknown')
    . PHP_EOL;


    $payload = json_encode(
    $licenseData['license'],
    JSON_UNESCAPED_SLASHES
);

if ($payload === false) {
    throw new RuntimeException(
        'Unable to encode license payload for verification.'
    );
}

$signature = base64_decode(
    $licenseData['signature'],
    true
);

if ($signature === false) {
    throw new RuntimeException(
        'License signature is not valid Base64.'
    );
}

$verificationResult = openssl_verify(
    $payload,
    $signature,
    $publicKey,
    OPENSSL_ALGO_SHA256
);

if ($verificationResult === 1) {
    echo "License signature is VALID." . PHP_EOL;
} elseif ($verificationResult === 0) {
    echo "License signature is INVALID." . PHP_EOL;
} else {
    echo "License verification encountered an OpenSSL error." . PHP_EOL;
}