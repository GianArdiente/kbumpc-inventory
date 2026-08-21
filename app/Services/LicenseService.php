<?php

namespace App\Services;

class LicenseService
{
    public function organization(): string
    {
        return (string) config(
            'product.license.organization',
            'Unlicensed'
        );
    }

    public function licenseId(): ?string
    {
        return config(
            'product.license.license_id'
        );
    }

    public function isConfigured(): bool
    {
        return $this->organization() !== 'Unlicensed'
            && ! empty($this->licenseId());
    }

    public function isValid(): bool
    {
        $licensePath = config(
            'product.license.license_file'
        );

        $publicKeyPath = config(
            'product.license.public_key'
        );

        if (
            empty($licensePath)
            || empty($publicKeyPath)
        ) {
            return false;
        }

        return $this->verifyLicenseFile(
            $licensePath,
            $publicKeyPath
        );
    }

    public function verifyLicenseFile(
        string $licensePath,
        string $publicKeyPath
    ): bool {
        if (! file_exists($licensePath)) {
            return false;
        }

        if (! file_exists($publicKeyPath)) {
            return false;
        }

        $licenseJson = file_get_contents(
            $licensePath
        );

        if ($licenseJson === false) {
            return false;
        }

        $licenseData = json_decode(
            $licenseJson,
            true
        );

        if (
            ! is_array($licenseData)
            || ! isset($licenseData['license'])
            || ! isset($licenseData['signature'])
        ) {
            return false;
        }

        $payload = json_encode(
            $licenseData['license'],
            JSON_UNESCAPED_SLASHES
        );

        if ($payload === false) {
            return false;
        }

        $signature = base64_decode(
            $licenseData['signature'],
            true
        );

        if ($signature === false) {
            return false;
        }

        $publicKeyPem = file_get_contents(
            $publicKeyPath
        );

        if ($publicKeyPem === false) {
            return false;
        }

        $publicKey = openssl_pkey_get_public(
            $publicKeyPem
        );

        if ($publicKey === false) {
            return false;
        }

        $result = openssl_verify(
            $payload,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $result === 1;
    }
}