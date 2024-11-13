<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MinyakResource\Pages;
use App\Models\Minyak;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

class MinyakResource extends Resource
{
    protected static ?string $model = Minyak::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Olah Minyak';

    public static function form(Form $form): Form
    {
        // Hitung stock untuk preview
        $data = Minyak::orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningStock = 0;
        $stockMap = [];

        foreach ($data as $item) {
            $runningStock += ($item->masuk - $item->keluar);
            $stockMap[$item->id] = $runningStock;
        }

        return $form
            ->schema([
                Select::make('unit_id')
                    ->relationship('unit', 'nama')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->unit_tipe} - {$record->nama}")
                    ->searchable()
                    ->preload()
                    ->label('Unit')
                    ->nullable(),
                DatePicker::make('tanggal')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
                TextInput::make('masuk')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(debounce: 500) // Tambahkan debounce 500ms
                    ->afterStateUpdated(function ($state, $get, $set) use ($stockMap) {
                        if (floatval($state) > 0) {
                            $set('keluar', 0);
                        }

                        // Jika sedang edit, ambil stock sebelumnya
                        if ($get('id')) {
                            $previousStock = $stockMap[$get('id')] ?? 0;
                            $newStock = $previousStock + floatval($state) - floatval($get('old_masuk') ?? 0);
                            $set('stock_preview', $newStock);
                        } else {
                            // Jika tambah baru
                            $lastStock = end($stockMap) ?: 0;
                            $set('stock_preview', $lastStock + floatval($state));
                        }
                    })
                    ->label('Masuk'),
                TextInput::make('keluar')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(debounce: 500) // Tambahkan debounce 500ms
                    ->afterStateUpdated(function ($state, $get, $set) use ($stockMap) {
                        if (floatval($state) > 0) {
                            $set('masuk', 0);
                        }

                        // Jika sedang edit, ambil stock sebelumnya
                        if ($get('id')) {
                            $previousStock = $stockMap[$get('id')] ?? 0;
                            $newStock = $previousStock - floatval($state) + floatval($get('old_keluar') ?? 0);
                            $set('stock_preview', $newStock);
                        } else {
                            // Jika tambah baru
                            $lastStock = end($stockMap) ?: 0;
                            $set('stock_preview', $lastStock - floatval($state));
                        }
                    })
                    ->label('Keluar'),
                TextInput::make('stock_preview')
                    ->label('Stock Saat Ini')
                    ->disabled()
                    ->default(function () use ($stockMap) {
                        return end($stockMap) ?: 0;
                    })
                    ->dehydrated(false),
                TextInput::make('nama_pengguna')
                    ->maxLength(255)
                    ->label('Nama Pengguna'),
                Textarea::make('keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Keterangan'),
                // Hidden fields untuk menyimpan nilai lama saat edit
                TextInput::make('old_masuk')
                    ->hidden()
                    ->default(fn($record) => $record?->masuk)
                    ->dehydrated(false),
                TextInput::make('old_keluar')
                    ->hidden()
                    ->default(fn($record) => $record?->keluar)
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Ambil semua data dan hitung stock secara berurutan
        $data = Minyak::orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningStock = 0;
        $stockMap = [];

        foreach ($data as $item) {
            $runningStock += ($item->masuk - $item->keluar);
            $stockMap[$item->id] = $runningStock;
        }

        // Hitung total
        $totalPemakaian = Minyak::sum('keluar');
        $totalMasuk = Minyak::sum('masuk');
        $totalStock = $totalMasuk - $totalPemakaian;

        return $table
            ->columns([
                TextColumn::make('unit.nama')
                    ->sortable()
                    ->searchable()
                    ->label('Unit'),
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable()
                    ->label('Tanggal'),
                TextColumn::make('masuk')
                    ->numeric()
                    ->sortable()
                    ->label('Masuk'),
                TextColumn::make('keluar')
                    ->numeric()
                    ->sortable()
                    ->label('Keluar'),
                TextColumn::make('id')
                    ->label('Stock')
                    ->formatStateUsing(fn($record) => $stockMap[$record->id] ?? 0)
                    ->alignEnd(),
                TextColumn::make('nama_pengguna')
                    ->searchable()
                    ->label('Nama Pengguna'),
            ])
            ->contentFooter(view('minyak-stats', [
                'totalPemakaian' => $totalPemakaian,
                'totalMasuk' => $totalMasuk,
                'totalStock' => $totalStock,
            ]))
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
            ->poll();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $stockMap = [];
        $runningStock = 0;

        // Hitung stock sampai record saat ini
        Minyak::orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->each(function ($item) use (&$runningStock, &$stockMap) {
                $runningStock += ($item->masuk - $item->keluar);
                $stockMap[$item->id] = $runningStock;
            });

        return $infolist
            ->schema([
                Section::make('Informasi Transaksi Minyak')
                    ->schema([
                        TextEntry::make('unit.nama')
                            ->label('Unit')
                            ->inlineLabel()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->date()
                            ->inlineLabel()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('masuk')
                            ->label('Masuk')
                            ->inlineLabel()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('keluar')
                            ->label('Keluar')
                            ->inlineLabel()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('id')
                            ->label('Stock')
                            ->inlineLabel()
                            ->formatStateUsing(fn($record) => $stockMap[$record->id] ?? 0)
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('nama_pengguna')
                            ->label('Nama Pengguna')
                            ->inlineLabel()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->inlineLabel()
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(false),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMinyaks::route('/'),
        ];
    }
    public static function getNavigationSort(): ?int
    {
        return 2; // akan muncul pertama dalam groupnya
    }
}
