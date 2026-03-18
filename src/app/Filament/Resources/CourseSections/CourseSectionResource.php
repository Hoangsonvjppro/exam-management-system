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

    protected static ?string $navigationLabel = 'Lop hoc phan';

    protected static ?string $modelLabel = 'Lop hoc phan';

    protected static ?string $pluralModelLabel = 'Lop hoc phan';

    protected static string | \UnitEnum | null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Ma lop')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->label('Ten lop hoc phan')
                ->maxLength(255)
                ->columnSpanFull(),

            Select::make('subject_id')
                ->label('Mon hoc')
                ->relationship('subject', 'name')
                ->searchable()
                ->preload(),

            Select::make('semester_id')
                ->label('Hoc ky')
                ->relationship('semester', 'name')
                ->searchable()
                ->preload(),

            Select::make('lecturer_id')
                ->label('Giang vien')
                ->relationship(
                    name: 'lecturer',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query->role('lecturer')
                )
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('max_students')
                ->label('Si so toi da')
                ->numeric()
                ->default(100)
                ->minValue(1)
                ->maxValue(999)
                ->required(),

            Select::make('status')
                ->label('Trang thai')
                ->options([
                    'active' => 'Dang mo',
                    'archived' => 'Luu tru',
                    'cancelled' => 'Huy',
                ])
                ->default('active')
                ->required(),

            TextInput::make('invite_code')
                ->label('Ma moi vao lop')
                ->maxLength(20)
                ->helperText('De trong de he thong tu sinh')
                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper($state) : null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Ma lop')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Ten lop')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('subject.name')
                    ->label('Mon hoc')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semester.name')
                    ->label('Hoc ky')
                    ->sortable(),

                TextColumn::make('lecturer.name')
                    ->label('Giang vien')
                    ->searchable(),

                TextColumn::make('max_students')
                    ->label('Si so')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trang thai')
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
                    ->label('Trang thai')
                    ->options([
                        'active' => 'Dang mo',
                        'archived' => 'Luu tru',
                        'cancelled' => 'Huy',
                    ]),
                SelectFilter::make('subject_id')
                    ->label('Mon hoc')
                    ->relationship('subject', 'name'),
                SelectFilter::make('semester_id')
                    ->label('Hoc ky')
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
