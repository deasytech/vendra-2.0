<?php

namespace App\Services;

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
        $this->baseUrl = $this->credential->base_url ?? config('services.taxly.base_url', 'https://dev.taxly.ng/api/v1');

        if ($this->credential && $this->credential->auth_type === 'api_key' && $this->credential->api_key) {
            $this->headers['X-Api-Key'] = $this->credential->api_key;
        } elseif ($this->credential && $this->credential->token) {
            $this->headers['Authorization'] = 'Bearer ' . $this->credential->token;
        }

        // default accept json
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
        Log::error('Taxly API RequestException', ['message' => $e->getMessage(), 'body' => $body]);
        throw $e;
    }

    /** Taxpayer login */
    public function taxPayerLogin(array $payload): array
    {
        try {
            $res = $this->client()->post('/auth/tax-payer-login', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly validateIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Validate IRN */
    public function validateIrn(array $payload): array
    {
        try {
            $res = $this->client()->post('/invoices/irn/validate', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly validateIrn error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Validate invoice (structure) */
    public function validateInvoice(array $payload): array
    {
        $url = $this->baseUrl . '/invoices/validate';
        Log::debug('Taxly validateInvoice call', ['url' => $url, 'payload' => $payload]);
        try {
            $res = $this->client()->post('/invoices/validate', $payload);
            $res->throw();
            return $res->json();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly confirmTransmittingInvoice error', ['error' => $e->getMessage()]);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
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
            $this->handleRequestException($e);
        } catch (Throwable $e) {
            Log::error('Taxly getEntity error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
