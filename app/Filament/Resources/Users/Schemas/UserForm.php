<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Select::make('organization_id')
                        ->label('Organization')
                        ->relationship('organization', 'legal_name')
                        ->searchable()
                        ->preload(),
                    TextInput::make('mfa'),
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required(),
                    DateTimePicker::make('email_verified_at'),
                    TextInput::make('password')
                        ->password()
                        ->required(fn($get) => $get('id') === null)
                        ->dehydrated(fn($state) => filled($state)),
                ])->columnSpanFull()->columns(2),
            ]);
    }
}
