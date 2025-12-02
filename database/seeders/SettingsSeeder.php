<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Project Settings
    Setting::setValue('project_name', 'Vendra Invoice System', 'The name of the project/application');
    Setting::setValue('project_logo', null, 'URL or path to the project logo');
    Setting::setValue('meta_title', 'Vendra Invoice System', 'Default meta title for the application');
    Setting::setValue('meta_description', 'Professional invoice management system with FIRS integration', 'Default meta description for the application');
    Setting::setValue('meta_keywords', 'invoice, tax, firs, nigeria, billing, accounting', 'Default meta keywords for the application');

    // Withholding Tax Settings
    Setting::setValue('withholding_tax_rate', 5.0, 'Default withholding tax rate (percentage)');
    Setting::setValue('withholding_tax_enabled', true, 'Whether withholding tax is enabled by default');

    // Invoice Settings
    Setting::setValue('default_invoice_type', '396', 'Default invoice type code');
    Setting::setValue('default_currency', 'NGN', 'Default currency code');
    Setting::setValue('default_vat_rate', 7.5, 'Default VAT rate (percentage)');

    // Company Settings
    Setting::setValue('company_name', 'Your Company Name', 'Default company name');
    Setting::setValue('company_tin', '', 'Company Tax Identification Number');
    Setting::setValue('company_email', '', 'Company email address');
    Setting::setValue('company_phone', '', 'Company phone number');
    Setting::setValue('company_address', json_encode([
      'street' => '',
      'city' => '',
      'state' => '',
      'country' => 'NG',
      'postal_code' => ''
    ]), 'Company address information');

    // Tax Settings
    Setting::setValue('tax_authority', 'FIRS', 'Tax authority name');
    Setting::setValue('tax_registration_number', '', 'Tax registration number');

    // Application Settings
    Setting::setValue('timezone', 'Africa/Lagos', 'Application timezone');
    Setting::setValue('date_format', 'Y-m-d', 'Date format');
    Setting::setValue('currency_symbol', '₦', 'Currency symbol');
    Setting::setValue('decimal_places', 2, 'Number of decimal places for currency');

    // Email Settings
    Setting::setValue('email_from_address', 'noreply@example.com', 'Default from email address');
    Setting::setValue('email_from_name', 'Vendra Invoice System', 'Default from name');

    // Notification Settings
    Setting::setValue('enable_email_notifications', true, 'Enable email notifications');
    Setting::setValue('enable_sms_notifications', false, 'Enable SMS notifications');

    // Security Settings
    Setting::setValue('session_timeout', 120, 'Session timeout in minutes');
    Setting::setValue('password_expiry_days', 90, 'Password expiry in days');
    Setting::setValue('enable_two_factor', false, 'Enable two-factor authentication');

    // Integration Settings
    Setting::setValue('taxly_api_enabled', true, 'Enable Taxly API integration');
    Setting::setValue('taxly_api_url', 'https://api.taxly.com', 'Taxly API base URL');
    Setting::setValue('enable_webhooks', true, 'Enable webhook notifications');

    // UI Settings
    Setting::setValue('theme_color', '#3B82F6', 'Primary theme color');
    Setting::setValue('enable_dark_mode', false, 'Enable dark mode by default');
    Setting::setValue('items_per_page', 10, 'Number of items per page in lists');

    // Backup Settings
    Setting::setValue('enable_auto_backup', true, 'Enable automatic backups');
    Setting::setValue('backup_frequency', 'daily', 'Backup frequency (daily, weekly, monthly)');
    Setting::setValue('backup_retention_days', 30, 'Number of days to retain backups');
  }
}
