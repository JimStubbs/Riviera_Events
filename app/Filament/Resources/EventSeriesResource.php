<?php

namespace App\Filament\Resources;

use App\Enums\EventSeriesStatus;
use App\Filament\Resources\EventSeriesResource\Pages;
use App\Models\Category;
use App\Models\EventSeries;
use App\Models\Location;
use App\Models\Town;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventSeriesResource extends Resource
{
    protected static ?string $model = EventSeries::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Events';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basics')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->helperText('Unique per town.')
                            ->disabled(fn (?EventSeries $record) => $record?->exists && $record?->status === EventSeriesStatus::Approved->value)
                            ->dehydrated(),

                        Forms\Components\Select::make('town_id')
                            ->options(fn () => Town::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('location_id')
                            ->options(function (Forms\Get $get) {
                                $townId = $get('town_id');
                                if (!$townId) {
                                    return Location::query()->orderBy('name')->pluck('name', 'id');
                                }

                                return Location::query()->where('town_id', $townId)->orderBy('name')->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('category_id')
                            ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable(),

                        Forms\Components\Toggle::make('is_all_day')->default(false),

                        Forms\Components\TextInput::make('timezone')
                            ->required()
                            ->helperText('Will be copied from the selected location at create time. For now editable.')
                            ->default('America/Cancun'),
                    ]),

                Forms\Components\Section::make('Schedule')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at_local')
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends_at_local')
                            ->required(),
                        Forms\Components\TextInput::make('rrule')
                            ->label('RRULE')
                            ->helperText('RFC 5545 RRULE string. UI builder comes next.')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('until_local')
                            ->label('Recurring until')
                            ->helperText('Optional end for recurring series.'),
                        Forms\Components\TextInput::make('count')
                            ->numeric()
                            ->helperText('Optional number of occurrences.'),
                        Forms\Components\Textarea::make('exdates')
                            ->helperText('JSON array of local dates to skip (MVP).'),
                    ]),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Textarea::make('description')->rows(6),
                        Forms\Components\TextInput::make('image_url')->url(),
                    ]),

                Forms\Components\Section::make('Monetization & Workflow')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_premium')->default(false),
                        Forms\Components\TextInput::make('premium_price_mxn')
                            ->numeric()
                            ->default(200)
                            ->helperText('Initial flat fee is 200 MXN (applies to entire series).'),
                        Forms\Components\DateTimePicker::make('premium_paid_at'),

                        Forms\Components\Select::make('status')
                            ->options(collect(EventSeriesStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->value])->all())
                            ->required(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_premium')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('town.name')->label('Town')->sortable(),
                Tables\Columns\TextColumn::make('location.name')->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('starts_at_local')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at_local')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->since()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(EventSeriesStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->value])->all()),
                Tables\Filters\TernaryFilter::make('is_premium'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventSeries::route('/'),
            'create' => Pages\CreateEventSeries::route('/create'),
            'edit' => Pages\EditEventSeries::route('/{record}/edit'),
        ];
    }
}
