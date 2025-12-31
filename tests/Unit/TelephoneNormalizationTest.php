<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Organization;
use Tests\TestCase;

class TelephoneNormalizationTest extends TestCase
{
  /**
   * Test customer telephone normalization
   */
  public function test_customer_telephone_normalization()
  {
    $customer = new Customer();

    // Test the specific case from the log: 8099500265 should become +2348099500265
    $customer->phone = '8099500265';
    $partyObject = $customer->toPartyObject();
    $this->assertEquals('+2348099500265', $partyObject['telephone']);

    // Test other common formats
    $testCases = [
      '08099500265' => '+2348099500265',   // Local format with leading 0
      '2348099500265' => '+2348099500265', // Already has country code
      '+2348099500265' => '+2348099500265', // Already internationalized
      '7031234567' => '+2347031234567',    // Another local format
      '09012345678' => '+2349012345678',   // Local format with leading 0
    ];

    foreach ($testCases as $input => $expected) {
      $customer->phone = $input;
      $partyObject = $customer->toPartyObject();
      $this->assertEquals($expected, $partyObject['telephone'], "Failed for input: $input");
    }
  }

  /**
   * Test organization telephone normalization
   */
  public function test_organization_telephone_normalization()
  {
    $organization = new Organization();

    // Test the specific case from the log: 8099500265 should become +2348099500265
    $organization->phone = '8099500265';
    $partyObject = $organization->toPartyObject();
    $this->assertEquals('+2348099500265', $partyObject['telephone']);

    // Test other common formats
    $testCases = [
      '08099500265' => '+2348099500265',   // Local format with leading 0
      '2348099500265' => '+2348099500265', // Already has country code
      '+2348099500265' => '+2348099500265', // Already internationalized
      '7031234567' => '+2347031234567',    // Another local format
      '09012345678' => '+2349012345678',   // Local format with leading 0
    ];

    foreach ($testCases as $input => $expected) {
      $organization->phone = $input;
      $partyObject = $organization->toPartyObject();
      $this->assertEquals($expected, $partyObject['telephone'], "Failed for input: $input");
    }
  }

  /**
   * Test edge cases
   */
  public function test_edge_cases()
  {
    $customer = new Customer();

    // Test empty phone number
    $customer->phone = '';
    $partyObject = $customer->toPartyObject();
    $this->assertNull($partyObject['telephone']);

    // Test null phone number
    $customer->phone = null;
    $partyObject = $customer->toPartyObject();
    $this->assertNull($partyObject['telephone']);

    // Test phone number with special characters
    $customer->phone = '(080) 995-00265';
    $partyObject = $customer->toPartyObject();
    $this->assertEquals('+2348099500265', $partyObject['telephone']);
  }
}
