<?php

namespace App\Services;

class FirsQrService
{
  public function generateEncryptedQrPayload(string $irn): string
  {
    $certificate = base64_decode(config('services.firs.certificate'));
    $publicKeyPem = base64_decode(config('services.firs.public_key'));

    // Ensure PEM formatting
    if (! str_contains($publicKeyPem, 'BEGIN PUBLIC KEY')) {
      $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n"
        . chunk_split(trim($publicKeyPem), 64, "\n")
        . "-----END PUBLIC KEY-----\n";
    }

    // Append timestamp
    $timestamp = time();
    $message = $irn . '.' . $timestamp;

    $data = json_encode([
      'irn' => $message,
      'certificate' => $certificate,
    ], JSON_UNESCAPED_SLASHES);

    // Encrypt with public key
    $encrypted = null;
    $ok = openssl_public_encrypt($data, $encrypted, $publicKeyPem, OPENSSL_PKCS1_OAEP_PADDING);

    if (! $ok) {
      throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
    }

    // Return Base64 string for QR
    return base64_encode($encrypted);
  }
}
