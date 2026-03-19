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

    protected static ?string $navigationLabel = 'Chương';

    protected static ?string $modelLabel = 'Chương';

    protected static ?string $pluralModelLabel = 'Chương';

    protected static string | \UnitEnum | null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->label('Môn học')
                ->relationship('subject', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('name')
                ->label('Tên chương')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('order')
                ->label('Thứ tự')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),

            Textarea::make('description')
                ->label('Mô tả')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.code')
                    ->label('Mã môn học')
                    ->searchable(),

                TextColumn::make('subject.name')
                    ->label('Môn học')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Tên chương')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Thứ tự')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Môn học')
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
