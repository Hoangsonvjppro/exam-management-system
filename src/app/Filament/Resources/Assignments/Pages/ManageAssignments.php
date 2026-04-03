<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

use function Safe\array_flip;

class ManageAssignments extends Page implements HasSchemas, HasTable
{
    use InteractsWithSchemas;
    use InteractsWithTable;
 
    protected static string $resource = AssignmentResource::class;
 
    protected string $view = 'filament.resources.assignment-resource.pages.manage-assignments';
 
    protected static ?string $title = 'Phân công môn học cho giảng viên';
 
    // -------------------------------------------------------- Livewire State
 
    /** ID giảng viên đang được chọn */
    public ?int $selectedLecturerId = null;

    /** Danh sách subject_id đã tick (chưa lưu) */
    public array $checkedSubjectIds = [];
 
    /** Snapshot khi load / sau khi lưu → dùng để Cancel */
    public array $originalSubjectIds = [];
 
    // -------------------------------------------------------- Schema (Select)
 
    /**
     * Schema chứa Select giảng viên.
     * Tên method = tên biến trong view: {{ $this->lecturerForm }}
     */
    public function lecturerForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('')            // bind trực tiếp vào component property
            ->components([
                Select::make('selectedLecturerId')
                    ->label('Chọn giảng viên')
                    ->placeholder('-- Chọn giảng viên --')
                    ->options(function (): array {
                        return User::lecturers()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $u): array => [
                                $u->id => "[{$u->lecturer_code}] {$u->name}",
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn () => $this->onLecturerSelected())
                    ->columnSpanFull(),
            ]);
    }
 
    // ---------------------------------------------------------- Table (Subjects)
 
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subject::query()->orderBy('code')
            )
            ->columns([
                // Cột checkbox – state lưu trong Livewire, không ghi DB ngay
                CheckboxColumn::make('assigned')
                    ->label('Phân công')
                    ->getStateUsing(
                        fn (Subject $record): bool =>
                            in_array($record->id, $this->checkedSubjectIds, true)
                    )
                    ->updateStateUsing(function (Subject $record, bool $state): void {
                        if ($state) {
                            $this->checkedSubjectIds = array_values(array_unique([
                                ...$this->checkedSubjectIds,
                                $record->id
                            ]));
                        } else {
                            $this->checkedSubjectIds = array_values(
                                array_filter(
                                    $this->checkedSubjectIds,
                                    fn (int $id): bool => $id !== $record->id
                                )
                            );
                        }
                        $this->skipRender();
                    })
                    ->disabled(fn (): bool => $this->selectedLecturerId === null),
 
                TextColumn::make('code')
                    ->label('Mã môn')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('info'),
 
                TextColumn::make('name')
                    ->label('Tên môn học')
                    ->searchable()
                    ->wrap(),
 
                TextColumn::make('credits')
                    ->label('Số tín chỉ')
                    ->alignCenter(),
            ])
            ->searchPlaceholder('Tìm mã môn, tên môn...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('Không có môn học nào')
            ->emptyStateIcon('heroicon-o-book-open');
    }
 
    // --------------------------------------------------------- Header Actions
 
    // protected function getHeaderActions(): array
    // {
        // return [
        //     Action::make('save')
        //         ->label('Lưu phân công')
        //         ->icon('heroicon-o-check-circle')
        //         ->color('primary')
        //         ->disabled(fn (): bool => $this->selectedLecturerId === null)
        //         ->requiresConfirmation()
        //         ->modalHeading('Xác nhận lưu phân công')
        //         ->modalDescription(
        //             fn (): string => $this->selectedLecturerId
        //                 ? 'Bạn có chắc muốn cập nhật phân công cho giảng viên này?'
        //                 : ''
        //         )
        //         ->action(fn () => $this->save()),
 
        //     Action::make('cancel')
        //         ->label('Hủy thay đổi')
        //         ->icon('heroicon-o-arrow-uturn-left')
        //         ->color('gray')
        //         ->disabled(fn (): bool => $this->selectedLecturerId === null)
        //         ->action(fn () => $this->cancel()),
        // ];
    // }
 
    // ------------------------------------------------------------------ Logic
 
    /**
     * Gọi khi giảng viên thay đổi trong Select.
     * Load danh sách subject đã phân công → đồng bộ lên checkedSubjectIds.
     */
    public function onLecturerSelected(): void
    {
        if (! $this->selectedLecturerId) {
            $this->checkedSubjectIds  = [];
            $this->originalSubjectIds = [];
            $this->resetTable();
            return;
        }
 
        $ids = User::find($this->selectedLecturerId)
            ?->subjects()
            ->pluck('subjects.id')
            ->toArray() ?? [];

        $this->checkedSubjectIds  = $ids;
        $this->originalSubjectIds = $ids;

        // Reset về trang 1, xóa search khi đổi giảng viên
        $this->resetTable();
    }
 
    /**
     * Sync assignment: chỉ những subject đang checked mới tồn tại trong pivot.
     */
    public function save(): void
    {
        if (! $this->selectedLecturerId) {
            return;
        }
 
        $lecturer = User::find($this->selectedLecturerId);
 
        if (! $lecturer) {
            Notification::make()
                ->title('Không tìm thấy giảng viên')
                ->danger()
                ->send();
 
            return;
        }
 
        // sync() tự động thêm mới + xóa bỏ những gì không còn trong list
        $lecturer->subjects()->sync($this->checkedSubjectIds);

        // Cập nhật snapshot sau khi lưu
        $this->originalSubjectIds = $this->checkedSubjectIds;
        $addedCount = count($this->checkedSubjectIds);
    
        Notification::make()
            ->title('Lưu thành công')
            ->body("Đã phân công {$addedCount} môn học cho {$lecturer->name}.")
            ->success()
            ->send();
    }
 
    /**
     * Hoàn tác về trạng thái lúc load (hoặc lần save gần nhất).
     */
    public function cancel(): void
    {
        $this->checkedSubjectIds = $this->originalSubjectIds;
    }
}