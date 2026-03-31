<?php

namespace App\Filament\Resources\Majors;

use App\Filament\Resources\Majors\Pages\ManageMajors;
use App\Models\Major;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Ngành học';
    protected static string | \UnitEnum | null $navigationGroup = 'Đào tạo';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Ngành học';
    protected static ?string $pluralModelLabel = 'Ngành học';
    protected static ?string $recordTitleAttribute = 'name';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->label('Khoa')
                    ->relationship(
                        name: 'department',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->active()->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc'
                    ]),
                TextInput::make('code')
                    ->label('Mã ngành học')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc',
                        'unique' => ':Attribute đã tồn tại'
                    ]),

                TextInput::make('name')
                    ->label('Tên ngành học')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => ':Attribute là bắt buộc'
                    ]),

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
                    ->label('Mã ngành')
                    ->copyable()
                    ->searchable()
                    ->badge()
                    ->size(TextSize::Large),

                TextColumn::make('name')
                    ->label('Tên ngành')
                    ->weight('semibold')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Khoa')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable()
                    ->size(TextSize::Large),

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
                SelectFilter::make('department_id')
                    ->label('Khoa')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã khóa')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn(Major $r) => $r->is_active ? 'Khóa' : 'Mở khóa')
                    ->icon(fn(Major $r) => $r->is_active
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open')
                    ->color(fn(Major $r) => $r->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn(Major $r) => $r->is_active
                        ? "Khóa ngành {$r->name}?"
                        : "Mở khóa ngành {$r->name}?")
                    ->action(fn(Major $r) => $r->toggleActive()),
                EditAction::make(),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMajors::route('/'),
        ];
    }
}
