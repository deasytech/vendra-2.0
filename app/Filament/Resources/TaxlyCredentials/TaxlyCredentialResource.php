<?php

namespace App\Filament\Resources\TaxlyCredentials;

use App\Filament\Resources\TaxlyCredentials\Pages\CreateTaxlyCredential;
use App\Filament\Resources\TaxlyCredentials\Pages\EditTaxlyCredential;
use App\Filament\Resources\TaxlyCredentials\Pages\ListTaxlyCredentials;
use App\Filament\Resources\TaxlyCredentials\Schemas\TaxlyCredentialForm;
use App\Filament\Resources\TaxlyCredentials\Tables\TaxlyCredentialsTable;
use App\Models\TaxlyCredential;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TaxlyCredentialResource extends Resource
{
    protected static ?string $model = TaxlyCredential::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! is_callable([$user, 'hasRole'])) {
            return false;
        }

        return is_callable([$user, 'hasRole']) ? call_user_func([$user, 'hasRole'], 'super admin') : false;
    }

    public static function form(Schema $schema): Schema
    {
        return TaxlyCredentialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxlyCredentialsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxlyCredentials::route('/'),
            'create' => CreateTaxlyCredential::route('/create'),
            'edit' => EditTaxlyCredential::route('/{record}/edit'),
        ];
    }
}
