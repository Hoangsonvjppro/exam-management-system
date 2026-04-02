<?php

namespace App\Filament\Resources\Announcements;

use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\EditAnnouncement;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Filament\Support\HasAdminCrudPermissions;
use App\Models\Announcement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    use HasAdminCrudPermissions;

    protected static function getAdminPermissionModule(): string
    {
        return 'announcements';
    }

    protected static ?string $model = Announcement::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Thông báo';
    protected static ?string $modelLabel = 'Thông báo';
    protected static ?string $pluralModelLabel = 'Thông báo';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Tiêu đề')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('type')
                ->label('Loại thông báo')
                ->options([
                    'info'    => 'Thông báo',
                    'urgent'  => 'Khẩn cấp',
                    'warning' => 'Cảnh báo',
                    'event'   => 'Sự kiện',
                ])
                ->default('info')
                ->required(),

            Toggle::make('is_published')
                ->label('Hiển thị trên trang chủ')
                ->inline(false)
                ->default(true),

            Textarea::make('body')
                ->label('Nội dung')
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'urgent'  => 'danger',
                        'warning' => 'warning',
                        'event'   => 'success',
                        default   => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'urgent'  => 'Khẩn cấp',
                        'warning' => 'Cảnh báo',
                        'event'   => 'Sự kiện',
                        default   => 'Thông báo',
                    }),

                IconColumn::make('is_published')
                    ->label('Hiển thị')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index'  => ListAnnouncements::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::published()->count() ?: null;
    }
}
