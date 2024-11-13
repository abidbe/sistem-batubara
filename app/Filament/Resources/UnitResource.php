<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Olah Unit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('unit_tipe')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('foto')
                    ->image()
                    ->directory('unit-images')
                    ->preserveFilenames()
                    ->visibility('public')
                    ->imageEditor()
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->maxSize(5120) // 5MB
                    ->columnSpanFull(),
                TextInput::make('tahun')
                    ->numeric()
                    ->required()
                    ->minValue(1900)
                    ->maxValue(date('Y')),
                TextInput::make('hm')
                    ->numeric()
                    ->label('HM')
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(999999999999999999),
                TextInput::make('kondisi')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Textarea::make('keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_tipe')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('hm')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tahun')
                    ->sortable(),
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
            ->poll();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        ImageEntry::make('foto')
                            ->disk('public')
                            ->url(fn($record) => asset('storage/' . $record->foto))
                            ->openUrlInNewTab()
                            ->extraAttributes([
                                'style' => '
                                display: block;
                                max-width: 800px; 
                                max-height: 500px;
                                width: 100%;
                                height: auto;
                                object-fit: contain;
                                margin: 0 auto;
                                border-radius: 8px;
                                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                                cursor: pointer;
                            ',
                                'target' => '_blank',
                                'rel' => 'noopener noreferrer'
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(false),
                Section::make('Informasi Unit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextEntry::make('unit_tipe')
                                        ->label('Tipe Unit')
                                        ->inlineLabel(),

                                    TextEntry::make('tahun')
                                        ->label('Tahun')
                                        ->inlineLabel(),
                                ]),
                                Group::make([
                                    TextEntry::make('nama')
                                        ->label('Nama Unit')
                                        ->inlineLabel(),
                                    TextEntry::make('kondisi')
                                        ->label('Kondisi')
                                        ->inlineLabel(),
                                    TextEntry::make('hm')
                                        ->label('HM')
                                        ->inlineLabel()
                                ]),
                            ]),

                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->inlineLabel()
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsed(false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
        ];
    }
    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
