<?php

namespace App\Filament\Resources\CourseSections;

use App\Filament\Resources\CourseSections\Pages\CreateCourseSection;
use App\Filament\Resources\CourseSections\Pages\EditCourseSection;
use App\Filament\Resources\CourseSections\Pages\ListCourseSections;
use App\Filament\Resources\CourseSections\Pages\ManageCourseSectionStudents;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Grid as Grid;

class CourseSectionResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'course-sections';
    }

    protected static ?string $model = CourseSection::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Lớp học phần';

    protected static ?string $modelLabel = 'Lớp học phần';

    protected static ?string $pluralModelLabel = 'Lớp học phần';

    protected static string | \UnitEnum | null $navigationGroup = 'Quản lý lớp';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components(static::getFormComponents());
    }

    public static function getFormComponents(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('code')
                    ->label('Mã lớp')
                    ->readOnly()
                    ->dehydrated()
                    ->unique(ignoreRecord: true)
                    ->helperText('Tự sinh theo môn học, nhóm, học kỳ và năm học'),

                TextInput::make('name')
                    ->label('Tên lớp học phần')
                    ->maxLength(255),

                Select::make('subject_id')
                    ->label('Môn học')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $set('code', static::generateCourseSectionCode(
                            $get('subject_id'),
                            $get('semester_id'),
                        ));
                    }),

                Select::make('semester_id')
                    ->label('Học kỳ')
                    ->relationship('semester', 'name')
                    ->modifyQueryUsing(fn(Builder $query): Builder => $query->openForCourseSectionCreation())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $set('code', static::generateCourseSectionCode(
                            $get('subject_id'),
                            $get('semester_id'),
                        ));
                    }),

                Select::make('lecturer_id')
                    ->label('Giảng viên')
                    ->relationship(
                        name: 'lecturer',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query): Builder => $query->role('lecturer')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('max_students')
                    ->label('Sĩ số tối đa')
                    ->numeric()
                    ->default(100)
                    ->minValue(1)
                    ->maxValue(999)
                    ->required(),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang mở',
                        'archived' => 'Lưu trữ',
                        'cancelled' => 'Hủy',
                    ])
                    ->default('active')
                    ->required(),

                // TextInput::make('invite_code')
                //     ->label('Mã mời vào lớp')
                //     ->maxLength(20)
                //     ->helperText('Để trống để hệ thống tự sinh')
                //     ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? strtoupper($state) : null),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã lớp')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge(),

                TextColumn::make('name')
                    ->label('Tên lớp')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('subject.name')
                    ->label('Môn học')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semester.name')
                    ->label('Học kỳ')
                    ->sortable(),

                TextColumn::make('lecturer.name')
                    ->label('Giảng viên')
                    ->searchable(),

                // TextColumn::make('max_students')
                //     ->label('Sĩ số')
                //     ->sortable(),
                TextColumn::make('enrolled_count')
                    ->label('Sĩ số')
                    ->getStateUsing(
                        fn(CourseSection $r) =>
                        "{$r->enrolled_count} / {$r->max_students}"
                    )
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'active'    => 'Đang mở',
                        'archived'  => 'Đã lưu trữ',
                        'cancelled' => 'Đã hủy',
                    })
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang mở',
                        'archived' => 'Lưu trữ',
                        'cancelled' => 'Hủy',
                    ]),
                SelectFilter::make('subject_id')
                    ->label('Môn học')
                    ->relationship('subject', 'name'),
                SelectFilter::make('semester_id')
                    ->label('Học kỳ')
                    ->relationship('semester', 'name'),
            ])
            ->recordUrl(fn(CourseSection $record): string => static::getUrl('students', [
                'record' => $record,
            ]))
            ->recordActions([
                ActionGroup::make([
                    Action::make('show_students')
                        ->label('Danh sách sinh viên')
                        ->icon('heroicon-m-users')
                        ->url(fn(CourseSection $record): string => static::getUrl('students', [
                            'record' => $record,
                        ])),
                    EditAction::make()->label('Sửa')->schema(static::getFormComponents()),
                    DeleteAction::make()->label('Xóa'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical'),
                // ->label(''),
            ]);
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseSections::route('/'),
            // 'create' => CreateCourseSection::route('/create'),
            // 'edit' => EditCourseSection::route('/{record}/edit'),
            'students' => ManageCourseSectionStudents::route('/{record}/students'),
        ];
    }

    public static function generateCourseSectionCode(null | int | string $subjectId, null | int | string $semesterId): ?string
    {
        if (blank($subjectId) || blank($semesterId)) {
            return null;
        }

        $subject = Subject::query()->find($subjectId);
        $semester = Semester::query()->find($semesterId);

        if (! $subject || ! $semester) {
            return null;
        }

        $groupNumber = static::resolveNextMissingGroupNumber((int) $subject->id, (int) $semester->id);

        $termCode = match ((int) $semester->term) {
            1 => 'HK1',
            2 => 'HK2',
            3 => 'HK3',
            default => 'HK' . (int) $semester->term,
        };

        $startYear = (int) $semester->year;
        $yearCode = sprintf('%02d%02d', $startYear % 100, ($startYear + 1) % 100);

        return strtoupper(sprintf(
            '%s-%02d-%s-%s',
            $subject->code,
            $groupNumber,
            $termCode,
            $yearCode,
        ));
    }

    protected static function resolveNextMissingGroupNumber(int $subjectId, int $semesterId): int
    {
        $used = CourseSection::query()
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->pluck('code')
            ->map(fn(string $code): ?int => static::extractGroupNumberFromCode($code))
            ->filter(fn(?int $n): bool => filled($n) && ($n > 0))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $expected = 1;

        foreach ($used as $n) {
            if ($n === $expected) {
                $expected++;

                continue;
            }

            if ($n > $expected) {
                break;
            }
        }

        return $expected;
    }

    protected static function extractGroupNumberFromCode(string $code): ?int
    {
        $parts = explode('-', $code);

        if (count($parts) < 2) {
            return null;
        }

        $group = $parts[1] ?? null;

        return is_numeric($group) ? (int) $group : null;
    }
}
