<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('domain')->maxLength(255),
                    TextInput::make('entity_id')->label('FIRS Entity ID')->maxLength(255),
                    FileUpload::make('brand')->label('Company Logo')->columnSpanFull(),
                ])->columnSpanFull()->columns(3)
            ]);
    }
}
