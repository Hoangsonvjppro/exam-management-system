<?php

namespace App\Filament\Resources\Semesters;

use App\Filament\Resources\Semesters\Pages\CreateSemester;
use App\Filament\Resources\Semesters\Pages\EditSemester;
use App\Filament\Resources\Semesters\Pages\ListSemesters;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\Semester;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SemesterResource extends Resource
{
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'semesters';
    }

    protected static ?string $model = Semester::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Học kỳ';

    protected static ?string $modelLabel = 'Học kỳ';

    protected static ?string $pluralModelLabel = 'Học kỳ';

    protected static string | \UnitEnum | null $navigationGroup = 'Đào tạo';

    protected static ?int $navigationSort = 1;

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
                ->afterOrEqual('start_date')
                ->helperText('Trạng thái current / upcoming / ended được hệ thống tự động tính theo ngày.'),
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
                    ->formatStateUsing(fn(int $state): string => match ($state) {
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

                TextColumn::make('lifecycle_status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Semester::STATUS_CURRENT => 'success',
                        Semester::STATUS_UPCOMING => 'warning',
                        Semester::STATUS_ENDED => 'gray',
                        Semester::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        Semester::STATUS_CURRENT => 'Đang diễn ra',
                        Semester::STATUS_UPCOMING => 'Sắp tới',
                        Semester::STATUS_ENDED => 'Đã qua',
                        Semester::STATUS_ARCHIVED => 'Lưu trữ',
                        default => $state,
                    })
                    ->alignCenter(),

                TextColumn::make('course_sections_count')
                    ->label('Số lớp HP')
                    ->counts('courseSections')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        Semester::STATUS_CURRENT => 'Đang diễn ra',
                        Semester::STATUS_UPCOMING => 'Sắp tới',
                        Semester::STATUS_ENDED => 'Đã qua',
                        Semester::STATUS_ARCHIVED => 'Lưu trữ',
                    ]),
                SelectFilter::make('term')
                    ->label('Học kỳ')
                    ->options([
                        1 => 'HK1',
                        2 => 'HK2',
                        3 => 'HK He',
                    ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn(Semester $record): bool => $record->courseSections()->exists())
                    ->tooltip(fn(Semester $record): ?string => $record->courseSections()->exists()
                        ? 'Không thể xóa học kỳ đã phát sinh lớp học phần.'
                        : null),
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
