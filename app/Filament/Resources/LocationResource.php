<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use App\Models\Town;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Taxonomy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('town_id')
                    ->label('Town')
                    ->options(fn () => Town::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
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
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                        // We enforce uniqueness via DB unique(town_id, slug); keep basic validation here.
                        return $rule;
                    }),

                Forms\Components\TextInput::make('timezone')
                    ->required()
                    ->default('America/Cancun')
                    ->helperText('IANA timezone, e.g. America/Cancun'),

                Forms\Components\TextInput::make('address')
                    ->maxLength(255),

                Forms\Components\TextInput::make('lat')
                    ->numeric()
                    ->helperText('Optional'),
                Forms\Components\TextInput::make('lng')
                    ->numeric()
                    ->helperText('Optional'),

                Forms\Components\Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('town.name')->label('Town')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('timezone')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->since()->sortable(),
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
