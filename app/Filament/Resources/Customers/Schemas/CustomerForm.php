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
                TextInput::make('tin'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('business_description'),
                TextInput::make('street_name'),
                TextInput::make('city_name'),
                TextInput::make('postal_zone'),
                TextInput::make('state'),
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
