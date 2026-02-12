<?php

namespace App\Livewire\Settings;

use App\Models\Organization;
use App\Models\Setting;
use App\Models\TaxlyCredential;
use App\Services\TaxlyService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaxlyIntegration extends Component
{
  public $organization;
  public $credential;

  // Integrator registration form
  public $integratorName = '';
  public $integratorBrand = '';
  public $integratorDomain = '';
  public $integratorDescription = '';
  public $integratorUseCase = '';
  public $integratorWebsite = '';
  public $integratorContactPerson = '';
  public $integratorContactEmail = '';

  // API Key form
  public $apiKeyName = '';
  public $apiKeyDescription = '';
  public $apiKeyPermissions = ['invoices.read', 'invoices.write'];

  // Status
  public $showRegistrationForm = false;
  public $showApiKeyForm = false;
  public $showManualApiKeyForm = false;
  public $registrationResult = null;
  public $apiKeyResult = null;
  public $errorMessage = null;

  // Manual API Key form
  public $manualApiKey = '';

  public function mount()
  {
    $this->organization = Auth::user()->organization;
    $this->loadCredential();

    // Pre-fill form with organization data
    if ($this->organization) {
      $this->integratorName = $this->organization->legal_name;
      $this->integratorBrand = $this->organization->slug ?? strtolower(str_replace(' ', '', $this->organization->legal_name));
      $this->integratorDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'vendra.app';
      $this->integratorDescription = !empty($this->organization->description) ? $this->organization->description : $this->getDefaultIntegratorDescription();
      $this->integratorUseCase = 'Enable suppliers to generate, sign, and receive FIRS-compliant e-invoices through the Vendra platform';
      $this->integratorWebsite = config('app.url');
      $this->integratorContactPerson = Auth::user()->name;
      $this->integratorContactEmail = Auth::user()->email;
    }
  }

  public function loadCredential()
  {
    // Load credential without tenant scope since Taxly tenant IDs are external references
    $this->credential = TaxlyCredential::withoutGlobalScopes()
      ->where('organization_id', $this->organization?->id)
      ->first();

    // If credential exists with stored email, update the form field
    if ($this->credential && $this->credential->integrator_contact_email) {
      $this->integratorContactEmail = $this->credential->integrator_contact_email;
    }
  }

  public function registerIntegrator()
  {
    $this->validate([
      'integratorName' => 'required|string|max:255',
      'integratorBrand' => 'required|string|max:255',
      'integratorDomain' => 'required|string|max:255',
      'integratorDescription' => 'required|string|min:50|max:1000',
      'integratorUseCase' => 'required|string|min:50|max:1000',
      'integratorWebsite' => 'nullable|url|max:255',
      'integratorContactPerson' => 'required|string|max:255',
      'integratorContactEmail' => 'required|email|max:255',
    ]);

    try {
      $taxlyService = new TaxlyService();

      $payload = [
        'name' => $this->integratorName,
        'brand' => $this->integratorBrand,
        'domain' => $this->integratorDomain,
        'description' => $this->integratorDescription,
        'use_case' => $this->integratorUseCase,
        'website' => $this->integratorWebsite,
        'contact_person' => $this->integratorContactPerson,
        'contact_email' => $this->integratorContactEmail,
        'landlord_id' => config('services.taxly.landlord_id'),
      ];

      Log::info('Registering integrator with Taxly', [
        'payload' => $payload,
        'base_url' => config('services.taxly.base_url', 'https://dev.taxly.ng/api/v1'),
        'api_key_from_env' => substr(env('TAXLY_API_KEY', ''), 0, 20) . '...',
      ]);

      $result = $taxlyService->registerIntegrator($payload);

      Log::info('Integrator registration result', ['result' => $result]);

      if (isset($result['data']['tenant'])) {
        $tenant = $result['data']['tenant'];

        // Store the integrator credentials
        TaxlyCredential::updateOrCreate(
          [
            'organization_id' => $this->organization->id,
          ],
          [
            'tenant_id' => $tenant['id'],
            'tenant_name' => $tenant['name'],
            'is_integrator' => $tenant['is_integrator'] ?? true,
            'integrator_status' => $tenant['integrator_status'] ?? 'pending',
            'integrator_contact_email' => $this->integratorContactEmail,
            'base_url' => config('services.taxly.base_url', 'https://dev.taxly.ng/api/v1'),
          ]
        );

        // Update organization with business_id if provided
        if (isset($tenant['business_id'])) {
          $this->organization->update(['business_id' => $tenant['business_id']]);
        }

        // Save the Taxly tenant_id to settings for use in API calls
        Setting::setValue(
          'taxly_tenant_id',
          $tenant['id'],
          'Taxly tenant ID used for API authentication'
        );

        // Reload credential to reflect changes
        $this->loadCredential();

        $this->registrationResult = $result['data'];
        $this->showRegistrationForm = false;
        $this->errorMessage = null;

        $statusMessage = $tenant['integrator_status'] ?? 'pending';
        $message = "Integrator registered successfully! Current status: " . ucfirst($statusMessage);

        if ($statusMessage === 'pending') {
          $message .= ". Your application is under review. Please check back later or contact Taxly support for approval.";
        }

        session()->flash('success', $message);
      } else {
        $this->errorMessage = 'Invalid response from Taxly. Please try again.';
      }
    } catch (\Exception $e) {
      Log::error('Integrator registration failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $this->errorMessage = 'Registration failed: ' . $e->getMessage();
    }
  }

  public function generateApiKey()
  {
    $this->validate([
      'apiKeyName' => 'required|string|max:255',
      'apiKeyDescription' => 'required|string|max:500',
    ]);

    try {
      if (!$this->credential || !$this->credential->tenant_id) {
        $this->errorMessage = 'Please register as an integrator first.';
        return;
      }

      $taxlyService = new TaxlyService();

      $payload = [
        'name' => $this->apiKeyName,
        'description' => $this->apiKeyDescription,
        'permissions' => $this->apiKeyPermissions,
      ];

      Log::info('Generating API key for integrator', [
        'tenant_id' => $this->credential->tenant_id,
        'payload' => $payload
      ]);

      // Get the integrator email - use the email from registration or the current user's email
      $integratorEmail = $this->credential->tenant_name
        ? $this->getIntegratorEmailFromRegistration()
        : $this->integratorContactEmail;

      // Pass the integrator email for first-time API key generation
      // This uses X-Integrator-Email header instead of API key auth
      $result = $taxlyService->generateApiKey(
        $this->credential->tenant_id,
        $payload,
        $integratorEmail
      );

      Log::info('API key generation result', ['result' => $result]);

      if (isset($result['data']['api_key'])) {
        $apiKey = $result['data']['api_key'];

        // Update credentials with API key
        $this->credential->update([
          'api_key' => $apiKey['key'],
          'api_key_id' => $apiKey['id'] ?? null,
          'api_key_permissions' => $this->apiKeyPermissions,
          'auth_type' => 'api_key',
        ]);

        $this->apiKeyResult = $result['data'];
        $this->showApiKeyForm = false;
        $this->errorMessage = null;

        session()->flash('success', 'API key generated successfully! Please copy it now as it won\'t be shown again.');
      } else {
        $this->errorMessage = 'Invalid response from Taxly. Please try again.';
      }
    } catch (\Exception $e) {
      Log::error('API key generation failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      // Provide a user-friendly error message
      $errorMsg = $e->getMessage();
      if (str_contains($errorMsg, 'Unauthorized')) {
        $this->errorMessage = 'Automatic API key generation failed. The Taxly API rejected the authentication request. '
          . 'This is a known issue with Taxly\'s API. Please use the "Enter API Key Manually" button instead, '
          . 'or contact Taxly support to obtain an API key directly.';
      } elseif (str_contains($errorMsg, 'approved integrator')) {
        $this->errorMessage = 'API key generation failed: Taxly reports the integrator is not approved, '
          . 'even though the status check shows approved. This appears to be a synchronization issue on Taxly\'s side. '
          . 'Please wait a few minutes and try again, or contact Taxly support for assistance. '
          . 'You can also use the "Enter API Key Manually" button if you have an API key from Taxly support.';
      } else {
        $this->errorMessage = 'API key generation failed: ' . $errorMsg;
      }
    }
  }

  public function checkIntegratorStatus()
  {
    try {
      if (!$this->credential || !$this->credential->tenant_id) {
        $this->errorMessage = 'No integrator found. Please register first.';
        return;
      }

      // Fetch latest status from Taxly API
      $taxlyService = new TaxlyService();
      $result = $taxlyService->getIntegratorStatus($this->credential->tenant_id);

      Log::info('Integrator status check result', ['result' => $result]);

      // Update local credential with latest status from Taxly
      // The API returns data directly, not nested under 'integrator' key
      $integratorData = $result['data'];

      if (isset($integratorData['integrator_status'])) {
        $this->credential->update([
          'integrator_status' => $integratorData['integrator_status'],
          'is_integrator' => $integratorData['is_integrator'] ?? true,
          'tenant_name' => $integratorData['name'] ?? $this->credential->tenant_name,
        ]);

        // Update organization business_id if provided
        if (isset($integratorData['business_id']) && $this->organization) {
          $this->organization->update(['business_id' => $integratorData['business_id']]);
        }
      }

      // Reload credential to reflect changes
      $this->loadCredential();
      $this->errorMessage = null;

      // Use the status_message from API if available
      $status = $this->credential->integrator_status ?? 'unknown';
      $apiStatusMessage = $integratorData['status_message'] ?? null;
      $canGenerateApiKeys = $integratorData['can_generate_api_keys'] ?? false;

      if ($apiStatusMessage) {
        $message = 'Status refreshed: ' . $apiStatusMessage;
      } else {
        $message = 'Status refreshed. Current status: ' . ucfirst($status);

        if ($status === 'pending') {
          $message .= '. Your application is still under review by Taxly.';
        } elseif ($status === 'approved') {
          if ($canGenerateApiKeys) {
            $message .= '. You can now generate API keys and receive exchange invoices.';
          } else {
            $message .= '. Your application is approved but API key generation is not yet enabled.';
          }
        } elseif ($status === 'rejected') {
          $message .= '. Your application was rejected. Please contact Taxly support for more information.';
        }
      }

      session()->flash('success', $message);
    } catch (\Exception $e) {
      Log::error('Failed to refresh integrator status', [
        'error' => $e->getMessage(),
        'credential_id' => $this->credential?->id
      ]);

      // Even if API call fails, show current local status
      $this->loadCredential();
      $status = $this->credential->integrator_status ?? 'unknown';
      $message = 'Unable to fetch latest status from Taxly. Current local status: ' . ucfirst($status);

      session()->flash('warning', $message);
    }
  }

  public function toggleRegistrationForm()
  {
    $this->showRegistrationForm = !$this->showRegistrationForm;
    $this->showApiKeyForm = false;
    $this->errorMessage = null;
  }

  public function toggleApiKeyForm()
  {
    $this->showApiKeyForm = !$this->showApiKeyForm;
    $this->showManualApiKeyForm = false;
    $this->showRegistrationForm = false;
    $this->errorMessage = null;

    // Pre-fill API key form with default values when opening
    if ($this->showApiKeyForm) {
      $organizationName = $this->organization?->legal_name ?? 'Vendra';
      $this->apiKeyName = $organizationName . ' API Key';
      $this->apiKeyDescription = 'API key for ' . $organizationName . ' to receive and manage exchange invoices from Taxly';
    }
  }

  public function toggleManualApiKeyForm()
  {
    $this->showManualApiKeyForm = !$this->showManualApiKeyForm;
    $this->showApiKeyForm = false;
    $this->showRegistrationForm = false;
    $this->errorMessage = null;
    $this->manualApiKey = '';
  }

  public function saveManualApiKey()
  {
    $this->validate([
      'manualApiKey' => 'required|string|min:20|max:255',
    ]);

    try {
      if (!$this->credential || !$this->credential->tenant_id) {
        $this->errorMessage = 'Please register as an integrator first.';
        return;
      }

      // Update credentials with manually entered API key
      $this->credential->update([
        'api_key' => $this->manualApiKey,
        'api_key_permissions' => ['invoices.read', 'invoices.write'],
        'auth_type' => 'api_key',
      ]);

      $this->showManualApiKeyForm = false;
      $this->manualApiKey = '';
      $this->errorMessage = null;

      session()->flash('success', 'API key saved successfully! You can now receive exchange invoices.');
    } catch (\Exception $e) {
      Log::error('Manual API key save failed', [
        'error' => $e->getMessage(),
        'credential_id' => $this->credential?->id,
      ]);

      $this->errorMessage = 'Failed to save API key: ' . $e->getMessage();
    }
  }

  public function clearApiKey()
  {
    try {
      if ($this->credential) {
        $this->credential->update([
          'api_key' => null,
          'api_key_id' => null,
        ]);

        session()->flash('success', 'API key cleared successfully.');
      }
    } catch (\Exception $e) {
      $this->errorMessage = 'Failed to clear API key: ' . $e->getMessage();
    }
  }

  /**
   * Get the integrator email used during registration
   * This is needed for first-time API key generation
   */
  private function getIntegratorEmailFromRegistration(): string
  {
    // Use the stored integrator contact email from registration
    // This is required for first-time API key generation with X-Integrator-Email header
    if ($this->credential && $this->credential->integrator_contact_email) {
      return $this->credential->integrator_contact_email;
    }

    // Fallback to current user's email if not stored
    return Auth::user()->email;
  }

  /**
   * Get default integrator description when organization has no description
   */
  private function getDefaultIntegratorDescription(): string
  {
    $orgName = $this->organization?->legal_name ?? 'Our company';

    return "{$orgName} is a leading provider of products and services in Nigeria. "
      . "We manage a network of suppliers and require a streamlined invoice management system "
      . "to ensure compliance with FIRS e-invoicing regulations. "
      . "Through Vendra, we aim to simplify invoice processing and maintain transparent financial records.";
  }

  public function render()
  {
    return view('livewire.settings.taxly-integration');
  }
}
