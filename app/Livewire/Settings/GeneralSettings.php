<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class GeneralSettings extends Component
{
  use WithFileUploads;

  // Project Settings
  public $project_name;
  public $project_logo;
  public $existing_logo;
  public $meta_title;
  public $meta_description;
  public $meta_keywords;

  // Withholding Tax Settings
  public $withholding_tax_rate;
  public $withholding_tax_enabled;

  // Company Settings
  public $company_name;
  public $company_tin;
  public $company_email;
  public $company_phone;
  public $company_address;

  // Invoice Settings
  public $default_invoice_type;
  public $default_currency;
  public $default_vat_rate;

  // UI Settings
  public $theme_color;
  public $enable_dark_mode;
  public $items_per_page;

  // Application Settings
  public $timezone;
  public $date_format;
  public $currency_symbol;
  public $decimal_places;

  public $message;
  public $success = false;

  public function mount()
  {
    $this->loadSettings();
  }

  public function loadSettings()
  {
    // Project Settings
    $this->project_name = Setting::getValue('project_name', 'Vendra Invoice System');
    $this->existing_logo = Setting::getValue('project_logo');
    $this->meta_title = Setting::getValue('meta_title', 'Vendra Invoice System');
    $this->meta_description = Setting::getValue('meta_description', 'Professional invoice management system');
    $this->meta_keywords = Setting::getValue('meta_keywords', 'invoice, tax, firs, nigeria');

    // Withholding Tax Settings
    $this->withholding_tax_rate = Setting::getValue('withholding_tax_rate', 5.0);
    $this->withholding_tax_enabled = Setting::getValue('withholding_tax_enabled', true);

    // Company Settings
    $this->company_name = Setting::getValue('company_name', 'Your Company Name');
    $this->company_tin = Setting::getValue('company_tin', '');
    $this->company_email = Setting::getValue('company_email', '');
    $this->company_phone = Setting::getValue('company_phone', '');
    $this->company_address = Setting::getValue('company_address', [
      'street' => '',
      'city' => '',
      'state' => '',
      'country' => 'NG',
      'postal_code' => ''
    ]);

    // Invoice Settings
    $this->default_invoice_type = Setting::getValue('default_invoice_type', '396');
    $this->default_currency = Setting::getValue('default_currency', 'NGN');
    $this->default_vat_rate = Setting::getValue('default_vat_rate', 7.5);

    // UI Settings
    $this->theme_color = Setting::getValue('theme_color', '#3B82F6');
    $this->enable_dark_mode = Setting::getValue('enable_dark_mode', false);
    $this->items_per_page = Setting::getValue('items_per_page', 10);

    // Application Settings
    $this->timezone = Setting::getValue('timezone', 'Africa/Lagos');
    $this->date_format = Setting::getValue('date_format', 'Y-m-d');
    $this->currency_symbol = Setting::getValue('currency_symbol', '₦');
    $this->decimal_places = Setting::getValue('decimal_places', 2);
  }

  public function saveSettings()
  {
    $this->validate([
      'project_name' => 'required|string|max:255',
      'meta_title' => 'required|string|max:255',
      'meta_description' => 'required|string|max:500',
      'meta_keywords' => 'nullable|string|max:500',
      'withholding_tax_rate' => 'required|numeric|min:0|max:100',
      'withholding_tax_enabled' => 'boolean',
      'company_name' => 'required|string|max:255',
      'company_tin' => 'nullable|string|max:50',
      'company_email' => 'nullable|email|max:255',
      'company_phone' => 'nullable|string|max:50',
      'default_invoice_type' => 'required|string|max:10',
      'default_currency' => 'required|string|size:3',
      'default_vat_rate' => 'required|numeric|min:0|max:100',
      'theme_color' => 'required|string|max:7',
      'enable_dark_mode' => 'boolean',
      'items_per_page' => 'required|integer|min:5|max:100',
      'timezone' => 'required|string|max:50',
      'date_format' => 'required|string|max:20',
      'currency_symbol' => 'required|string|max:10',
      'decimal_places' => 'required|integer|min:0|max:4',
    ]);

    try {
      // Handle logo upload
      if ($this->project_logo) {
        // Delete old logo if exists
        if ($this->existing_logo) {
          Storage::disk('public')->delete($this->existing_logo);
        }

        // Store new logo
        $logoPath = $this->project_logo->store('logos', 'public');
        Setting::setValue('project_logo', $logoPath, 'Project logo');
      }

      // Project Settings
      Setting::setValue('project_name', $this->project_name, 'Project name');
      Setting::setValue('meta_title', $this->meta_title, 'Meta title');
      Setting::setValue('meta_description', $this->meta_description, 'Meta description');
      Setting::setValue('meta_keywords', $this->meta_keywords, 'Meta keywords');

      // Withholding Tax Settings
      Setting::setValue('withholding_tax_rate', $this->withholding_tax_rate, 'Withholding tax rate (%)');
      Setting::setValue('withholding_tax_enabled', $this->withholding_tax_enabled, 'Enable withholding tax');

      // Company Settings
      Setting::setValue('company_name', $this->company_name, 'Company name');
      Setting::setValue('company_tin', $this->company_tin, 'Company TIN');
      Setting::setValue('company_email', $this->company_email, 'Company email');
      Setting::setValue('company_phone', $this->company_phone, 'Company phone');
      Setting::setValue('company_address', $this->company_address, 'Company address');

      // Invoice Settings
      Setting::setValue('default_invoice_type', $this->default_invoice_type, 'Default invoice type');
      Setting::setValue('default_currency', $this->default_currency, 'Default currency');
      Setting::setValue('default_vat_rate', $this->default_vat_rate, 'Default VAT rate (%)');

      // UI Settings
      Setting::setValue('theme_color', $this->theme_color, 'Theme color');
      Setting::setValue('enable_dark_mode', $this->enable_dark_mode, 'Enable dark mode');
      Setting::setValue('items_per_page', $this->items_per_page, 'Items per page');

      // Application Settings
      Setting::setValue('timezone', $this->timezone, 'Timezone');
      Setting::setValue('date_format', $this->date_format, 'Date format');
      Setting::setValue('currency_symbol', $this->currency_symbol, 'Currency symbol');
      Setting::setValue('decimal_places', $this->decimal_places, 'Decimal places');

      $this->success = true;
      $this->message = 'Settings saved successfully!';

      // Reload settings to reflect changes
      $this->loadSettings();
    } catch (\Exception $e) {
      $this->success = false;
      $this->message = 'Error saving settings: ' . $e->getMessage();
    }
  }

  public function removeLogo()
  {
    if ($this->existing_logo) {
      Storage::disk('public')->delete($this->existing_logo);
      Setting::setValue('project_logo', null, 'Project logo');
      $this->existing_logo = null;
      $this->project_logo = null;
      $this->success = true;
      $this->message = 'Logo removed successfully!';
    }
  }

  public function render()
  {
    return view('livewire.settings.general-settings');
  }
}
