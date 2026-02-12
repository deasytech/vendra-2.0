<?php

namespace App\Exceptions;

use Exception;

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
}
