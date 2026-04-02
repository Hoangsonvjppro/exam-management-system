<?php

namespace App\Filament\Resources\Lecturers;

use App\Filament\Resources\Lecturers\Pages\ManageLecturers;
use App\Filament\Resources\Lecturers\Pages\ManageLecturerSubjects;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->validationMessages([
                        'unique' => ':Attribute đã tồn tại'
                    ]),

                TextInput::make('lecturer_code')
                    ->label('Mã giảng viên')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ':Attribute đã tồn tại'
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
                    ->live()
                    ->partiallyRenderComponentsAfterStateUpdated([
                        'password_preview',
                    ])
                    ->native(true)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->maxDate(now())
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc'
                    ]),

                TextInput::make('password_preview')
                    ->label('Mật khẩu mặc định')
                    ->disabled()
                    ->live()
                    ->dehydrated(false)
                    ->formatStateUsing(
                        fn($state, Get $get) =>
                        $get('date_of_birth')
                            ? \Carbon\Carbon::parse($get('date_of_birth'))->format('dmY')
                            : 'Chưa chọn ngày sinh'
                    ),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lecturer_code')
                    ->label('Mã GV')
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
                    ->label('Kích hoạt')
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->alignBetween(),
            ])
            ->paginated([10, 25, 50, 'all'])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->filters([
                TrashedFilter::make(),
                Filter::make('active')
                    ->label('Chỉ active')
                    ->query(fn(Builder $query) => $query->where('is_active', true)),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
                Action::make('assign')
                    ->label('Phân công')
                    ->url(fn(User $record): string => static::getUrl('subjects', [
                        'record' => $record,
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
