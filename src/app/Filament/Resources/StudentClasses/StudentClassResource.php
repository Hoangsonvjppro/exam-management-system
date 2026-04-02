<?php

namespace App\Filament\Resources\StudentClasses;

use App\Filament\Resources\StudentClasses\Pages\ManageStudentClasses;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\Major;
use App\Models\StudentClass;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StudentClassResource extends Resource
{
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'student-classes';
    }

    protected static ?string $model = StudentClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Lớp sinh viên';

    protected static string | \UnitEnum | null $navigationGroup = 'Quản lý lớp';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Lớp';

    protected static ?string $pluralModelLabel = 'Lớp sinh viên';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên lớp')
                    ->readOnly()
                    ->dehydrated()
                    ->columnSpanFull()
                    ->helperText("Tên lớp sẽ được tự động sinh khi có ngành học, năm nhập học và nhóm lớp"),

                TextInput::make('code')
                    ->label('Mã lớp')
                    ->required()
                    ->regex('/^[A-Z]{3}[0-9]{4}$/')
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->placeholder('VD: DKP1235')
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc',
                        'unique' => ':Attribute đã tồn tại',
                        'regex' => ':Attribute phải có dạng DKP1235',
                    ]),

                Select::make('major_id')
                    ->label('Ngành học')
                    ->relationship('major', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('majors', 'id')
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $majorName = Major::find($get('major_id'))?->name;
                        $set('major_name', $majorName);
                        $set('name', self::generateName(
                            $majorName,
                            $get('academic_year'),
                            $get('class_group')
                        ));
                    })
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc',
                    ]),

                TextInput::make('academic_year')
                    ->label('Khóa nhập học (năm)')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(2000)
                    ->maxValue(now()->year + 1)
                    ->afterStateUpdatedJs(self::jsGenerateName())
                    ->placeholder('VD: 2023'),

                TextInput::make('class_group')
                    ->label('Nhóm lớp')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(1)
                    ->maxValue(99)
                    ->default(1)
                    ->afterStateUpdatedJs(self::jsGenerateName())
                    ->placeholder('VD: 1')
                    ->unique(
                        table: StudentClass::class,
                        column: 'class_group',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Get $get) {
                            return $rule
                                ->where('major_id', $get('major_id'))
                                ->where('academic_year', $get('academic_year'));
                        }
                    )
                    ->validationMessages([
                        'unique' => ':Attribute đã tồn tại'
                    ]),

                Toggle::make('is_active')
                    ->label('Đang hoạt động')
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
                TextColumn::make('code')
                    ->label('Mã lớp')
                    ->badge()
                    ->copyable()
                    ->sortable()
                    ->searchable()
                    ->size(TextSize::Large),

                TextColumn::make('name')
                    ->label('Tên lớp')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('major.name')
                    ->label('Ngành')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable()
                    ->size(TextSize::Large),

                TextColumn::make('major.department.name')
                    ->label('Khoa')
                    ->sortable()
                    ->size(TextSize::Large)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('academic_year')
                    ->label('Khóa')
                    ->formatStateUsing(fn($state) => "K. " . substr($state, -2))
                    ->sortable()
                    ->alignBetween(),


                TextColumn::make('class_group')
                    ->label('Nhóm')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('major_id')
                    ->label('Ngành học')
                    ->relationship('major', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('department')
                    ->label('Khoa')
                    ->relationship('major.department', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Filter::make('academic_year')
                    ->schema([
                        TextInput::make('academic_year')
                            ->label('Khóa nhập học')
                            ->numeric()
                            ->placeholder('VD: 2023'),
                    ])
                    ->query(
                        fn($query, array $data) =>
                        $query->when(
                            $data['academic_year'],
                            fn($q, $year) => $q->where('academic_year', $year)
                        )
                    ),
                TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã khóa')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn(StudentClass $r) => $r->is_active ? 'Khóa' : 'Mở khóa')
                    ->icon(fn(StudentClass $r) => $r->is_active
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open')
                    ->color(fn(StudentClass $r) => $r->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->visible(fn(): bool => static::canForAction('update'))
                    ->modalHeading(fn(StudentClass $r) => $r->is_active
                        ? "Khóa lớp {$r->name}?"
                        : "Mở khóa lớp {$r->name}?")
                    ->action(function (StudentClass $r): void {
                        abort_unless(static::canForAction('update'), 403);
                        $r->toggleActive();
                    }),
                EditAction::make()
                    ->modalWidth(Width::ThreeExtraLarge),
            ])
            ->defaultSort('academic_year', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStudentClasses::route('/'),
        ];
    }

    protected static function generateName(
        ?string $majorName,
        ?int $year,
        ?int $group
    ): string {
        if (!$majorName || !$year || !$group) {
            return '';
        }
        $shortYear = str_pad(substr((string) $year, -2), 2, '0', STR_PAD_LEFT);
        $paddedGroup = str_pad((string) $group, 2, '0', STR_PAD_LEFT);

        return "{$majorName} - K.{$shortYear} - Lớp {$paddedGroup}";
    }

    protected static function jsGenerateName(): string
    {
        return <<<'JS'
        const major = $get('major_name') ?? '';
        const year = $get('academic_year');
        const group = $get('class_group')

        if (!major || !year || !group) {
            $set('name', '')
        } else {
            const shortYear = year.toString().slice(-2);
            const paddedGroup = String(group).padStart(2, '0');
            $set('name', `${major} - K.${shortYear} - Lớp ${paddedGroup}`);
        }
        JS;
    }

    // protected static function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $majorName = Major::find($data['major_id'])?->name;

    //     $data['name'] = self::generateName(
    //         $majorName,
    //         $data['academic_year'] ?? null,
    //         $data['class_group'] ?? null
    //     );

    //     return $data;
    // }

    // protected static function mutateFormDataBeforeSave(array $data): array
    // {
    //     // ensure name is always updated on edit
    //     $majorName = Major::find($data['major_id'])?->name;

    //     $data['name'] = self::generateName(
    //         $majorName,
    //         $data['academic_year'] ?? null,
    //         $data['class_group'] ?? null
    //     );

    //     return $data;
    // }
}
