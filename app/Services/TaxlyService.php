<?php

namespace App\Services;

use App\Exceptions\TaxlyApiException;
use App\Models\TaxlyCredential;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaxlyService
{
    protected string $baseUrl;
    protected ?TaxlyCredential $credential;
    protected array $headers = [];

    public function __construct(?TaxlyCredential $credential = null)
    {
        $this->credential = $credential ?? TaxlyCredential::first();
        $this->baseUrl = $this->credential->base_url
            ?? config('services.taxly.base_url', 'https://dev.taxly.ng/api/v1');

        // Determine the API key or token
        $apiKey = $this->credential->api_key
            ?? config('services.taxly.api_key')   // fallback to .env key
            ?? env('TAXLY_API_KEY');              // direct env fallback

        $token = $this->credential->token ?? null;

        // Set headers
        if ($this->credential && $this->credential->auth_type === 'api_key' && $apiKey) {
            $this->headers['X-Api-Key'] = $apiKey;
        } elseif ($token) {
            $this->headers['Authorization'] = 'Bearer ' . $token;
        } elseif ($apiKey) {
            // Fallback in case no auth_type set but key exists
            $this->headers['X-Api-Key'] = $apiKey;
        }

        if (empty($this->headers['X-Api-Key']) && empty($this->headers['Authorization'])) {
            Log::warning('TaxlyService: No API key or token found. Falling back to .env key.');
        }

        // Always accept JSON
        $this->headers['Accept'] = 'application/json';
    }

    protected function client()
    {
        return Http::withHeaders($this->headers)->baseUrl($this->baseUrl);
    }

    protected function handleRequestException(RequestException $e)
    {
        $response = $e->response;
        $body = $response ? $response->body() : $e->getMessage();

        // Parse the error response to extract message and details
        $errorData = json_decode($body, true);
        $message = $errorData['message'] ?? $e->getMessage();
        $details = null;

        if (isset($errorData['data']['error']['details'])) {
            $details = $errorData['data']['error']['details'];
        } elseif (isset($errorData['data']['message'])) {
            $details = $errorData['data']['message'];
        } elseif (isset($errorData['error']['details'])) {
            $details = $errorData['error']['details'];
        }

        Log::error('Taxly API RequestException', [
            'message' => $message,
            'details' => $details,
            'body' => $body
        ]);

        // Throw a new custom exception with parsed details
        throw new TaxlyApiException($message, $e->getCode(), $e, $details);
    }

    /** Taxpayer login */
    public function taxPayerLogin(array $payload): array
    {
        try {
            $res = $this->client()->post('/auth/tax-payer-login', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly validateIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Validate IRN */
    public function validateIrn(array $payload): array
    {
        try {
            Log::info('Validating IRN with Taxly', ['payload' => $payload, 'url' => $this->baseUrl . '/invoices/irn/validate']);
            $res = $this->client()->post('/invoices/irn/validate', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly validateIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Validate invoice (structure) */
    public function validateInvoice(array $payload): array
    {
        try {
            $res = $this->client()->post('/invoices/validate', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly validateInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Submit invoice (validate + sign + submit) */
    public function submitInvoice(array $payload): array
    {
        try {
            $res = $this->client()->post('/invoices/submit', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly submitInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function transmitByIrn(string $irn, ?string $webhookUrl = null): array
    {
        try {
            $payload = [];
            if ($webhookUrl) {
                $payload['webhook_url'] = $webhookUrl;
            }

            $res = $this->client()->post("/invoices/{$irn}/transmit", $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly transmitByIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getTransmittedInvoiceByIrn(string $irn): array
    {
        try {
            $res = $this->client()->get("/invoices/transmit/{$irn}/lookup");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getTransmittedInvoiceByIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function confirmTransmittingInvoice(string $irn, array $payload): array
    {
        try {
            $res = $this->client()->patch("/invoices/transmit/{$irn}/confirmation", $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly confirmTransmittingInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateInvoicePayment(string $irn, string $paymentStatus): array
    {
        try {
            $res = $this->client()->patch("/invoices/{$irn}/update", [
                'payment_status' => $paymentStatus,
            ]);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly updateInvoicePayment error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function confirmByIrn(string $irn): array
    {
        try {
            $res = $this->client()->get("/invoices/{$irn}/confirm");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly confirmByIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function downloadByIrn(string $irn): array
    {
        try {
            $res = $this->client()->get("/invoices/{$irn}/download");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly downloadByIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Helper: set API key dynamically (if needed) */
    public function setApiKey(string $key): void
    {
        $this->headers['X-Api-Key'] = $key;
    }

    /** Helper: set Bearer token dynamically */
    public function setToken(string $token): void
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;
    }

    /** Get Invoice Types */
    public function getInvoiceTypes(): array
    {
        try {
            $res = $this->client()->get('/resources/invoice-types');
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getInvoiceTypes error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Get Payment Means */
    public function getPaymentMeans(): array
    {
        try {
            $res = $this->client()->get('/resources/payment_means');
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getPaymentMeans error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Get Tax Categories */
    public function getTaxCategories(): array
    {
        try {
            $res = $this->client()->get('/resources/tax-categories');
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getTaxCategories error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Lookup TIN */
    public function getTin(string $tinNumber): array
    {
        try {
            $res = $this->client()->get("/resources/tin/{$tinNumber}");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getTin error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Get Entity by ID */
    public function getEntity(string $entityId): array
    {
        try {
            $res = $this->client()->get("/resources/entity/{$entityId}");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getEntity error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** ============================================
     *  EXCHANGE / INVOICE RECEIVING APIs
     *  ============================================ */

    /**
     * Search exchange invoices
     * GET /api/v1/invoices/search/{business_id}
     *
     * @param string $businessId The business ID (TIN)
     * @param array $filters Optional filters: tin, status, date_from, date_to
     * @return array
     */
    public function searchExchangeInvoices(string $businessId, array $filters = []): array
    {
        try {
            $queryParams = [];

            if (isset($filters['tin'])) {
                $queryParams['tin'] = $filters['tin'];
            }
            if (isset($filters['status'])) {
                $queryParams['status'] = $filters['status'];
            }
            if (isset($filters['date_from'])) {
                $queryParams['date_from'] = $filters['date_from'];
            }
            if (isset($filters['date_to'])) {
                $queryParams['date_to'] = $filters['date_to'];
            }

            $res = $this->client()->get("/invoices/search/{$businessId}", $queryParams);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly searchExchangeInvoices error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Download invoice PDF by IRN
     * GET /api/v1/invoices/{irn}/download
     *
     * @param string $irn Invoice Reference Number
     * @return array
     */
    public function downloadExchangeInvoice(string $irn): array
    {
        try {
            $res = $this->client()->get("/invoices/{$irn}/download");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly downloadExchangeInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Confirm/Receive exchange invoice
     * GET /api/v1/invoices/{irn}/confirm
     *
     * @param string $irn Invoice Reference Number
     * @return array
     */
    public function confirmExchangeInvoice(string $irn): array
    {
        try {
            $res = $this->client()->get("/invoices/{$irn}/confirm");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly confirmExchangeInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** ============================================
     *  WEBHOOK MANAGEMENT APIs
     *  ============================================ */

    /**
     * List all webhooks
     * GET /api/v1/webhooks
     *
     * @return array
     */
    public function listWebhooks(): array
    {
        try {
            $res = $this->client()->get('/webhooks');
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly listWebhooks error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a webhook
     * POST /api/v1/webhooks
     *
     * @param array $payload Webhook configuration
     * @return array
     */
    public function createWebhook(array $payload): array
    {
        try {
            $res = $this->client()->post('/webhooks', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly createWebhook error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update a webhook
     * PATCH /api/v1/webhooks/{id}
     *
     * @param string $id Webhook ID
     * @param array $payload Updated webhook configuration
     * @return array
     */
    public function updateWebhook(string $id, array $payload): array
    {
        try {
            $res = $this->client()->patch("/webhooks/{$id}", $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly updateWebhook error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a webhook
     * DELETE /api/v1/webhooks/{id}
     *
     * @param string $id Webhook ID
     * @return array
     */
    public function deleteWebhook(string $id): array
    {
        try {
            $res = $this->client()->delete("/webhooks/{$id}");
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly deleteWebhook error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** ============================================
     *  INTEGRATOR MANAGEMENT APIs
     *  ============================================ */

    /**
     * Register as an integrator
     * POST /api/v1/integrators/register
     *
     * @param array $payload Integrator registration data
     * @return array
     */
    public function registerIntegrator(array $payload): array
    {
        try {
            $res = $this->client()->post('/integrators/register', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly registerIntegrator error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get integrator status
     * GET /api/v1/integrators/status
     *
     * @param string $tenantId The tenant ID
     * @return array
     */
    public function getIntegratorStatus(string $tenantId): array
    {
        try {
            $res = $this->client()->get('/integrators/status', [
                'tenant_id' => $tenantId,
            ]);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Taxly getIntegratorStatus error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate API key for integrator
     * POST /api/v1/integrators/{tenant}/api-keys
     * 
     * Note: First key issuance requires X-Integrator-Email header AND contact_email in body (no API key auth)
     *
     * @param string $tenant Tenant ID
     * @param array $payload API key configuration
     * @param string|null $integratorEmail The contact email used during registration (for first key generation)
     * @param int $maxRetries Number of retries for synchronization issues
     * @return array
     */
    public function generateApiKey(string $tenant, array $payload, ?string $integratorEmail = null, int $maxRetries = 3): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                if ($integratorEmail) {
                    // First time API key generation - use BOTH header AND body parameter
                    // NO X-Api-Key header should be sent for first-time generation
                    $payload['contact_email'] = $integratorEmail;

                    $requestHeaders = [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Integrator-Email' => $integratorEmail,
                    ];

                    Log::info('First-time API key generation attempt', [
                        'attempt' => $attempt,
                        'tenant' => $tenant,
                        'contact_email' => $integratorEmail,
                        'payload' => $payload,
                        'headers' => $requestHeaders,
                        'url' => $this->baseUrl . "/integrators/{$tenant}/api-keys",
                        'note' => 'Using X-Integrator-Email header for first-time authentication'
                    ]);

                    // Use a fresh HTTP client with X-Integrator-Email header
                    $res = Http::withHeaders($requestHeaders)
                        ->baseUrl($this->baseUrl)
                        ->post("/integrators/{$tenant}/api-keys", $payload);

                    Log::info('API key generation response received', [
                        'attempt' => $attempt,
                        'status' => $res->status(),
                        'body' => $res->body(),
                    ]);
                } else {
                    // Subsequent calls - use existing API key
                    Log::info('Subsequent API key generation using existing API key', [
                        'attempt' => $attempt,
                        'tenant' => $tenant,
                        'url' => $this->baseUrl . "/integrators/{$tenant}/api-keys"
                    ]);
                    $res = $this->client()->post("/integrators/{$tenant}/api-keys", $payload);
                }

                $res->throw();
                return $res->json();
            } catch (RequestException $e) {
                $response = $e->response;
                $statusCode = $response ? $response->status() : 'N/A';
                $body = $response ? $response->body() : 'No response body';

                $errorData = json_decode($body, true);
                $errorMessage = $errorData['message'] ?? '';

                // Check if it's a synchronization issue (403 with "not approved" message)
                if ($statusCode === 403 && str_contains($errorMessage, 'approved integrator')) {
                    $lastException = $e;

                    if ($attempt < $maxRetries) {
                        $waitTime = $attempt * 2; // 2, 4, 6 seconds
                        Log::warning('Taxly API key generation hit synchronization issue, retrying...', [
                            'attempt' => $attempt,
                            'max_retries' => $maxRetries,
                            'wait_time_seconds' => $waitTime,
                            'tenant' => $tenant,
                            'error' => $errorMessage,
                        ]);

                        sleep($waitTime);
                        continue; // Retry
                    }
                }

                // For other errors, fail immediately
                Log::error('Taxly API key generation failed - RequestException', [
                    'status_code' => $statusCode,
                    'body' => $body,
                    'tenant' => $tenant,
                    'integrator_email' => $integratorEmail,
                    'attempt' => $attempt,
                    'message' => 'The request reached Taxly API but was rejected.'
                ]);

                $this->handleRequestException($e);
                throw $e;
            } catch (Throwable $e) {
                Log::error('Taxly generateApiKey error - Unexpected error', [
                    'error' => $e->getMessage(),
                    'tenant' => $tenant,
                    'integrator_email' => $integratorEmail,
                    'attempt' => $attempt,
                ]);
                throw $e;
            }
        }

        // If we exhausted all retries, throw the last exception
        if ($lastException) {
            $this->handleRequestException($lastException);
            throw $lastException;
        }

        // This should not happen, but just in case
        throw new \RuntimeException('Failed to generate API key after ' . $maxRetries . ' attempts');
    }
}
