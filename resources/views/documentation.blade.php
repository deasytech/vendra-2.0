<x-layouts.app :title="__('Documentation')">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">

                    <!-- Documentation Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-4">
                            Vendra Documentation
                        </h1>
                        <p class="text-lg text-zinc-600 dark:text-zinc-400">
                            Comprehensive guide to using the Vendra Invoice Management System
                        </p>
                    </div>

                    <!-- Table of Contents -->
                    <div class="mb-8 p-6 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <h2 class="text-xl font-semibold mb-4">Table of Contents</h2>
                        <ul class="space-y-2">
                            <li><a href="#overview" class="text-blue-600 dark:text-blue-400 hover:underline">System
                                    Overview</a></li>
                            <li><a href="#getting-started"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">Getting Started</a></li>
                            <li><a href="#dashboard"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a></li>
                            <li><a href="#invoices" class="text-blue-600 dark:text-blue-400 hover:underline">Invoice
                                    Management</a></li>
                            <li><a href="#customers" class="text-blue-600 dark:text-blue-400 hover:underline">Customer
                                    Management</a></li>
                            <li><a href="#invoice-exchange"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">Invoice Exchange</a></li>
                            <li><a href="#settings" class="text-blue-600 dark:text-blue-400 hover:underline">Settings &
                                    Configuration</a></li>
                            <li><a href="#tax-integration" class="text-blue-600 dark:text-blue-400 hover:underline">Tax
                                    Integration</a></li>
                            <li><a href="#troubleshooting"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">Troubleshooting</a></li>
                            <li><a href="#best-practices" class="text-blue-600 dark:text-blue-400 hover:underline">Best
                                    Practices</a></li>
                        </ul>
                    </div>

                    <!-- System Overview -->
                    <section id="overview" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">System Overview</h2>

                        <div class="prose dark:prose-invert max-w-none">
                            <p class="mb-4">
                                Vendra is a comprehensive invoice management system designed for Nigerian businesses to
                                create, manage,
                                and transmit invoices in compliance with FIRS (Federal Inland Revenue Service)
                                regulations. The system
                                integrates with Taxly.ng for seamless tax reporting and invoice validation.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Key Features</h3>
                            <ul class="list-disc pl-6 space-y-2 mb-4">
                                <li>Multi-tenant invoice management with organization isolation</li>
                                <li>FIRS-compliant invoice generation and transmission</li>
                                <li>Integration with Taxly.ng for tax validation and reporting</li>
                                <li>Customer management with TIN validation</li>
                                <li>QR code generation for invoice verification</li>
                                <li>Real-time invoice transmission status tracking</li>
                                <li>Comprehensive filtering and search capabilities</li>
                                <li>Multi-currency support (NGN, USD, EUR, GBP, CAD, GHS)</li>
                                <li>Secure user authentication with two-factor authentication</li>
                                <li>Responsive web interface with dark mode support</li>
                            </ul>

                            <h3 class="text-xl font-semibold mt-6 mb-3">System Architecture</h3>
                            <p class="mb-4">
                                Vendra is built on Laravel framework with Livewire components for reactive user
                                interfaces.
                                The system uses PostgreSQL for data storage and implements a multi-tenant architecture
                                where
                                each organization has isolated data. Integration with external tax services is handled
                                through
                                RESTful APIs with proper error handling and retry mechanisms.
                            </p>
                        </div>
                    </section>

                    <!-- Getting Started -->
                    <section id="getting-started" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Getting Started</h2>

                        <div class="space-y-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4">
                                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">First Time Setup</h3>
                                <p class="text-blue-700 dark:text-blue-300 mt-2">
                                    When you first log in, you'll be prompted to complete your organization profile.
                                    This information is required for generating FIRS-compliant invoices.
                                </p>
                            </div>

                            <h3 class="text-xl font-semibold">Required Information</h3>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>Phone Number:</strong> Contact number for your organization</li>
                                <li><strong>Registration Number:</strong> Your business registration number (optional)
                                </li>
                                <li><strong>Postal Address:</strong> Complete business address including street, city,
                                    state, and postal code</li>
                                <li><strong>Business Description:</strong> Brief description of your business activities
                                    (minimum 50 characters)</li>
                            </ul>

                            <h3 class="text-xl font-semibold mt-6">Navigation Overview</h3>
                            <p class="mb-4">
                                The main navigation is located in the sidebar and provides access to all major features:
                            </p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>Dashboard:</strong> Overview of your invoice activity and quick actions</li>
                                <li><strong>Invoices:</strong> Create, view, and manage all your invoices</li>
                                <li><strong>Customers:</strong> Manage your customer database</li>
                                <li><strong>Invoice Exchange:</strong> View transmitted invoices and their status</li>
                                <li><strong>Settings:</strong> Configure your profile and system preferences</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Dashboard -->
                    <section id="dashboard" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Dashboard</h2>

                        <div class="space-y-6">
                            <p class="mb-4">
                                The dashboard provides a comprehensive overview of your invoice management activities
                                and
                                serves as your central hub for quick access to important functions.
                            </p>

                            <h3 class="text-xl font-semibold">Dashboard Features</h3>
                            <div class="grid md:grid-cols-2 gap-6 mt-4">
                                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Quick Stats</h4>
                                    <ul class="list-disc pl-6 space-y-1">
                                        <li>Total invoices created</li>
                                        <li>Invoices pending transmission</li>
                                        <li>Successfully transmitted invoices</li>
                                        <li>Failed transmissions requiring attention</li>
                                    </ul>
                                </div>
                                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Organization Status</h4>
                                    <ul class="list-disc pl-6 space-y-1">
                                        <li>Profile completion status</li>
                                        <li>Tax integration connectivity</li>
                                        <li>Recent activity summary</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                                <h4 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">Organization
                                    Profile Reminder</h4>
                                <p class="text-yellow-700 dark:text-yellow-300 mt-2">
                                    If your organization profile is incomplete, you'll see a modal prompting you to
                                    complete the required information. This is essential for generating valid invoices.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Invoice Management -->
                    <section id="invoices" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Invoice Management</h2>

                        <div class="space-y-8">
                            <h3 class="text-xl font-semibold">Creating Invoices</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <ol class="list-decimal pl-6 space-y-3">
                                    <li>Navigate to <strong>Invoices</strong> in the sidebar</li>
                                    <li>Click the <strong>Create Invoice</strong> button</li>
                                    <li>Select a customer from your database or add a new one</li>
                                    <li>Fill in invoice details:
                                        <ul class="list-disc pl-6 mt-2 space-y-1">
                                            <li>Invoice reference number</li>
                                            <li>Issue date and due date</li>
                                            <li>Invoice type (Standard, Credit Note, etc.)</li>
                                            <li>Currency (NGN, USD, EUR, GBP, CAD, GHS)</li>
                                        </ul>
                                    </li>
                                    <li>Add invoice line items with descriptions, quantities, and prices</li>
                                    <li>Apply appropriate tax categories</li>
                                    <li>Review and save the invoice</li>
                                </ol>
                            </div>

                            <h3 class="text-xl font-semibold">Invoice Status Management</h3>
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600">
                                    <thead class="bg-zinc-100 dark:bg-zinc-600">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Status</th>
                                            <th class="px-4 py-2 text-left">Description</th>
                                            <th class="px-4 py-2 text-left">Actions Available</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-600">
                                        <tr>
                                            <td class="px-4 py-2 font-medium">Draft</td>
                                            <td class="px-4 py-2">Invoice created but not yet transmitted</td>
                                            <td class="px-4 py-2">Edit, Delete, Transmit</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">Transmitting</td>
                                            <td class="px-4 py-2">Currently being processed by Taxly</td>
                                            <td class="px-4 py-2">View Status</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">Transmitted</td>
                                            <td class="px-4 py-2">Successfully sent to FIRS</td>
                                            <td class="px-4 py-2">View, Download</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">Failed</td>
                                            <td class="px-4 py-2">Transmission failed, requires retry</td>
                                            <td class="px-4 py-2">Retry Transmission</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-xl font-semibold">Advanced Filtering</h3>
                            <p class="mb-4">
                                The invoices page includes comprehensive filtering options to help you find specific
                                invoices:
                            </p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>Search:</strong> Search by invoice reference or IRN (Invoice Reference
                                    Number)</li>
                                <li><strong>Customer:</strong> Filter by specific customer</li>
                                <li><strong>Payment Status:</strong> Filter by paid, pending, or overdue</li>
                                <li><strong>Transmission Status:</strong> Filter by transmission state</li>
                                <li><strong>Currency:</strong> Filter by invoice currency</li>
                                <li><strong>Date Range:</strong> Filter by issue date range</li>
                                <li><strong>Amount Range:</strong> Filter by invoice amount</li>
                            </ul>

                            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4">
                                <h4 class="text-lg font-semibold text-red-800 dark:text-red-200">Important Notes</h4>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-red-700 dark:text-red-300">
                                    <li>Invoices with IRN (Invoice Reference Number) cannot be deleted</li>
                                    <li>Only failed transmissions can be retried</li>
                                    <li>Successfully transmitted invoices cannot be modified</li>
                                    <li>Always verify customer TIN before creating invoices</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Customer Management -->
                    <section id="customers" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Customer Management</h2>

                        <div class="space-y-6">
                            <p class="mb-4">
                                Effective customer management is crucial for creating valid, FIRS-compliant invoices.
                                Each customer record includes all necessary information for tax reporting.
                            </p>

                            <h3 class="text-xl font-semibold">Adding Customers</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <ol class="list-decimal pl-6 space-y-3">
                                    <li>Navigate to <strong>Customers</strong> in the sidebar</li>
                                    <li>Click the <strong>Create Customer</strong> button</li>
                                    <li>Fill in customer information:
                                        <ul class="list-disc pl-6 mt-2 space-y-1">
                                            <li><strong>Name:</strong> Customer's legal business name</li>
                                            <li><strong>TIN:</strong> Tax Identification Number (validated against FIRS
                                                database)</li>
                                            <li><strong>Email:</strong> Primary contact email</li>
                                            <li><strong>Phone:</strong> Contact phone number</li>
                                            <li><strong>Business Description:</strong> Brief description of customer's
                                                business</li>
                                            <li><strong>Address:</strong> Complete postal address</li>
                                        </ul>
                                    </li>
                                    <li>Upload customer logo (optional)</li>
                                    <li>Save the customer record</li>
                                </ol>
                            </div>

                            <h3 class="text-xl font-semibold">TIN Validation</h3>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4">
                                <p class="text-blue-700 dark:text-blue-300">
                                    The system automatically validates TIN numbers against the FIRS database to ensure
                                    accuracy and compliance. Invalid TIN numbers will prevent invoice creation.
                                </p>
                            </div>

                            <h3 class="text-xl font-semibold">Customer Status</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                    <h4 class="font-semibold text-green-800 dark:text-green-200">Active</h4>
                                    <p class="text-green-700 dark:text-green-300">Customer can be used for new invoices
                                    </p>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                    <h4 class="font-semibold text-red-800 dark:text-red-200">Inactive</h4>
                                    <p class="text-red-700 dark:text-red-300">Customer cannot be used for new invoices
                                    </p>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">Managing Existing Customers</h3>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>View:</strong> Access complete customer details and invoice history</li>
                                <li><strong>Edit:</strong> Update customer information as needed</li>
                                <li><strong>Status Toggle:</strong> Activate or deactivate customers</li>
                                <li><strong>Search:</strong> Find customers by name, TIN, or email</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Invoice Exchange -->
                    <section id="invoice-exchange" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Invoice Exchange</h2>

                        <div class="space-y-6">
                            <p class="mb-4">
                                The Invoice Exchange section provides a centralized view of all transmitted invoices and
                                their current status with the tax authorities.
                            </p>

                            <h3 class="text-xl font-semibold">Transmission Process</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <ol class="list-decimal pl-6 space-y-3">
                                    <li>Invoice is created and saved in the system</li>
                                    <li>Invoice details are validated for FIRS compliance</li>
                                    <li>Invoice is transmitted to Taxly.ng for processing</li>
                                    <li>Taxly.ng validates and signs the invoice</li>
                                    <li>Signed invoice is sent to FIRS systems</li>
                                    <li>IRN (Invoice Reference Number) is generated</li>
                                    <li>QR code is generated for invoice verification</li>
                                </ol>
                            </div>

                            <h3 class="text-xl font-semibold">Transmission Status Tracking</h3>
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600">
                                    <thead class="bg-zinc-100 dark:bg-zinc-600">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Status</th>
                                            <th class="px-4 py-2 text-left">Meaning</th>
                                            <th class="px-4 py-2 text-left">Typical Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-600">
                                        <tr>
                                            <td class="px-4 py-2 font-medium">PENDING</td>
                                            <td class="px-4 py-2">Ready for transmission</td>
                                            <td class="px-4 py-2">N/A</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">TRANSMITTING</td>
                                            <td class="px-4 py-2">Currently being processed</td>
                                            <td class="px-4 py-2">1-5 minutes</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">TRANSMITTED</td>
                                            <td class="px-4 py-2">Successfully processed</td>
                                            <td class="px-4 py-2">Complete</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-medium">FAILED</td>
                                            <td class="px-4 py-2">Transmission error occurred</td>
                                            <td class="px-4 py-2">Requires manual intervention</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-xl font-semibold">Failed Transmission Handling</h3>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">Common Failure Reasons
                                </h4>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-yellow-700 dark:text-yellow-300">
                                    <li>Network connectivity issues</li>
                                    <li>Invalid customer TIN information</li>
                                    <li>Taxly.ng service unavailability</li>
                                    <li>Invoice validation errors</li>
                                </ul>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 p-4">
                                <h4 class="font-semibold text-green-800 dark:text-green-200">Retry Process</h4>
                                <p class="text-green-700 dark:text-green-300 mt-2">
                                    Failed transmissions can be retried by clicking the "Retry Transmission" button.
                                    The system will attempt to retransmit the invoice with updated validation.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Settings -->
                    <section id="settings" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Settings & Configuration</h2>

                        <div class="space-y-8">
                            <h3 class="text-xl font-semibold">Profile Settings</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Personal Information</h4>
                                    <ul class="list-disc pl-6 space-y-1">
                                        <li>Update name and email</li>
                                        <li>Change profile picture</li>
                                        <li>Manage contact preferences</li>
                                    </ul>
                                </div>
                                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Security Settings</h4>
                                    <ul class="list-disc pl-6 space-y-1">
                                        <li>Change password</li>
                                        <li>Enable two-factor authentication</li>
                                        <li>Manage recovery codes</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">Organization Settings</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <h4 class="font-semibold mb-3">General Settings</h4>
                                <ul class="list-disc pl-6 space-y-2">
                                    <li><strong>Business Information:</strong> Update organization name, TIN, and
                                        registration details</li>
                                    <li><strong>Contact Details:</strong> Manage phone, email, and postal address</li>
                                    <li><strong>Business Description:</strong> Update your business activity description
                                    </li>
                                    <li><strong>Logo Management:</strong> Upload and manage your organization logo</li>
                                </ul>
                            </div>

                            <h3 class="text-xl font-semibold">Appearance Settings</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <ul class="list-disc pl-6 space-y-2">
                                    <li><strong>Theme:</strong> Switch between light and dark modes</li>
                                    <li><strong>Language:</strong> Change interface language (if available)</li>
                                    <li><strong>Timezone:</strong> Set your preferred timezone</li>
                                </ul>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4">
                                <h4 class="font-semibold text-blue-800 dark:text-blue-200">Configuration Tips</h4>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-blue-700 dark:text-blue-300">
                                    <li>Ensure your organization TIN is correct before creating invoices</li>
                                    <li>Upload a high-quality logo for professional invoice appearance</li>
                                    <li>Enable two-factor authentication for enhanced security</li>
                                    <li>Keep your business description updated and comprehensive</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Tax Integration -->
                    <section id="tax-integration" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Tax Integration</h2>

                        <div class="space-y-8">
                            <p class="mb-4">
                                Vendra integrates seamlessly with Taxly.ng to ensure your invoices meet FIRS
                                requirements
                                and are properly reported to tax authorities.
                            </p>

                            <h3 class="text-xl font-semibold">Taxly.ng Integration Features</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Invoice Validation</h4>
                                    <ul class="list-disc pl-6 space-y-1">
                                        <li>Structure validation</li>
                                        <li>Data format verification</li>
                                        <li>Compliance checking</li>
                                    </ul>
                                </div>
                                <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Tax Reporting</h4>
                                    <ul class="list-disc pl-6 space-y-1">
                                        <li>Automatic IRN generation</li>
                                        <li>FIRS submission</li>
                                        <li>Status tracking</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">FIRS QR Code Generation</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <p class="mb-4">
                                    Each transmitted invoice receives a unique QR code that can be scanned to verify
                                    authenticity and access invoice details. The QR code contains encrypted information
                                    including:
                                </p>
                                <ul class="list-disc pl-6 space-y-2">
                                    <li>Invoice Reference Number (IRN)</li>
                                    <li>Timestamp of generation</li>
                                    <li>Encrypted certificate data</li>
                                    <li>Verification hash</li>
                                </ul>
                            </div>

                            <h3 class="text-xl font-semibold">Multi-Currency Support</h3>
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600">
                                    <thead class="bg-zinc-100 dark:bg-zinc-600">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Currency</th>
                                            <th class="px-4 py-2 text-left">Code</th>
                                            <th class="px-4 py-2 text-left">Use Case</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-600">
                                        <tr>
                                            <td class="px-4 py-2">Nigerian Naira</td>
                                            <td class="px-4 py-2 font-mono">NGN</td>
                                            <td class="px-4 py-2">Default for Nigerian transactions</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2">US Dollar</td>
                                            <td class="px-4 py-2 font-mono">USD</td>
                                            <td class="px-4 py-2">International transactions</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2">Euro</td>
                                            <td class="px-4 py-2 font-mono">EUR</td>
                                            <td class="px-4 py-2">European transactions</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2">British Pound</td>
                                            <td class="px-4 py-2 font-mono">GBP</td>
                                            <td class="px-4 py-2">UK transactions</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2">Canadian Dollar</td>
                                            <td class="px-4 py-2 font-mono">CAD</td>
                                            <td class="px-4 py-2">Canadian transactions</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2">Ghanaian Cedi</td>
                                            <td class="px-4 py-2 font-mono">GHS</td>
                                            <td class="px-4 py-2">Ghanaian transactions</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 p-4">
                                <h4 class="font-semibold text-green-800 dark:text-green-200">Compliance Benefits</h4>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-green-700 dark:text-green-300">
                                    <li>Automatic tax calculation and reporting</li>
                                    <li>Reduced risk of penalties for non-compliance</li>
                                    <li>Streamlined audit processes</li>
                                    <li>Real-time validation of invoice data</li>
                                    <li>Secure transmission to tax authorities</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Troubleshooting -->
                    <section id="troubleshooting" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Troubleshooting</h2>

                        <div class="space-y-8">
                            <h3 class="text-xl font-semibold">Common Issues and Solutions</h3>

                            <div class="space-y-6">
                                <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden">
                                    <div class="bg-zinc-100 dark:bg-zinc-600 px-4 py-3">
                                        <h4 class="font-semibold">Invoice Transmission Failed</h4>
                                    </div>
                                    <div class="p-4">
                                        <p class="mb-3"><strong>Possible Causes:</strong></p>
                                        <ul class="list-disc pl-6 space-y-1 mb-4">
                                            <li>Network connectivity issues</li>
                                            <li>Invalid customer TIN</li>
                                            <li>Taxly service unavailable</li>
                                            <li>Invoice data validation errors</li>
                                        </ul>
                                        <p class="mb-3"><strong>Solutions:</strong></p>
                                        <ol class="list-decimal pl-6 space-y-1">
                                            <li>Check your internet connection</li>
                                            <li>Verify customer TIN is valid and active</li>
                                            <li>Wait a few minutes and retry transmission</li>
                                            <li>Check invoice details for completeness</li>
                                            <li>Contact support if issue persists</li>
                                        </ol>
                                    </div>
                                </div>

                                <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden">
                                    <div class="bg-zinc-100 dark:bg-zinc-600 px-4 py-3">
                                        <h4 class="font-semibold">Cannot Create Invoice</h4>
                                    </div>
                                    <div class="p-4">
                                        <p class="mb-3"><strong>Possible Causes:</strong></p>
                                        <ul class="list-disc pl-6 space-y-1 mb-4">
                                            <li>Organization profile incomplete</li>
                                            <li>Customer TIN validation failed</li>
                                            <li>Required fields missing</li>
                                            <li>System maintenance in progress</li>
                                        </ul>
                                        <p class="mb-3"><strong>Solutions:</strong></p>
                                        <ol class="list-decimal pl-6 space-y-1">
                                            <li>Complete your organization profile in settings</li>
                                            <li>Verify customer information and TIN</li>
                                            <li>Ensure all required fields are filled</li>
                                            <li>Check system status page for maintenance</li>
                                        </ol>
                                    </div>
                                </div>

                                <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden">
                                    <div class="bg-zinc-100 dark:bg-zinc-600 px-4 py-3">
                                        <h4 class="font-semibold">QR Code Not Generating</h4>
                                    </div>
                                    <div class="p-4">
                                        <p class="mb-3"><strong>Possible Causes:</strong></p>
                                        <ul class="list-disc pl-6 space-y-1 mb-4">
                                            <li>Invoice not yet transmitted</li>
                                            <li>Transmission still in progress</li>
                                            <li>FIRS certificate configuration issue</li>
                                        </ul>
                                        <p class="mb-3"><strong>Solutions:</strong></p>
                                        <ol class="list-decimal pl-6 space-y-1">
                                            <li>Wait for transmission to complete</li>
                                            <li>Check transmission status in Invoice Exchange</li>
                                            <li>Contact support for certificate issues</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">System Status Indicators</h3>
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg text-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mx-auto mb-2"></div>
                                    <h4 class="font-semibold text-green-800 dark:text-green-200">Operational</h4>
                                    <p class="text-sm text-green-700 dark:text-green-300">All systems functioning
                                        normally</p>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg text-center">
                                    <div class="w-4 h-4 bg-yellow-500 rounded-full mx-auto mb-2"></div>
                                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">Degraded</h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">Some features may be slow
                                    </p>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg text-center">
                                    <div class="w-4 h-4 bg-red-500 rounded-full mx-auto mb-2"></div>
                                    <h4 class="font-semibold text-red-800 dark:text-red-200">Down</h4>
                                    <p class="text-sm text-red-700 dark:text-red-300">Service unavailable</p>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">Getting Help</h3>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4">
                                <h4 class="font-semibold text-blue-800 dark:text-blue-200">Support Channels</h4>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-blue-700 dark:text-blue-300">
                                    <li>Email: support@vendra.com</li>
                                    <li>Phone: +234-XXX-XXX-XXXX</li>
                                    <li>Live Chat: Available during business hours</li>
                                    <li>Documentation: This comprehensive guide</li>
                                </ul>
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">Information to Provide
                                </h4>
                                <p class="text-yellow-700 dark:text-yellow-300 mt-2">
                                    When contacting support, please include:
                                </p>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-yellow-700 dark:text-yellow-300">
                                    <li>Your organization name and TIN</li>
                                    <li>Specific error messages (screenshots helpful)</li>
                                    <li>Steps to reproduce the issue</li>
                                    <li>Invoice reference numbers (if applicable)</li>
                                    <li>Browser and operating system information</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Best Practices -->
                    <section id="best-practices" class="mb-12">
                        <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Best Practices</h2>

                        <div class="space-y-8">
                            <h3 class="text-xl font-semibold">Invoice Management</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                    <h4 class="font-semibold text-green-800 dark:text-green-200 mb-2">Do's</h4>
                                    <ul class="list-disc pl-6 space-y-1 text-green-700 dark:text-green-300">
                                        <li>Verify customer TIN before creating invoices</li>
                                        <li>Use clear, descriptive invoice references</li>
                                        <li>Include detailed line item descriptions</li>
                                        <li>Set appropriate payment terms</li>
                                        <li>Review invoices before transmission</li>
                                        <li>Keep customer information updated</li>
                                    </ul>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                    <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Don'ts</h4>
                                    <ul class="list-disc pl-6 space-y-1 text-red-700 dark:text-red-300">
                                        <li>Create invoices with invalid TIN numbers</li>
                                        <li>Transmit incomplete invoices</li>
                                        <li>Delete invoices after transmission</li>
                                        <li>Use vague item descriptions</li>
                                        <li>Ignore transmission failure alerts</li>
                                        <li>Delay customer information updates</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">Data Management</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <h4 class="font-semibold mb-3">Regular Maintenance Tasks</h4>
                                <ul class="list-disc pl-6 space-y-2">
                                    <li><strong>Monthly:</strong> Review and update customer information</li>
                                    <li><strong>Quarterly:</strong> Verify organization details and tax information</li>
                                    <li><strong>Annually:</strong> Audit invoice records and transmission logs</li>
                                    <li><strong>As Needed:</strong> Update user access and permissions</li>
                                </ul>
                            </div>

                            <h3 class="text-xl font-semibold">Security Recommendations</h3>
                            <div class="space-y-4">
                                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4">
                                    <h4 class="font-semibold text-blue-800 dark:text-blue-200">User Access</h4>
                                    <ul class="list-disc pl-6 mt-2 space-y-1 text-blue-700 dark:text-blue-300">
                                        <li>Use strong, unique passwords</li>
                                        <li>Enable two-factor authentication</li>
                                        <li>Regularly review user access levels</li>
                                        <li>Remove access for former employees immediately</li>
                                    </ul>
                                </div>

                                <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-400 p-4">
                                    <h4 class="font-semibold text-purple-800 dark:text-purple-200">Data Protection</h4>
                                    <ul class="list-disc pl-6 mt-2 space-y-1 text-purple-700 dark:text-purple-300">
                                        <li>Regular backup of important data</li>
                                        <li>Secure storage of API credentials</li>
                                        <li>Monitor system access logs</li>
                                        <li>Keep software updated</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold">Performance Optimization</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 p-6 rounded-lg">
                                <h4 class="font-semibold mb-3">System Performance</h4>
                                <ul class="list-disc pl-6 space-y-2">
                                    <li>Use specific search terms instead of broad queries</li>
                                    <li>Apply filters to narrow down large datasets</li>
                                    <li>Regularly clear browser cache and cookies</li>
                                    <li>Use modern browsers for optimal performance</li>
                                    <li>Avoid creating multiple browser tabs of the same application</li>
                                </ul>
                            </div>

                            <h3 class="text-xl font-semibold">Compliance Guidelines</h3>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">FIRS Compliance</h4>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-yellow-700 dark:text-yellow-300">
                                    <li>Ensure all invoices are transmitted to FIRS</li>
                                    <li>Maintain accurate customer TIN records</li>
                                    <li>Use correct tax categories for products/services</li>
                                    <li>Keep invoice records for audit purposes</li>
                                    <li>Respond promptly to transmission failures</li>
                                    <li>Regular reconciliation of transmitted invoices</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Footer -->
                    <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-600">
                        <p class="text-center text-zinc-600 dark:text-zinc-400">
                            This documentation is regularly updated. For the latest version, visit our documentation
                            portal.
                        </p>
                        <p class="text-center text-sm text-zinc-500 dark:text-zinc-500 mt-2">
                            Vendra Invoice Management System - Version 2.0 | Last Updated: December 2025
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
