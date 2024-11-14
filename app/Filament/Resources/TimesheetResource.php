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
use Carbon\Carbon;

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
                    ->numeric(2),
                TextColumn::make('hm_akhir')
                    ->label('HM Akhir')
                    ->numeric(2),
                TextColumn::make('jam_kerja')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('timesheet_filter')
                    ->form([
                        Select::make('unit_id')
                            ->label('Unit')
                            ->relationship('unit', 'nama')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->unit_tipe} - {$record->nama}")
                            ->required(),
                        Select::make('month')
                            ->label('Bulan & Tahun')
                            ->options(function () {
                                $months = [];
                                for ($i = -6; $i <= 6; $i++) {
                                    $date = now()->addMonths($i)->startOfMonth();
                                    $key = $date->format('Y-m');
                                    $label = $date->isoFormat('MMMM Y');
                                    $months[$key] = $label;
                                }
                                return $months;
                            })
                            ->required()
                            ->default(now()->format('Y-m')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['unit_id'],
                                fn(Builder $query, $unit_id): Builder => $query->where('unit_id', $unit_id)
                            )
                            ->when(
                                $data['month'],
                                fn(Builder $query, $month): Builder => $query->whereMonth('tanggal', Carbon::parse($month)->month)
                                    ->whereYear('tanggal', Carbon::parse($month)->year)
                            );
                    }),
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
            ->modifyQueryUsing(function (Builder $query) {
                $data = json_decode(request()->input('components.0.snapshot'), true);

                if (!$data) {
                    $query->whereRaw('1 = 0');
                    return $query;
                }

                $tableFilters = $data['data']['tableFilters'] ?? [];

                if (!empty($tableFilters) && isset($tableFilters[0]['timesheet_filter'][0])) {
                    $filter = $tableFilters[0]['timesheet_filter'][0];
                    if (!empty($filter['unit_id']) && !empty($filter['month'])) {
                        return $query;
                    }
                }

                $query->whereRaw('1 = 0');
                return $query;
            })
            ->emptyStateHeading('Pilih filter terlebih dahulu')
            ->emptyStateDescription('Silakan pilih Unit dan Periode untuk menampilkan data timesheet.')
            ->emptyStateIcon('heroicon-o-funnel')
            ->poll()
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter Data'),
            );
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
