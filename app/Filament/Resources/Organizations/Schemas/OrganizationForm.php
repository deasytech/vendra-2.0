<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('service_id'),
                TextInput::make('tin'),
                TextInput::make('business_id'),
                TextInput::make('registration_number'),
                TextInput::make('legal_name'),
                TextInput::make('slug'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Fieldset::make('postal_address')
                    ->label('Postal Address')
                    ->schema([
                        TextInput::make('postal_address.street_name')
                            ->label('Street Address')
                            ->required(),

                        TextInput::make('postal_address.city_name')
                            ->label('City')
                            ->required(),

                        Grid::make(3)->schema([
                            TextInput::make('postal_address.postal_zone')
                                ->label('Postal Code')
                                ->required(),

                            TextInput::make('postal_address.state_name')
                                ->label('State')
                                ->required(),

                            Select::make('postal_address.country')
                                ->label('Country')
                                ->options([
                                    'NG' => 'Nigeria',
                                    'GH' => 'Ghana',
                                    'KE' => 'Kenya',
                                    'US' => 'United States',
                                    'GB' => 'United Kingdom',
                                ])
                                ->default('NG')
                                ->required(),
                        ])->columnSpanFull()
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
