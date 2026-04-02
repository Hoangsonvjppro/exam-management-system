<?php

namespace App\Filament\Resources\AdminRoles\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class AdminRolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên vai trò')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('permissions_count')
                    ->label('Số chức năng')
                    ->sortable(),

                IconColumn::make('is_protected')
                    ->label('Vai trò hệ thống')
                    ->state(fn(Role $record): bool => $record->name === 'root_admin')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn(Role $record): bool => $record->name !== 'root_admin'),
            ]);
    }
}
