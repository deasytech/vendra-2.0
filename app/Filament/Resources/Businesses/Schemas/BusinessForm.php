<?php

namespace App\Filament\Resources\Businesses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Select::make('organization_id')
                        ->relationship('organization', 'legal_name')
                        ->required(),

                    TextInput::make('name')
                        ->required(),

                    TextInput::make('tin')
                        ->label('TIN')
                        ->maxLength(50),

                    TextInput::make('email')
                        ->email(),

                    TextInput::make('telephone'),

                    TextInput::make('business_id')
                        ->label('Business ID')
                        ->required(),

                    Grid::make(3)->schema([
                        TextInput::make('service_id')
                            ->label('Service ID')
                            ->required(),

                        TextInput::make('reference')
                            ->label('Business Reference')
                            ->required(),

                        TextInput::make('sector')
                            ->required(),
                    ])->columnSpanFull(),

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
                ])->columnSpanFull()->columns(2),
            ]);
    }
}
