<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('tin')
                    ->required()
                    ->minLength(5),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('business_description'),
                TextInput::make('street_name')
                    ->required(),
                TextInput::make('city_name')
                    ->required(),
                TextInput::make('postal_zone')
                    ->required(),
                TextInput::make('state')
                    ->required(),
                TextInput::make('country')
                    ->required()
                    ->default('NG'),
                TextInput::make('logo_path'),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
