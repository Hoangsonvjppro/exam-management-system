<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ManageDepartments;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'departments';
    }

    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;


    protected static ?string $navigationLabel = 'Khoa';

    protected static string | \UnitEnum | null $navigationGroup = 'Đào tạo';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Khoa';

    protected static ?string $pluralModelLabel = 'Khoa';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Mã khoa')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->maxLength(50)
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->columnSpanFull()
                    ->placeholder('VD: CT')
                    ->validationMessages(([
                        'unique' => ":Attribute đã tồn tại"
                    ])),

                TextInput::make('name')
                    ->label('Tên khoa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Đang hoạt động')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã khoa')
                    ->badge()
                    ->copyable()
                    ->searchable()
                    ->size(TextSize::Large),

                TextColumn::make('name')
                    ->label('Tên khoa')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),

                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã khóa')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(
                        fn(Department $record) =>
                        $record->is_active ? 'Khóa' : 'Mở khóa'
                    )
                    ->icon(
                        fn(Department $record) =>
                        $record->is_active
                            ? 'heroicon-o-lock-closed'
                            : 'heroicon-o-lock-open'
                    )
                    ->color(
                        fn(Department $record) =>
                        $record->is_active ? 'warning' : 'success'
                    )
                    ->requiresConfirmation()
                    ->visible(fn(): bool => static::canForAction('update'))
                    ->modalHeading(
                        fn(Department $record) =>
                        $record->is_active
                            ? 'Khóa khoa ' . $record->name . '?'
                            : 'Mở khóa khoa ' . $record->name . '?'
                    )
                    ->action(function (Department $record): void {
                        abort_unless(static::canForAction('update'), 403);
                        $record->toggleActive();
                    }),
                EditAction::make()
                    ->modalWidth(Width::Medium),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDepartments::route('/'),
        ];
    }
}
