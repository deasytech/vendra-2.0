<?php

namespace App\Filament\Resources\TaxlyCredentials\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxlyCredentialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Select::make('tenant_id')->relationship('tenant', 'name')->required(),
                    Select::make('auth_type')->options([
                        'api_key' => 'API Key',
                        'token' => 'Bearer Token',
                    ])->default('api_key')->required(),
                    TextInput::make('api_key')->password()->label('X-Api-Key'),
                    TextInput::make('token')->label('Bearer Token'),
                    DateTimePicker::make('token_expires_at'),
                    TextInput::make('base_url')->default('https://taxly.ng'),
                ])->columnSpanFull()->columns(2)
            ]);
    }
}
