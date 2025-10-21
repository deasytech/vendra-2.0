<?php

namespace App\Filament\Resources\TaxlyCredentials\Pages;

use App\Filament\Resources\TaxlyCredentials\TaxlyCredentialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxlyCredentials extends ListRecords
{
    protected static string $resource = TaxlyCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
