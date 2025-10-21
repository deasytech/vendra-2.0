<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Invoice;
use App\Services\TaxlyService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Throwable;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_reference')->searchable()->sortable(),
                TextColumn::make('irn')->label('IRN')->copyable(),
                TextColumn::make('issue_date')->date(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->colors(['success' => 'PAID', 'warning' => 'PENDING']),
                TextColumn::make('transmit')->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('Transmit')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->label('Transmit to FIRS')
                        ->action(function (Invoice $record) {
                            if (empty($record->irn)) {
                                Notification::make()
                                    ->title('IRN Missing')
                                    ->body('This invoice does not have a valid IRN and cannot be transmitted.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            try {
                                $service = new TaxlyService($record->tenant->taxlyCredential);
                                $response = $service->transmitByIrn($record->irn);

                                $record->transmissions()->create([
                                    'action' => 'transmit',
                                    'response_payload' => $response,
                                    'status' => 'success',
                                ]);

                                $record->update(['transmit' => 'TRANSMITTING']);

                                Notification::make()
                                    ->title('Invoice is Transmitting')
                                    ->body("Invoice {$record->invoice_reference} has started transmitting to FIRS.")
                                    ->success()
                                    ->send();
                            } catch (Throwable $e) {
                                $record->transmissions()->create([
                                    'action' => 'transmit',
                                    'response_payload' => ['error' => $e->getMessage()],
                                    'status' => 'failed',
                                ]);

                                Notification::make()
                                    ->title('Transmission Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('Confirm')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->label('Confirm from FIRS')
                        ->action(function (Invoice $record) {
                            if (empty($record->irn)) {
                                Notification::make()
                                    ->title('IRN Missing')
                                    ->body('Cannot confirm this invoice. No valid IRN found.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            try {
                                $service = new TaxlyService($record->tenant->taxlyCredential);
                                $response = $service->confirmByIrn($record->irn);

                                $record->transmissions()->create([
                                    'action' => 'confirm',
                                    'response_payload' => $response,
                                    'status' => 'success',
                                ]);

                                Notification::make()
                                    ->title('Invoice Confirmation Successful')
                                    ->body("Invoice {$record->invoice_reference} was confirmed with FIRS.")
                                    ->success()
                                    ->send();
                            } catch (Throwable $e) {
                                Notification::make()
                                    ->title('Confirmation Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->label('Download XML/PDF')
                        ->action(function (Invoice $record) {
                            if (empty($record->irn)) {
                                Notification::make()
                                    ->title('IRN Missing')
                                    ->body('Cannot download FIRS file. No valid IRN found.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            try {
                                $service = new TaxlyService($record->tenant->taxlyCredential);
                                $service->downloadByIrn($record->irn);

                                Notification::make()
                                    ->title('Invoice Downloaded')
                                    ->body("FIRS document for {$record->invoice_reference} was downloaded successfully.")
                                    ->success()
                                    ->send();
                            } catch (Throwable $e) {
                                Notification::make()
                                    ->title('Download Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
