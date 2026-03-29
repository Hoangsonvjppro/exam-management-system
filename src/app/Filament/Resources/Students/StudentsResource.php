<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\StudentResource\Pages\ManageStudents as PagesManageStudents;
use App\Filament\Resources\Students\Pages\ManageStudents;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = "Sinh viên";

    protected static ?string $modelLabel = 'Sinh viên';

    protected static ?string $pluralModelLabel = 'Sinh viên';

    protected static ?string $slug = 'students';

    protected static ?string $recordTitleAttribute = 'student';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('student_code');
    }
public static function form(Schema $schema): Schema
{
    return $schema->components([
        TextInput::make('name')
            ->label('Họ và tên')
            ->required()
            ->maxLength(255),

        TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255),

        TextInput::make('student_code')
            ->label('Mã số sinh viên')
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(20),

        TextInput::make('phone')
            ->label('Số điện thoại')
            ->required()
            ->tel()
            ->maxLength(10),

        TextInput::make('class_name')
            ->label('Lớp')
            ->required()
            ->maxLength(50),

        TextInput::make('department')
            ->label('Khoa')
            ->required()
            ->maxLength(100),

        DatePicker::make('date_of_birth')
            ->label('Ngày sinh')
            ->required()
            ->native(true)
            ->displayFormat('d/m/Y')
            ->maxDate(now()->subYears(15)),
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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('class_name')
                    ->label('Lớp')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department')
                    ->label('Khoa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_of_birth')
                    ->label('Ngày sinh')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('password')
                    ->label('Có mật khẩu')
                    ->boolean()
                    ->getStateUsing(fn (User $record) => ! is_null($record->password))
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // IconColumn::make('is_active')
                //     ->label('Kích hoạt')
                //     ->boolean(),

                // TextColumn::make('created_at')
                //     ->label('Ngày tạo')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('has_password')
                    ->label('Trạng thái mật khẩu')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã có mật khẩu')
                    ->falseLabel('Chưa có mật khẩu')
                    ->queries(
                        true:  fn (Builder $q) => $q->whereNotNull('password'),
                        false: fn (Builder $q) => $q->whereNull('password'),
                    ),

                TernaryFilter::make('is_active')
                    ->label('Kích hoạt')
                    ->placeholder('Tất cả'),

                Filter::make('missing_dob')
                    ->label('Chưa có ngày sinh')
                    ->query(fn (Builder $q) => $q->whereNull('date_of_birth'))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
