<?php

$privateKey = openssl_pkey_new([
    'private_key_bits' => 3072,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
]);

if ($privateKey === false) {
    while ($error = openssl_error_string()) {
        echo $error . PHP_EOL;
    }

    exit(1);
}

openssl_pkey_export($privateKey, $privateKeyPem);

file_put_contents(
    __DIR__ . '/private/signing-private.pem',
    $privateKeyPem
);

echo "Private signing key generated successfully." . PHP_EOL;

$details = openssl_pkey_get_details($privateKey);

if ($details === false || empty($details['key'])) {
    throw new RuntimeException(
        'Unable to extract the public verification key.'
    );
}

file_put_contents(
    __DIR__ . '/signing-public.pem',
    $details['key']
);

echo "Public verification key generated successfully." . PHP_EOL;