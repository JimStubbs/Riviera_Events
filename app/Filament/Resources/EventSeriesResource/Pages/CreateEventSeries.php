<?php

namespace App\Filament\Resources\EventSeriesResource\Pages;

use App\Enums\EventSeriesStatus;
use App\Filament\Resources\EventSeriesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventSeries extends CreateRecord
{
    protected static string $resource = EventSeriesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $data['status'] ?? EventSeriesStatus::Draft->value;

        // For now: start draft_data/published_data empty. Workflow implementation PR will set these.
        return $data;
    }
}
