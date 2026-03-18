<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Chapters\Pages\EditChapter;
use App\Filament\Resources\Chapters\Pages\ListChapters;
use App\Models\Chapter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Chuong';

    protected static ?string $modelLabel = 'Chuong';

    protected static ?string $pluralModelLabel = 'Chuong';

    protected static string | \UnitEnum | null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->label('Mon hoc')
                ->relationship('subject', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('name')
                ->label('Ten chuong')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('order')
                ->label('Thu tu')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),

            Textarea::make('description')
                ->label('Mo ta')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.code')
                    ->label('Ma mon')
                    ->searchable(),

                TextColumn::make('subject.name')
                    ->label('Mon hoc')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Ten chuong')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Thu tu')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Mon hoc')
                    ->relationship('subject', 'name'),
            ])
            ->defaultSort('subject_id')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChapters::route('/'),
            'create' => CreateChapter::route('/create'),
            'edit' => EditChapter::route('/{record}/edit'),
        ];
    }
}
