<?php

namespace App\Filament\Resources\Lecturers;

use App\Filament\Resources\Lecturers\Pages\ManageLecturers;
use App\Filament\Resources\Lecturers\Pages\ManageLecturerSubjects;
use App\Filament\Resources\Lecturers\Subjects\SubjectTable;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class LecturersResource extends Resource
{
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'lecturers';
    }
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string | \UnitEnum | null $navigationGroup = 'Người dùng';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Giảng viên';

    protected static ?string $pluralModelLabel = 'Giảng viên';

    protected static ?string $modelLabel = 'Giảng viên';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->lecturers();
    }

    public static function canBlockLecturer(): bool
    {
        return static::canForAction('block');
    }

    public static function canAssignLecturerSubject(): bool
    {
        return static::canForAction('assign');
    }

    public static function canImportLecturer(): bool
    {
        return static::canForAction('import');
    }

    public static function canEdit($record): bool
    {
        return static::canForAction('update') || static::canForAction('edit');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Họ tên')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull()
                    ->regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
                    ->validationMessages([
                        'unique' => ':Attribute đã tồn tại',
                        'regex' => ':Attribute không đúng định dạng'
                    ]),

                TextInput::make('lecturer_code')
                    ->label('Mã giảng viên')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->regex('/GV_\d{3}/')
                    ->validationMessages([
                        'unique' => ':Attribute đã tồn tại',
                        'regex' => ':Attribute phải có dạng GV_xxx, trong đó x là chữ số'
                    ]),

                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->unique(ignoreRecord: true)
                    ->regex('/0\d{9}/')
                    ->validationMessages([
                        'unique' => ':Attribute đã tồn tại',
                        'regex' => ':Attribute phải bắt đầu bằng 0 và có 10 chữ số'
                    ]),

                DatePicker::make('date_of_birth')
                    ->label('Ngày sinh')
                    ->required()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set(
                            'password_preview',
                            $state
                                ? Carbon::parse($state)->format('dmY')
                                : 'Chưa chọn ngày sinh'
                        );
                    })
                    ->native(true)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->maxDate(now())
                    ->after('1900-01-01')
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc',
                        'after' => ':Attribute không hợp lệ'
                    ]),

                TextInput::make('password_preview')
                    ->label('Mật khẩu mặc định')
                    ->disabled()
                    ->live()
                    ->dehydrated(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lecturer_code')
                    ->label('Mã giảng viên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_of_birth')
                    ->label('Ngày sinh')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->alignBetween(),
            ])
            ->paginated([10, 25, 50, 'all'])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->defaultSort('lecturer_code')
            ->filters([
                Filter::make('active')
                    ->label('Đang hoạt động')
                    ->query(fn(Builder $query) => $query->where('is_active', true)),
                Filter::make('inactive')
                    ->label('Đã khóa')
                    ->query(fn(Builder $query) => $query->where('is_active', false)),
            ])
            ->recordActions([
                Action::make('lock')
                    ->label('Khóa')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->authorize(fn(): bool => static::canBlockLecturer())
                    ->visible(fn($record): bool => static::canBlockLecturer() && $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Khóa tài khoản')
                    ->modalDescription('Bạn chắc chắn muốn KHÓA tài khoản này? Giảng viên sẽ không thể đăng nhập.')
                    ->modalSubmitActionLabel('Xác nhận khóa')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalIconColor('danger')
                    ->successNotificationTitle('Đã khóa tài khoản')
                    ->action(fn($record) => $record->update(['is_active' => false])),

                Action::make('unlock')
                    ->label('Mở khóa')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->authorize(fn(): bool => static::canBlockLecturer())
                    ->visible(fn($record): bool => static::canBlockLecturer() && ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Mở khóa tài khoản')
                    ->modalDescription('Tài khoản sẽ được kích hoạt lại và có thể đăng nhập bình thường.')
                    ->modalSubmitActionLabel('Xác nhận mở khóa')
                    ->modalIcon('heroicon-o-lock-open')
                    ->modalIconColor('success')
                    ->successNotificationTitle('Đã mở khóa tài khoản')
                    ->action(fn($record) => $record->update(['is_active' => true])),
                EditAction::make()
                    ->label('Edit')
                    ->authorize(fn($record): bool => static::canEdit($record)),
                Action::make('assign')
                    ->label('Phân công')
                    ->icon('heroicon-o-book-open')
                    ->authorize(fn(): bool => static::canAssignLecturerSubject())
                    ->visible(fn(): bool => static::canAssignLecturerSubject())
                    ->schema([
                        CheckboxList::make('subjects')
                            ->label('Môn học')
                            ->relationship('subjects', 'name')
                            ->searchable()
                            ->columns(2)
                            ->bulkToggleable()
                    ])
                    ->fillForm(function ($record) {
                        return [
                            'subjects' => $record->subjects->pluck('id')->toArray()
                        ];
                    })
                    ->action(function (array $data, $livewire) {
                        $lecturerId = $livewire->data['lecturers'] ?? null;

                        if ($lecturerId) {
                            $lecturer = User::find($lecturerId);

                            $lecturer->subjects()->sync($data['subjects'] ?? []);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('lock')
                        ->label('Khóa hàng loạt')
                        ->icon('heroicon-o-lock-closed')
                        ->authorize(fn(): bool => static::canBlockLecturer())
                        ->visible(fn(): bool => static::canBlockLecturer())
                        ->requiresConfirmation()
                        ->modalHeading('Khóa nhiều tài khoản')
                        ->modalDescription('Tất cả giảng viên được chọn sẽ bị khóa.')
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalIconColor('danger')
                        ->successNotificationTitle('Đã khóa thành công các tài khoản')
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unlock')
                        ->label('Mở khóa hàng loạt')
                        ->icon('heroicon-o-lock-open')
                        ->authorize(fn(): bool => static::canBlockLecturer())
                        ->visible(fn(): bool => static::canBlockLecturer())
                        ->requiresConfirmation()
                        ->modalHeading('Mở khóa nhiều tài khoản')
                        ->modalDescription('Các tài khoản sẽ hoạt động lại bình thường.')
                        ->modalIcon('heroicon-o-check-circle')
                        ->modalIconColor('success')
                        ->successNotificationTitle('Đã mở khóa thành công các tài khoản')
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLecturers::route('/'),
            'subjects' => ManageLecturerSubjects::route('/{record}/subjects'),
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
