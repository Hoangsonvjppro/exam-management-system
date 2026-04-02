<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages\ManageStudents as PagesManageStudents;
use App\Filament\Tables\StudentClassesTable;
use App\Models\Major;
use App\Models\StudentClass;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string | \UnitEnum | null $navigationGroup = 'Người dùng';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = "Sinh viên";

    protected static ?string $modelLabel = 'Sinh viên';

    protected static ?string $pluralModelLabel = 'Sinh viên';

    protected static ?string $slug = 'students';


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->students()
            ->with(['studentClass']);
    }
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Họ và tên')
                ->required()
                ->maxLength(255),

            TextInput::make('student_code')
                ->label('Mã số sinh viên')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),


            TextInput::make('phone')
                ->label('Số điện thoại')
                ->required()
                ->unique(ignoreRecord: true)
                ->regex('/^0[0-9]{9}$/')
                ->maxLength(10)
                ->validationMessages([
                    'regex' => ':Attribute có 10 số và bắt đầu bằng số 0'
                ]),

            Select::make('major_id')
                ->label('Ngành học')
                ->searchable()
                ->preload()
                ->required()
                ->options(fn () => Major::activeWithDepartment()->orderBy('name')->pluck('name', 'id')->toArray())
                ->getOptionLabelFromRecordUsing(
                    fn($record) => "{$record->code} - {$record->name}"
                )
                ->live()
                ->afterStateUpdated(fn($set) => $set('student_class_id', null)),

            TextInput::make('academic_year')
                ->label('Khóa nhập học (năm)')
                ->required()
                ->numeric()
                ->integer()
                ->minValue(2000)
                ->maxValue(now()->year + 1)
                ->placeholder('VD: 2023')
                ->afterStateHydrated(function (?Model $record, Set $set) {
                    if ($record && $record->studentClass) {
                        $set('academic_year', $record->studentClass->academic_year);
                    }
                })
                ->live(debounce: 100),

            DatePicker::make('date_of_birth')
                ->label('Ngày sinh')
                ->required()
                ->native(true)
                ->displayFormat('d/m/Y')
                ->maxDate(now()->subYears(15)),

            Select::make('student_class_id')
                ->label('Lớp')
                ->optionsLimit(15)
                ->options(function ($get): array {
                    $majorId = $get('major_id');
                    $academicYear = $get('academic_year');
                    if (! $majorId || ! $academicYear) return [];

                    return StudentClass::getClassesFromMajorAndAcademicYear((int) $majorId, (int) $academicYear)
                        ->get()
                        ->mapWithKeys(fn($c) => [$c->id => $c->code . ' - ' . $c->name])
                        ->toArray();
                })
                ->searchable()
                ->nullable()
                ->disabled(fn(Get $get) => ! $get('major_id') || ! $get('academic_year'))
                ->placeholder(
                    fn(Get $get) =>
                    ! $get('major_id') || ! $get('academic_year')
                        ? 'Vui lòng chọn ngành học và khóa nhập học'
                        : '- Chọn lớp -'
                )
                ->noOptionsMessage(
                    fn(Get $get) =>
                    ! $get('major_id')
                        ? 'Vui lòng chọn ngành học'
                        : (! $get('academic_year')
                            ? 'Vui lòng chọn khóa nhập học'
                            : 'Hiện không có lớp học')
                ),

            Toggle::make('is_active')
                ->label('Tài khoản hoạt động')
                ->default(true)
                ->onColor('success')
                ->offColor('danger')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_code')
                    ->label('MSSV')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('major.name')
                    ->label('Ngành')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('studentClass.name')
                    ->label('Lớp')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Khoa')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_of_birth')
                    ->label('Ngày sinh')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                IconColumn::make('password')
                    ->label('Có mật khẩu')
                    ->boolean()
                    ->getStateUsing(fn(User $record) => ! is_null($record->password))
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                // TextColumn::make('created_at')
                //     ->label('Ngày tạo')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('major_id')
                    ->label('Ngành học')
                    ->relationship('major', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('student_class_id')
                    ->label('Lớp')
                    ->relationship('studentClass', 'code')
                    ->searchable()
                    ->preload()
                    ->native(false),

                TernaryFilter::make('has_password')
                    ->label('Trạng thái mật khẩu')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã có mật khẩu')
                    ->falseLabel('Chưa có mật khẩu')
                    ->queries(
                        true: fn(Builder $q) => $q->whereNotNull('password'),
                        false: fn(Builder $q) => $q->whereNull('password'),
                    ),

                TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã khóa')
                    ->native(false),

                Filter::make('missing_dob')
                    ->label('Chưa có ngày sinh')
                    ->query(fn(Builder $q) => $q->whereNull('date_of_birth'))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn(User $r) => $r->is_active ? 'Khóa TK' : 'Mở TK')
                    ->icon(fn(User $r) => $r->is_active
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open')
                    ->color(fn(User $r) => $r->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn(User $r) => $r->is_active
                        ? "Khóa tài khoản {$r->name}?"
                        : "Mở khóa tài khoản {$r->name}?")
                    ->action(fn(User $r) => $r->update(['is_active' => ! $r->is_active])),
                EditAction::make(),
            ])
            ->defaultSort('student_code');
    }

    public static function getPages(): array
    {
        return [
            'index' => PagesManageStudents::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
