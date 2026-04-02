<?php

namespace App\Filament\Resources\Admins\Tables;

use App\Models\Admin;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Vai trò')
                    ->badge()
                    ->separator(','),

                IconColumn::make('is_super_admin')
                    ->label('Super')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Hoạt động')
                    ->boolean(),

                IconColumn::make('must_change_password')
                    ->label('Đổi mật khẩu')
                    ->boolean(),

                TextColumn::make('last_login_at')
                    ->label('Đăng nhập cuối')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('toggle_active')
                    ->label(fn(Admin $record): string => $record->is_active ? 'Khóa tài khoản' : 'Mở khóa')
                    ->color(fn(Admin $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(function (Admin $record): bool {
                        $actor = auth('admin')->user();

                        if (! $actor || ! Gate::forUser($actor)->allows('admin.admins.block')) {
                            return false;
                        }

                        if ($record->id === $actor->id) {
                            return false;
                        }

                        if ($record->is_super_admin && ! $actor->is_super_admin) {
                            return false;
                        }

                        return true;
                    })
                    ->action(function (Admin $record): void {
                        $actor = auth('admin')->user();

                        abort_unless($actor && Gate::forUser($actor)->allows('admin.admins.block'), 403);
                        abort_if($record->id === $actor->id, 403);
                        abort_if($record->is_super_admin && ! $actor->is_super_admin, 403);

                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Đã mở khóa tài khoản admin' : 'Đã khóa tài khoản admin')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
