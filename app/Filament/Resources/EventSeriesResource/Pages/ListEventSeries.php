<?php

namespace App\Filament\Resources\EventSeriesResource\Pages;

use App\Enums\EventSeriesStatus;
use App\Filament\Resources\EventSeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventSeries extends ListRecords
{
    protected static string $resource = EventSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('pendingReview')
                ->label('Pending review')
                ->url(fn () => static::getResource()::getUrl('index', ['tableFilters[status][value]' => EventSeriesStatus::PendingReview->value])),
        ];
    }
}
