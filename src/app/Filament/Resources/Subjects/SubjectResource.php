<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\CreateSubject;
use App\Filament\Resources\Subjects\Pages\EditSubject;
use App\Filament\Resources\Subjects\Pages\ListSubjects;
use App\Models\Subject;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Môn học';

    protected static ?string $modelLabel = 'Môn học';

    protected static ?string $pluralModelLabel = 'Môn học';

    protected static string | \UnitEnum | null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Mã môn học')
                ->required()
                ->maxLength(20)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->label('Tên môn học')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('credits')
                ->label('Số tín chỉ')
                ->numeric()
                ->minValue(1)
                ->maxValue(10)
                ->required(),

            TextInput::make('department')
                ->label('Khoa phụ trách')
                ->maxLength(255),

            Textarea::make('description')
                ->label('Mô tả môn học')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã môn học')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Tên môn học')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('credits')
                    ->label('Số tín chỉ')
                    ->sortable(),

                TextColumn::make('department')
                    ->label('Khoa')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('credits')
                    ->label('Số tín chỉ')
                    ->options(fn (): array => Subject::query()
                        ->select('credits')
                        ->distinct()
                        ->orderBy('credits')
                        ->pluck('credits', 'credits')
                        ->toArray()),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubjects::route('/'),
            'create' => CreateSubject::route('/create'),
            'edit' => EditSubject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
