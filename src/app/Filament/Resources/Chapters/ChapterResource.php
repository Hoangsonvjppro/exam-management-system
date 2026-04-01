<?php

namespace App\Filament\Resources\Chapters;

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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

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
                ->required()
                ->columnSpanFull()
                ->validationMessages([
                    'required' => ':Attribute là bắt buộc'
                ]),

            TextInput::make('name')
                ->label('Tên chương')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('order')
                ->label('Thứ tự chương')
                ->numeric()
                ->integer()
                ->default(1)
                ->minValue(1)
                ->required()
                ->columnSpanFull()
                ->unique(
                    table: Chapter::class,
                    column: 'order',
                    ignoreRecord: true,
                    modifyRuleUsing: function (Unique $rule, Get $get) {
                        return $rule->where('subject_id', $get('subject_id'));
                    }
                )
                ->validationMessages([
                    'unique' => 'Chương đã tồn tại'
                ]),

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
                    ->sortable()
                    ->formatStateUsing(
                        fn($state, $record) =>
                        "Chương {$record->order}: {$state}"
                    ),

                TextColumn::make('order')
                    ->label('Thứ tự chương')
                    ->sortable(),
            ])
            ->defaultKeySort(false)
            ->groups([
                Group::make('subject.name')
                    ->label('Môn học')
                    ->collapsible(),
            ])
            ->defaultGroup('subject.name')
            ->groupingDirectionSettingHidden()
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Môn học')
                    ->relationship('subject', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                ->modalWidth(Width::Medium),
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
        ];
    }
}
