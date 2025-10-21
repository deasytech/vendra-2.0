<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Select::make('tenant_id')->relationship('tenant', 'name')->required(),
                    TextInput::make('legal_name')->required()->maxLength(255),
                    TextInput::make('registration_number')->label('RC Number')->maxLength(255),
                    TextInput::make('email')->email(),
                    TextInput::make('phone')->tel(),
                    TextInput::make('city_name')->label('City'),
                    Textarea::make('description')->columnSpanFull(),
                ])->columnSpanFull()->columns(2)
            ]);
    }
}
