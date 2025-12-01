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

    // Create a more compact data structure for QR code
    $data = json_encode([
      'irn' => $message,
      'cert' => substr($certificate, 0, 50), // Use only first 50 chars of certificate for brevity
      'ts' => $timestamp,
    ], JSON_UNESCAPED_SLASHES);

    // Encrypt with public key
    $encrypted = null;
    $ok = openssl_public_encrypt($data, $encrypted, $publicKeyPem, OPENSSL_PKCS1_OAEP_PADDING);

    if (! $ok) {
      throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
    }

    // Return Base64 string for QR (truncated to fit QR code capacity)
    $base64 = base64_encode($encrypted);

    // QR codes have a maximum capacity, especially at smaller sizes
    // For a 200px QR code at medium error correction, we can fit about 200-300 characters
    // Let's truncate to 250 characters to be safe
    if (strlen($base64) > 250) {
      $base64 = substr($base64, 0, 250);
    }

    return $base64;
  }
}
