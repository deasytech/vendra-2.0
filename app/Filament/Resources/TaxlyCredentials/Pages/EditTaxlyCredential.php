<?php

namespace App\Filament\Resources\TaxlyCredentials\Pages;

use App\Filament\Resources\TaxlyCredentials\TaxlyCredentialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxlyCredential extends EditRecord
{
    protected static string $resource = TaxlyCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
