<?php

namespace App\Filament\Resources\Semesters;

use App\Filament\Resources\Semesters\Pages\CreateSemester;
use App\Filament\Resources\Semesters\Pages\EditSemester;
use App\Filament\Resources\Semesters\Pages\ListSemesters;
use App\Models\Semester;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Học kỳ';

    protected static ?string $modelLabel = 'Học kỳ';

    protected static ?string $pluralModelLabel = 'Học kỳ';

    protected static string | \UnitEnum | null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Tên học kỳ')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),

            TextInput::make('year')
                ->label('Năm học bắt đầu')
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100)
                ->required(),

            Select::make('term')
                ->label('Học kỳ')
                ->options([
                    1 => 'HK1',
                    2 => 'HK2',
                    3 => 'HK He',
                ])
                ->required(),

            DatePicker::make('start_date')
                ->label('Ngày bắt đầu')
                ->native(false)
                ->required(),

            DatePicker::make('end_date')
                ->label('Ngày kết thúc')
                ->native(false)
                ->required()
                ->afterOrEqual('start_date'),

            Toggle::make('is_current')
                ->label('Học kỳ hiện tại')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên học kỳ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Năm học')
                    ->sortable(),

                TextColumn::make('term')
                    ->label('Học kỳ')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'HK1',
                        2 => 'HK2',
                        3 => 'HK He',
                        default => (string) $state,
                    }),

                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_current')
                    ->label('Học kỳ hiện tại')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('term')
                    ->label('Học kỳ')
                    ->options([
                        1 => 'HK1',
                        2 => 'HK2',
                        3 => 'HK He',
                    ]),
            ])
            ->defaultSort('year', 'desc')
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
            'index' => ListSemesters::route('/'),
            'create' => CreateSemester::route('/create'),
            'edit' => EditSemester::route('/{record}/edit'),
        ];
    }
}
