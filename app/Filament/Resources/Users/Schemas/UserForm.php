<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Organization;
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
                        ->options(fn(): array => Organization::query()
                            ->whereNotNull('legal_name')
                            ->where('legal_name', '!=', '')
                            ->orderBy('legal_name')
                            ->limit(50)
                            ->pluck('legal_name', 'id')
                            ->toArray())

                        ->searchable()

                        ->getSearchResultsUsing(fn(string $search): array => Organization::query()
                            ->whereNotNull('legal_name')
                            ->where('legal_name', '!=', '')
                            ->where('legal_name', 'like', "%{$search}%")
                            ->orderBy('legal_name')
                            ->limit(50)
                            ->pluck('legal_name', 'id')
                            ->toArray())

                        ->getOptionLabelUsing(
                            fn($value): ?string =>
                            Organization::find($value)?->legal_name ?: 'Unknown Organization'
                        )

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
