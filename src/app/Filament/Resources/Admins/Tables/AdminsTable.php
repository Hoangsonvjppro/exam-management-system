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

class AdminsTable
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
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Vai tro')
                    ->badge()
                    ->separator(','),

                IconColumn::make('is_super_admin')
                    ->label('Super')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Hoat dong')
                    ->boolean(),

                IconColumn::make('must_change_password')
                    ->label('Doi mat khau')
                    ->boolean(),

                TextColumn::make('last_login_at')
                    ->label('Dang nhap cuoi')
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
                    ->label(fn (Admin $record): string => $record->is_active ? 'Khoa tai khoan' : 'Mo khoa')
                    ->color(fn (Admin $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Admin $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Da mo khoa tai khoan admin' : 'Da khoa tai khoan admin')
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
