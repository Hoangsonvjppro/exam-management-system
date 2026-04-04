<?php

namespace App\Filament\Resources\Semesters;

use App\Filament\Resources\Semesters\Pages\CreateSemester;
use App\Filament\Resources\Semesters\Pages\EditSemester;
use App\Filament\Resources\Semesters\Pages\ListSemesters;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\Semester;
use App\Services\SemesterGovernanceService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

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
                ->placeholder('VD: HK1 2026-2027')
                ->helperText('Nếu ô tên đang để trống, hệ thống sẽ tự gợi ý khi bạn chọn năm học và học kỳ.')
                ->validationMessages([
                    'required' => 'Vui lòng nhập tên học kỳ.',
                ])
                ->columnSpanFull(),

            Select::make('year')
                ->label('Năm học bắt đầu')
                ->options(static::yearOptions())
                ->default(now()->year)
                ->searchable()
                ->native(false)
                ->live()
                ->required()
                ->validationMessages([
                    'required' => 'Vui lòng chọn năm học bắt đầu.',
                ])
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if (! filled($state)) {
                        return;
                    }

                    if (blank($get('name')) && filled($get('term'))) {
                        $set('name', static::suggestSemesterName((int) $state, (int) $get('term')));
                    }

                    if (! filled($get('start_date'))) {
                        return;
                    }

                    $startYear = Carbon::parse((string) $get('start_date'))->year;

                    if ((int) $state !== $startYear) {
                        $set('start_date', null);
                        $set('end_date', null);

                        Notification::make()
                            ->title('Đã thay đổi năm học')
                            ->body('Ngày bắt đầu/kết thúc đã được xóa để bạn chọn lại theo năm mới.')
                            ->warning()
                            ->send();
                    }
                }),

            Select::make('term')
                ->label('Học kỳ')
                ->options([
                    1 => 'HK1',
                    2 => 'HK2',
                    3 => 'HK3',
                ])
                ->default(static::suggestCurrentTerm())
                ->live()
                ->required()
                ->validationMessages([
                    'required' => 'Vui lòng chọn học kỳ.',
                ])
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if (! filled($state) || ! filled($get('year')) || ! blank($get('name'))) {
                        return;
                    }

                    $set('name', static::suggestSemesterName((int) $get('year'), (int) $state));
                }),

            DatePicker::make('start_date')
                ->label('Ngày bắt đầu')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->live()
                ->minDate(fn(Get $get): ?Carbon => filled($get('year'))
                    ? Carbon::create((int) $get('year'), 1, 1)->startOfDay()
                    : null)
                ->maxDate(fn(Get $get): ?Carbon => filled($get('year'))
                    ? Carbon::create((int) $get('year'), 12, 31)->endOfDay()
                    : null)
                ->required()
                ->helperText('Để tạo học kỳ hiện tại, hãy chọn khoảng ngày có chứa ngày hôm nay.')
                ->validationMessages([
                    'required' => 'Vui lòng chọn ngày bắt đầu.',
                    'date' => 'Ngày bắt đầu không đúng định dạng.',
                ])
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if (! filled($state) || ! filled($get('end_date'))) {
                        return;
                    }

                    $startDate = Carbon::parse((string) $state)->startOfDay();
                    $endDate = Carbon::parse((string) $get('end_date'))->endOfDay();

                    if ($endDate->lt($startDate)) {
                        $set('end_date', null);

                        Notification::make()
                            ->title('Ngày kết thúc chưa hợp lệ')
                            ->body('Ngày kết thúc phải sau hoặc trùng ngày bắt đầu. Vui lòng chọn lại.')
                            ->warning()
                            ->send();
                    }
                }),

            DatePicker::make('end_date')
                ->label('Ngày kết thúc')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->live()
                ->minDate(fn(Get $get): ?Carbon => filled($get('start_date'))
                    ? Carbon::parse((string) $get('start_date'))->startOfDay()
                    : (filled($get('year')) ? Carbon::create((int) $get('year'), 1, 1)->startOfDay() : null))
                ->maxDate(fn(Get $get): ?Carbon => filled($get('year'))
                    ? Carbon::create((int) $get('year') + 1, 12, 31)->endOfDay()
                    : null)
                ->required()
                ->afterOrEqual('start_date')
                ->validationMessages([
                    'required' => 'Vui lòng chọn ngày kết thúc.',
                    'date' => 'Ngày kết thúc không đúng định dạng.',
                    'after_or_equal' => 'Ngày kết thúc phải sau hoặc trùng ngày bắt đầu.',
                ])
                ->helperText('Trạng thái current / upcoming / ended được hệ thống tự động tính theo ngày.'),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected static function yearOptions(): array
    {
        $startYear = now()->year;
        $endYear = $startYear + 10;

        return collect(range($startYear, $endYear))
            ->mapWithKeys(fn(int $year): array => [$year => (string) $year])
            ->all();
    }

    protected static function suggestCurrentTerm(): int
    {
        $month = now()->month;

        return match (true) {
            $month >= 9 => 1,
            $month <= 5 => 2,
            default => 3,
        };
    }

    protected static function suggestSemesterName(int $year, int $term): string
    {
        $termLabel = match ($term) {
            1 => 'HK1',
            2 => 'HK2',
            3 => 'HK3',
            default => 'HK' . $term,
        };

        return sprintf('%s %d-%d', $termLabel, $year, $year + 1);
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
                        3 => 'HK3',
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
                        3 => 'HK3',
                    ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                Action::make('archive')
                    ->label('Lưu trữ')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Lưu trữ học kỳ')
                    ->modalDescription('Chỉ lưu trữ khi học kỳ đã kết thúc và không còn ca thi đang mở.')
                    ->visible(fn(Semester $record): bool => $record->status !== Semester::STATUS_ARCHIVED)
                    ->action(function (Semester $record): void {
                        try {
                            app(SemesterGovernanceService::class)->archiveSemester($record);

                            Notification::make()
                                ->title('Đã lưu trữ học kỳ')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            $message = (string) (collect($exception->errors())->flatten()->first()
                                ?? 'Không thể lưu trữ học kỳ ở thời điểm hiện tại.');

                            Notification::make()
                                ->title('Không thể lưu trữ học kỳ')
                                ->body($message)
                                ->danger()
                                ->send();
                        }
                    }),
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
            // 'create' => CreateSemester::route('/create'),
            // 'edit' => EditSemester::route('/{record}/edit'),
        ];
    }
}
