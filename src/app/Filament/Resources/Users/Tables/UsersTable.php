<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
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
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ho ten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Vai tro')
                    ->badge()
                    ->separator(',')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Trang thai')
                    ->boolean(),

                IconColumn::make('must_change_password')
                    ->label('Doi mat khau')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Cap nhat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role_name')
                    ->label('Vai tro')
                    ->options([
                        'lecturer' => 'Giang vien',
                        'student' => 'Sinh vien',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $role = $data['value'] ?? null;

                        if (blank($role)) {
                            return $query;
                        }

                        return $query->role($role, 'web');
                    }),

                TernaryFilter::make('is_active')
                    ->label('Kich hoat')
                    ->trueLabel('Dang hoat dong')
                    ->falseLabel('Da bi khoa'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('toggle_active')
                    ->label(fn (User $record): string => $record->is_active ? 'Khoa tai khoan' : 'Mo khoa')
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(function (): bool {
                        $admin = auth('admin')->user();

                        return $admin ? Gate::forUser($admin)->allows('admin.users.block') : false;
                    })
                    ->action(function (User $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Da mo khoa tai khoan' : 'Da khoa tai khoan')
                            ->success()
                            ->send();
                    }),

                Action::make('reset_password')
                    ->label('Reset mat khau tam')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool =>
                        $record->hasRole('lecturer')
                        && (($admin = auth('admin')->user())
                            ? Gate::forUser($admin)->allows('admin.users.reset-password')
                            : false)
                    )
                    ->action(function (User $record): void {
                        $tempPassword = Str::password(length: 10);

                        $record->update([
                            'password' => $tempPassword,
                            'must_change_password' => true,
                            'password_changed_at' => null,
                        ]);

                        Notification::make()
                            ->title('Da reset mat khau tam')
                            ->body("Mat khau tam moi: {$tempPassword}")
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
