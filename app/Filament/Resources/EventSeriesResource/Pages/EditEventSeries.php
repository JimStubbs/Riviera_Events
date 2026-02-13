<?php

namespace App\Filament\Resources\EventSeriesResource\Pages;

use App\Enums\EventSeriesStatus;
use App\Filament\Resources\EventSeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventSeries extends EditRecord
{
    protected static string $resource = EventSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->visible(fn () => $this->record->status === EventSeriesStatus::PendingReview->value)
                ->requiresConfirmation()
                ->action(function () {
                    // Placeholder: next PR will implement published/draft snapshot workflow.
                    $this->record->status = EventSeriesStatus::Approved->value;
                    $this->record->last_approved_at = now();
                    $this->record->save();

                    $this->refreshFormData(['status', 'last_approved_at']);
                }),

            Actions\Action::make('reject')
                ->visible(fn () => $this->record->status === EventSeriesStatus::PendingReview->value)
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->status = EventSeriesStatus::Rejected->value;
                    $this->record->rejection_reason = $data['rejection_reason'];
                    $this->record->save();

                    $this->refreshFormData(['status', 'rejection_reason']);
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
