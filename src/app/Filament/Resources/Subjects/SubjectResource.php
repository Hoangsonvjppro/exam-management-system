<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\CreateSubject;
use App\Filament\Resources\Subjects\Pages\EditSubject;
use App\Filament\Resources\Subjects\Pages\ListSubjects;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\Subject;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'subjects';
    }

    protected static ?string $model = Subject::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Môn học';

    protected static ?string $modelLabel = 'Môn học';

    protected static ?string $pluralModelLabel = 'Môn học';

    protected static string | \UnitEnum | null $navigationGroup = 'Đào tạo';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Mã môn học')
                ->required()
                ->maxLength(20)
                ->columnSpanFull()
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'unique' => ':Attribute đã tồn tại'
                ])
                ->disabledOn('edit'),

            TextInput::make('name')
                ->label('Tên môn học')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('credits')
                ->label('Số tín chỉ')
                ->numeric()
                ->minValue(1)
                ->maxValue(10)
                ->required(),

            Select::make('department_id')
                ->label('Khoa phụ trách')
                ->relationship('department', 'name')
                ->searchable(['name', 'code'])
                ->getOptionLabelFromRecordUsing(
                    fn($record) =>
                    "{$record->code} - {$record->name}"
                )
                ->preload()
                ->required()
                ->native(false)
                ->validationMessages([
                    'required' => ':Attribute là bắt buộc'
                ]),

            Textarea::make('description')
                ->label('Mô tả môn học')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã môn học')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->copyable()
                    ->size(TextSize::Large),

                TextColumn::make('name')
                    ->label('Tên môn học')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('credits')
                    ->label('Số tín chỉ')
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('Khoa')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('credits')
                    ->native(false)
                    ->label('Số tín chỉ')
                    ->options(fn(): array => Subject::query()
                        ->select('credits')
                        ->distinct()
                        ->orderBy('credits')
                        ->pluck('credits', 'credits')
                        ->toArray()),

                TrashedFilter::make()
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::TwoExtraLarge),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubjects::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
