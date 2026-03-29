<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\AdminUserLifecycleService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class UsersTable
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
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Vai trò')
                    ->badge()
                    ->separator(',')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean(),

                IconColumn::make('must_change_password')
                    ->label('Đổi mật khẩu')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role_name')
                    ->label('Vai trò')
                    ->options([
                        'lecturer' => 'Giảng viên',
                        'student' => 'Sinh viên',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $role = $data['value'] ?? null;

                        if (blank($role)) {
                            return $query;
                        }

                        return $query->role($role, 'web');
                    }),

                TernaryFilter::make('is_active')
                    ->label('Kích hoạt')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã bị khóa'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('toggle_active')
                    ->label(fn(User $record): string => $record->is_active ? 'Khoa tài khoản' : 'Mở khóa')
                    ->color(fn(User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(function (): bool {
                        $admin = auth('admin')->user();

                        return $admin ? Gate::forUser($admin)->allows('admin.users.block') : false;
                    })
                    ->action(function (User $record): void {
                        $actorAdminId = auth('admin')->id();
                        $isActive = app(AdminUserLifecycleService::class)->toggleActive($record, $actorAdminId);

                        Notification::make()
                            ->title($isActive ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản')
                            ->success()
                            ->send();
                    }),

                Action::make('reset_password')
                    ->label('Reset mật khẩu tạm')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn(User $record): bool =>
                        $record->hasRole('lecturer')
                            && (($admin = auth('admin')->user())
                                ? Gate::forUser($admin)->allows('admin.users.reset-password')
                                : false)
                    )
                    ->action(function (User $record): void {
                        $actorAdminId = auth('admin')->id();
                        $tempPassword = app(AdminUserLifecycleService::class)->resetLecturerPassword($record, $actorAdminId);

                        Notification::make()
                            ->title('Đã reset mật khẩu tạm thời')
                            ->body("Mật khẩu tạm mới: {$tempPassword}")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
