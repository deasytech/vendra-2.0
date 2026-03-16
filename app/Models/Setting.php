<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
  use HasFactory;

  protected $fillable = [
    'key',
    'value',
    'type',
    'description',
    'metadata',
    'tenant_id',
    'organization_id',
    'user_id',
  ];

  protected $casts = [
    'metadata' => 'array',
    'tenant_id' => 'integer',
    'organization_id' => 'integer',
    'user_id' => 'integer',
  ];

  public function tenant()
  {
    return $this->belongsTo(Tenant::class);
  }

  public function organization()
  {
    return $this->belongsTo(Organization::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

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
    if ($value === null) {
      $this->type = 'string';
      $this->value = null;
    } elseif (is_bool($value)) {
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
  public static function getValue(string $key, $default = null, ?array $scope = null)
  {
    $scope = self::resolveScope($scope);

    $setting = self::query()
      ->where('key', $key)
      ->where(function ($query) use ($scope) {
        if ($scope['user_id']) {
          $query->orWhere(function ($subQuery) use ($scope) {
            $subQuery
              ->where('user_id', $scope['user_id'])
              ->where('organization_id', $scope['organization_id'])
              ->where('tenant_id', $scope['tenant_id']);
          });
        }

        if ($scope['organization_id']) {
          $query->orWhere(function ($subQuery) use ($scope) {
            $subQuery
              ->whereNull('user_id')
              ->where('organization_id', $scope['organization_id'])
              ->where('tenant_id', $scope['tenant_id']);
          });
        }

        if ($scope['tenant_id']) {
          $query->orWhere(function ($subQuery) use ($scope) {
            $subQuery
              ->whereNull('user_id')
              ->whereNull('organization_id')
              ->where('tenant_id', $scope['tenant_id']);
          });
        }

        $query->orWhere(function ($subQuery) {
          $subQuery
            ->whereNull('user_id')
            ->whereNull('organization_id')
            ->whereNull('tenant_id');
        });
      })
      ->orderByRaw('CASE
          WHEN user_id IS NOT NULL THEN 1
          WHEN organization_id IS NOT NULL THEN 2
          WHEN tenant_id IS NOT NULL THEN 3
          ELSE 4
      END')
      ->first();

    if (!$setting) {
      return $default;
    }

    return $setting->getTypedValue();
  }

  /**
   * Set a setting value by key
   */
  public static function setValue(string $key, $value, string $description = null, ?array $scope = null): self
  {
    $scope = self::resolveScope($scope);

    $setting = self::firstOrNew([
      'key' => $key,
      'tenant_id' => $scope['tenant_id'],
      'organization_id' => $scope['organization_id'],
      'user_id' => $scope['user_id'],
    ]);

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
  public static function has(string $key, ?array $scope = null): bool
  {
    return self::getSettingRecord($key, $scope) !== null;
  }

  /**
   * Delete a setting by key
   */
  public static function deleteValue(string $key, ?array $scope = null): bool
  {
    $scope = self::resolveScope($scope);

    return self::query()
      ->where('key', $key)
      ->where('tenant_id', $scope['tenant_id'])
      ->where('organization_id', $scope['organization_id'])
      ->where('user_id', $scope['user_id'])
      ->delete() > 0;
  }

  /**
   * Get all settings as key-value array
   */
  public static function getAllSettings(?array $scope = null): array
  {
    $keys = self::query()->distinct()->pluck('key');
    $result = [];

    foreach ($keys as $key) {
      $result[$key] = self::getValue($key, null, $scope);
    }

    return $result;
  }

  /**
   * Get project settings
   */
  public static function getProjectSettings(?array $scope = null): array
  {
    return [
      'project_name' => self::getValue('project_name', 'Vendra Invoice System', $scope),
      'project_logo' => self::getValue('project_logo', null, $scope),
      'meta_title' => self::getValue('meta_title', 'Vendra Invoice System', $scope),
      'meta_description' => self::getValue('meta_description', 'Professional invoice management system with FIRS integration', $scope),
      'meta_keywords' => self::getValue('meta_keywords', 'invoice, tax, firs, nigeria', $scope),
      'withholding_tax_rate' => self::getValue('withholding_tax_rate', 5.0, $scope),
      'withholding_tax_enabled' => self::getValue('withholding_tax_enabled', true, $scope),
    ];
  }

  /**
   * Update multiple settings at once
   */
  public static function updateSettings(array $settings): void
  {
    foreach ($settings as $key => $value) {
      if (is_array($value) && isset($value['value'])) {
        self::setValue($key, $value['value'], $value['description'] ?? null, $value['scope'] ?? null);
      } else {
        self::setValue($key, $value);
      }
    }
  }

  protected static function getSettingRecord(string $key, ?array $scope = null): ?self
  {
    $scope = self::resolveScope($scope);

    return self::query()
      ->where('key', $key)
      ->where('tenant_id', $scope['tenant_id'])
      ->where('organization_id', $scope['organization_id'])
      ->where('user_id', $scope['user_id'])
      ->first();
  }

  protected static function resolveScope(?array $scope = null): array
  {
    $user = Auth::user();

    $defaults = [
      'tenant_id' => $user?->tenant_id,
      'organization_id' => $user?->organization_id,
      'user_id' => null,
    ];

    $scope = array_merge($defaults, $scope ?? []);

    return [
      'tenant_id' => $scope['tenant_id'] ?? null,
      'organization_id' => $scope['organization_id'] ?? null,
      'user_id' => $scope['user_id'] ?? null,
    ];
  }
}
