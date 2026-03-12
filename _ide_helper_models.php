<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string|null $tin
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $business_description
 * @property string|null $street_name
 * @property string|null $city_name
 * @property string|null $postal_zone
 * @property string|null $state
 * @property string $country
 * @property string|null $logo_path
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Database\Factories\CustomerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereBusinessDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePostalZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereStreetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereTin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 */
	class Customer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int|null $organization_id
 * @property int|null $customer_id
 * @property string $invoice_reference
 * @property string|null $irn
 * @property \Illuminate\Support\Carbon|null $issue_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string|null $invoice_type_code
 * @property string $document_currency_code
 * @property string $payment_status
 * @property array<array-key, mixed>|null $note
 * @property array<array-key, mixed>|null $payment_terms_note
 * @property array<array-key, mixed>|null $accounting_supplier_party
 * @property array<array-key, mixed>|null $accounting_customer_party
 * @property array<array-key, mixed>|null $legal_monetary_total
 * @property array<array-key, mixed>|null $metadata
 * @property string $transmit
 * @property int $delivered
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceLine> $lines
 * @property-read int|null $lines_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTaxTotal> $taxTotals
 * @property-read int|null $tax_totals_count
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTransmission> $transmissions
 * @property-read int|null $transmissions_count
 * @method static \Database\Factories\InvoiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAccountingCustomerParty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAccountingSupplierParty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDocumentCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceTypeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereLegalMonetaryTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentTermsNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTransmit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice withoutTrashed()
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property string $filename
 * @property string $path
 * @property string|null $mime
 * @property array<array-key, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceAttachment whereUpdatedAt($value)
 */
	class InvoiceAttachment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property string|null $hsn_code
 * @property string|null $product_category
 * @property numeric $discount_rate
 * @property numeric $discount_amount
 * @property numeric $fee_rate
 * @property numeric $fee_amount
 * @property numeric $invoiced_quantity
 * @property numeric $line_extension_amount
 * @property array<array-key, mixed>|null $item
 * @property array<array-key, mixed>|null $price
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice $invoice
 * @method static \Database\Factories\InvoiceLineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereDiscountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereFeeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereFeeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereHsnCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereInvoicedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereLineExtensionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereProductCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceLine whereUpdatedAt($value)
 */
	class InvoiceLine extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property numeric $tax_amount
 * @property array<array-key, mixed>|null $tax_subtotal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice $invoice
 * @method static \Database\Factories\InvoiceTaxTotalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal whereTaxSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxTotal whereUpdatedAt($value)
 */
	class InvoiceTaxTotal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property string|null $irn
 * @property string $action
 * @property array<array-key, mixed>|null $request_payload
 * @property array<array-key, mixed>|null $response_payload
 * @property string|null $status
 * @property string|null $message
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $transmitted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice $invoice
 * @method static \Database\Factories\InvoiceTransmissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereIrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereRequestPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereResponsePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereTransmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTransmission whereUpdatedAt($value)
 */
	class InvoiceTransmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tenant_id
 * @property string|null $service_id
 * @property string|null $tin
 * @property string|null $business_id
 * @property string|null $registration_number
 * @property string|null $legal_name
 * @property string|null $slug
 * @property string|null $email
 * @property string|null $phone
 * @property array<array-key, mixed>|null $postal_address
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\OrganizationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization wherePostalAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereTin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization withoutTrashed()
 */
	class Organization extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string|null $description
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string|null $tenant_name
 * @property int|null $organization_id
 * @property string $auth_type
 * @property string|null $api_key
 * @property string|null $api_key_id
 * @property array<array-key, mixed>|null $api_key_permissions
 * @property string|null $token
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 * @property string $base_url
 * @property bool $is_integrator
 * @property string $integrator_status
 * @property string|null $integrator_contact_email
 * @property array<array-key, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Database\Factories\TaxlyCredentialFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereApiKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereApiKeyPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereAuthType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereBaseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereIntegratorContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereIntegratorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereIsIntegrator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereTenantName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxlyCredential whereUpdatedAt($value)
 */
	class TaxlyCredential extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $brand
 * @property string|null $domain
 * @property string|null $entity_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Customer> $customers
 * @property-read int|null $customers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organization> $organizations
 * @property-read int|null $organizations_count
 * @property-read \App\Models\TaxlyCredential|null $taxlyCredential
 * @method static \Database\Factories\TenantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant withoutTrashed()
 */
	class Tenant extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int|null $organization_id
 * @property string|null $logo_path
 * @property int $is_landlord
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organization> $organizations
 * @property-read int|null $organizations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsLandlord($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

