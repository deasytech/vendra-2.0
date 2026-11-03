<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class TaxlyApiException extends Exception
{
  public ?string $details;

  public function __construct(string $message, int $code = 0, ?\Throwable $previous = null, ?string $details = null)
  {
    parent::__construct($message, $code, $previous);
    $this->details = $details;
  }

  public function getDetails(): ?string
  {
    return $this->details;
  }

  /**
   * The most specific human-readable error FIRS/Taxly returned for this exception.
   * Prefers the detailed reason over the generic headline (e.g. "FIRS Invoice validation failed").
   */
  public static function readable(Throwable $e): string
  {
    if ($e instanceof self) {
      return trim((string) $e->details) !== '' ? $e->details : $e->getMessage();
    }

    // Legacy: a JSON body embedded in a plain exception message
    if (preg_match('/\{.*\}/s', $e->getMessage(), $matches)) {
      $json = json_decode($matches[0], true);

      if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        $error = $json['data']['error'] ?? null;

        foreach ([$error['details'] ?? null, $error['public_message'] ?? null, $json['message'] ?? null] as $candidate) {
          if (!empty($candidate) && is_string($candidate)) {
            return $candidate;
          }
        }
      }
    }

    return $e->getMessage();
  }
}
