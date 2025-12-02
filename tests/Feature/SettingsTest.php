<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingsTest extends TestCase
{
  use RefreshDatabase;

  public function test_settings_table_exists()
  {
    $this->assertTrue(Schema::hasTable('settings'));
  }

  public function test_can_create_setting()
  {
    $setting = Setting::create([
      'key' => 'test_key',
      'value' => 'test_value',
      'type' => 'string',
      'description' => 'Test setting'
    ]);

    $this->assertDatabaseHas('settings', [
      'key' => 'test_key',
      'value' => 'test_value'
    ]);
  }

  public function test_setting_get_value_method()
  {
    Setting::create([
      'key' => 'test_key',
      'value' => 'test_value',
      'type' => 'string'
    ]);

    $value = Setting::getValue('test_key');
    $this->assertEquals('test_value', $value);
  }

  public function test_setting_get_value_with_default()
  {
    $value = Setting::getValue('non_existent_key', 'default_value');
    $this->assertEquals('default_value', $value);
  }

  public function test_setting_set_value_method()
  {
    Setting::setValue('test_key', 'test_value');

    $this->assertDatabaseHas('settings', [
      'key' => 'test_key',
      'value' => 'test_value'
    ]);
  }

  public function test_setting_typed_values()
  {
    // Test boolean
    Setting::setValue('bool_key', true);
    $this->assertTrue(Setting::getValue('bool_key'));

    // Test integer
    Setting::setValue('int_key', 42);
    $this->assertEquals(42, Setting::getValue('int_key'));

    // Test float
    Setting::setValue('float_key', 3.14);
    $this->assertEquals(3.14, Setting::getValue('float_key'));

    // Test array
    $array = ['key' => 'value'];
    Setting::setValue('array_key', $array);
    $this->assertEquals($array, Setting::getValue('array_key'));
  }

  public function test_project_settings()
  {
    $projectSettings = Setting::getProjectSettings();

    $this->assertArrayHasKey('project_name', $projectSettings);
    $this->assertArrayHasKey('project_logo', $projectSettings);
    $this->assertArrayHasKey('meta_title', $projectSettings);
    $this->assertArrayHasKey('meta_description', $projectSettings);
    $this->assertArrayHasKey('meta_keywords', $projectSettings);
    $this->assertArrayHasKey('withholding_tax_rate', $projectSettings);
    $this->assertArrayHasKey('withholding_tax_enabled', $projectSettings);
  }

  public function test_withholding_tax_settings_loaded_in_invoice_edit()
  {
    // Set custom withholding tax settings
    Setting::setValue('withholding_tax_rate', 10.0);
    Setting::setValue('withholding_tax_enabled', false);

    // Test that settings are loaded correctly
    $withholdingTaxRate = Setting::getValue('withholding_tax_rate');
    $withholdingTaxEnabled = Setting::getValue('withholding_tax_enabled');

    // Check that settings were loaded correctly
    $this->assertEquals(10.0, $withholdingTaxRate);
    $this->assertFalse($withholdingTaxEnabled);
  }

  public function test_settings_seeder_populates_defaults()
  {
    $this->seed(\Database\Seeders\SettingsSeeder::class);

    // Test some key settings were created
    $this->assertNotNull(Setting::getValue('project_name'));
    $this->assertNotNull(Setting::getValue('withholding_tax_rate'));
    $this->assertNotNull(Setting::getValue('meta_title'));
    $this->assertNotNull(Setting::getValue('meta_description'));
  }
}
