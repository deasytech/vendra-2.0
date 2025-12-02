<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  use HasFactory;

  protected $fillable = [
    'key',
    'value',
    'type',
    'description',
    'metadata',
  ];

  protected $casts = [
    'metadata' => 'array',
  ];

  /**
   * Get setting value cast to appropriate type
   */
  public function getTypedValue()
  {
    return match ($this->type) {
      'boolean' => (bool) $this->value,
      'integer' => (int) $this->value,
      'float' => (float) $this->value,
      'json' => json_decode($this->value, true),
      'array' => json_decode($this->value, true),
      default => $this->value,
    };
  }

  /**
   * Set setting value with automatic type detection
   */
  public function setTypedValue($value): void
  {
    if (is_bool($value)) {
      $this->type = 'boolean';
      $this->value = $value ? '1' : '0';
    } elseif (is_int($value)) {
      $this->type = 'integer';
      $this->value = (string) $value;
    } elseif (is_float($value)) {
      $this->type = 'float';
      $this->value = (string) $value;
    } elseif (is_array($value) || is_object($value)) {
      $this->type = 'json';
      $this->value = json_encode($value);
    } else {
      $this->type = 'string';
      $this->value = (string) $value;
    }
  }

  /**
   * Get a setting value by key
   */
  public static function getValue(string $key, $default = null)
  {
    $setting = self::where('key', $key)->first();

    if (!$setting) {
      return $default;
    }

    return $setting->getTypedValue();
  }

  /**
   * Set a setting value by key
   */
  public static function setValue(string $key, $value, string $description = null): self
  {
    $setting = self::firstOrNew(['key' => $key]);
    $setting->setTypedValue($value);

    if ($description !== null) {
      $setting->description = $description;
    }

    $setting->save();

    return $setting;
  }

  /**
   * Check if a setting exists
   */
  public static function has(string $key): bool
  {
    return self::where('key', $key)->exists();
  }

  /**
   * Delete a setting by key
   */
  public static function deleteValue(string $key): bool
  {
    return self::where('key', $key)->delete() > 0;
  }

  /**
   * Get all settings as key-value array
   */
  public static function getAllSettings(): array
  {
    $settings = self::all();
    $result = [];

    foreach ($settings as $setting) {
      $result[$setting->key] = $setting->getTypedValue();
    }

    return $result;
  }

  /**
   * Get project settings
   */
  public static function getProjectSettings(): array
  {
    return [
      'project_name' => self::getValue('project_name', 'Vendra Invoice System'),
      'project_logo' => self::getValue('project_logo', null),
      'meta_title' => self::getValue('meta_title', 'Vendra Invoice System'),
      'meta_description' => self::getValue('meta_description', 'Professional invoice management system with FIRS integration'),
      'meta_keywords' => self::getValue('meta_keywords', 'invoice, tax, firs, nigeria'),
      'withholding_tax_rate' => self::getValue('withholding_tax_rate', 5.0),
      'withholding_tax_enabled' => self::getValue('withholding_tax_enabled', true),
    ];
  }

  /**
   * Update multiple settings at once
   */
  public static function updateSettings(array $settings): void
  {
    foreach ($settings as $key => $value) {
      if (is_array($value) && isset($value['value'])) {
        self::setValue($key, $value['value'], $value['description'] ?? null);
      } else {
        self::setValue($key, $value);
      }
    }
  }
}
