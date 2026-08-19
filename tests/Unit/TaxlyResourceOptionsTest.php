<?php

namespace Tests\Unit;

use App\Services\TaxlyResourceOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxlyResourceOptionsTest extends TestCase
{
  use RefreshDatabase;

  public function test_hs_code_category_falls_back_to_the_code_when_unknown(): void
  {
    self::assertSame('0101.29', TaxlyResourceOptions::hsCodeCategory('0101.29'));
  }

  public function test_hs_code_category_resolves_known_code(): void
  {
    self::assertSame('Static converters', TaxlyResourceOptions::hsCodeCategory('8504.40'));
  }

  public function test_service_code_category_falls_back_to_the_code_when_unknown(): void
  {
    self::assertSame('6312', TaxlyResourceOptions::serviceCodeCategory('6312'));
  }

  public function test_service_code_category_resolves_known_code(): void
  {
    self::assertSame('Computer programming activities', TaxlyResourceOptions::serviceCodeCategory('6201'));
  }

  public function test_category_helpers_return_null_for_missing_codes(): void
  {
    self::assertNull(TaxlyResourceOptions::hsCodeCategory(null));
    self::assertNull(TaxlyResourceOptions::serviceCodeCategory(null));
  }
}