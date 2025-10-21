<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('invoice_reference')->required(),
                    DatePicker::make('issue_date')->required(),
                    DatePicker::make('due_date'),
                    Select::make('payment_status')
                        ->options(['PENDING' => 'PENDING', 'PAID' => 'PAID'])
                        ->default('PENDING'),
                    Textarea::make('note')->columnSpanFull(),
                ])->columnSpanFull()->columns(2)
            ]);
    }
}
