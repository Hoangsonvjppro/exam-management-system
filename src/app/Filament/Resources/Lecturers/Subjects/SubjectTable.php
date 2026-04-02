<?php

namespace App\Filament\Resources\Lecturers\Subjects;

use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectTable
{
    public static function configure(Table $table): Table
    {
        return $table
           ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('name')->searchable(),
            ])
            ->paginated([10, 25, 50]);
    }
}

// <?php

// namespace App\Filament\Resources\Lecturers\Subjects;

// use App\Models\Subject;
// use Filament\Actions\Action;
// use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Filters\SelectFilter;
// use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;

// class SubjectTable
// {
//     public static function configure(Table $table, object $page): Table
//     {
//         return $table
//             ->modifyQueryUsing(function (Builder $query) use ($page): Builder {
//                 $lecturer = $page->getRecord();
//                 $lecturerId = $lecturer->id;
//                 return $query->whereHas('lecturers', function (Builder $q) use ($lecturerId) {
//                     // Lọc các môn học mà giảng viên này giảng dạy
//                     $q->where('users.id', $lecturerId);
//                 });
//             })
//             ->columns([
//                 TextColumn::make('name')
//                     ->label('Tên môn học')
//                     ->searchable()
//                     ->sortable(),
//                 TextColumn::make('code')
//                     ->label('Mã môn học')
//                     ->searchable()
//                     ->sortable(),
//                 TextColumn::make('credits')
//                     ->label('Số tín chỉ')
//                     ->sortable(),
//             ])
//             ->filters([
//                 SelectFilter::make('credits')
//                     ->label('Số tín chỉ')
//                     ->options([
//                         1 => '1 tín chỉ',
//                         2 => '2 tín chỉ',
//                         3 => '3 tín chỉ',
//                     ])
//                     ->query(fn(Builder $query, array $data): Builder => $query->when(
//                         filled($data['value'] ?? null),
//                         fn(Builder $q): Builder => $q->where('credits', $data['value'])
//                     )),
//             ])
//             ->recordActions([
//                 Action::make('removeSubject')
//                     ->label('Xóa')
//                     ->icon('heroicon-o-trash')
//                     ->color('danger')
//                     ->requiresConfirmation()
//                     ->action(function (Subject $subject) use ($page) {
//                         $lecturer = $page->getRecord(); // Giảng viên hiện tại
//                         $lecturer->subjects()->detach($subject->id); // Gỡ bỏ môn học khỏi giảng viên
//                     }),
//             ]);
//     }
// }