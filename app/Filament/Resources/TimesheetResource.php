<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimesheetResource\Pages;
use App\Models\Timesheet;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimesheetResource extends Resource
{
    protected static ?string $model = Timesheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Olah Timesheet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('unit_id')
                    ->relationship('unit', 'nama')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->unit_tipe} - {$record->nama}")
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            // Ambil unit yang dipilih
                            $unit = \App\Models\Unit::find($state);
                            if ($unit) {
                                $set('hm_awal', $unit->hm);
                                $set('hm_akhir', $unit->hm); // Set nilai awal hm_akhir sama dengan hm_awal
                            }
                        }
                    }),
                DatePicker::make('tanggal')
                    ->required()
                    ->default(now()),
                TextInput::make('jam_kerja')
                    ->numeric()
                    ->required()
                    ->columnSpanFull()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $hm_awal = floatval($get('hm_awal') ?? 0);
                        $jam_kerja = floatval($state ?? 0);
                        $set('hm_akhir', $hm_awal + $jam_kerja);
                    }),
                TextInput::make('hm_awal')
                    ->label('HM Awal')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                TextInput::make('hm_akhir')
                    ->label('HM Akhir')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Textarea::make('keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit.nama')
                    ->label('Unit')
                    ->formatStateUsing(fn($record) => "{$record->unit->unit_tipe} - {$record->unit->nama}")
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('hm_awal')
                    ->label('HM Awal')
                    ->numeric(2), // Langsung menggunakan value dari model
                TextColumn::make('hm_akhir')
                    ->label('HM Akhir')
                    ->numeric(2), // Langsung menggunakan value dari model
                TextColumn::make('jam_kerja')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalWidth('xl')
                        ->color('info'),
                    EditAction::make()
                        ->modalWidth('lg'),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50, 100])
            ->modifyQueryUsing(fn(Builder $query) => $query)
            ->poll();;
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
            'index' => Pages\ListTimesheets::route('/'),
        ];
    }
    public static function getNavigationSort(): ?int
    {
        return 3;
    }
}
