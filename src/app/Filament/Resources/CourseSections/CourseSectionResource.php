<?php

namespace App\Filament\Resources\CourseSections;

use App\Filament\Resources\CourseSections\Pages\CreateCourseSection;
use App\Filament\Resources\CourseSections\Pages\EditCourseSection;
use App\Filament\Resources\CourseSections\Pages\ListCourseSections;
use App\Models\CourseSection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseSectionResource extends Resource
{
    protected static ?string $model = CourseSection::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Lớp học phần';

    protected static ?string $modelLabel = 'Lớp học phần';

    protected static ?string $pluralModelLabel = 'Lớp học phần';

    protected static string | \UnitEnum | null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Mã lớp')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->label('Tên lớp học phần')
                ->maxLength(255)
                ->columnSpanFull(),

            Select::make('subject_id')
                ->label('Môn học')
                ->relationship('subject', 'name')
                ->searchable()
                ->preload(),

            Select::make('semester_id')
                ->label('Học kỳ')
                ->relationship('semester', 'name')
                ->searchable()
                ->preload(),

            Select::make('lecturer_id')
                ->label('Giảng viên')
                ->relationship(
                    name: 'lecturer',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query->role('lecturer')
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

            TextInput::make('invite_code')
                ->label('Mã mời vào lớp')
                ->maxLength(20)
                ->helperText('Để trống để hệ thống tự sinh')
                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper($state) : null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã lớp')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('max_students')
                    ->label('Sĩ số')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseSections::route('/'),
            'create' => CreateCourseSection::route('/create'),
            'edit' => EditCourseSection::route('/{record}/edit'),
        ];
    }
}
