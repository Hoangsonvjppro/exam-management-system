<?php

namespace App\Filament\Resources\Assignments;

use App\Filament\Resources\Assignments\Pages\ManageAssignments;
use App\Filament\Resources\Assignments\Tables\SubjectTable;
use App\Models\Assignment;
use App\Models\Subject;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssignmentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Phân công';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    // -------------------------------------------------------- Disable CRUD

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAssignments::route('/'),
        ];
    }
}
