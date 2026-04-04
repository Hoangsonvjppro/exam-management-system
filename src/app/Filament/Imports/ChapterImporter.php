<?php

namespace App\Filament\Imports;

use App\Models\Chapter;
use App\Models\Subject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;

class ChapterImporter extends Importer
{
    protected static ?string $model = Chapter::class;

    protected static array $subjectsCache = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('ma_mon_hoc')
                ->label('Mã môn học')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->example('IT001'),

            ImportColumn::make('ten_chuong')
                ->label('Tên chương')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Giới thiệu'),

            ImportColumn::make('thu_tu')
                ->label('Thứ tự')
                ->rules(['required', 'integer', 'min:1'])
                ->example('1'),

            ImportColumn::make('mo_ta')
                ->label('Mô tả')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Chapter
    {
        $subjectCode = $this->normalizeCode($this->data['ma_mon_hoc'] ?? null);

        $subject = $this->resolveSubject($subjectCode);

        if (! $subject) {
            throw new RowImportFailedException("Môn học '{$subjectCode}' không tồn tại.");
        }

        // Optional: tránh duplicate chapter theo subject + chuong
        return Chapter::firstOrNew([
            'subject_id' => $subject->id,
            'order' => $this->data['thu_tu'],
        ]);
    }

    protected function beforeValidate(): void
    {
        $this->data['ma_mon_hoc'] = $this->normalizeCode($this->data['ma_mon_hoc'] ?? null);
        $this->data['ten_chuong'] = trim((string) ($this->data['ten_chuong'] ?? ''));
    }

    protected function beforeFill(): void
    {
        $subject = $this->resolveSubject($this->data['ma_mon_hoc']);

        $this->record->subject_id = $subject->id;
        $this->record->name = $this->data['ten_chuong'];
        $this->record->order = $this->data['thu_tu'] ?? 0;
        $this->record->description = $this->data['mo_ta'] ?? null;

        unset(
            $this->data['ma_mon_hoc'],
            $this->data['ten_chuong'],
            $this->data['thu_tu'],
            $this->data['mo_ta'],
        );
    }

    protected function afterSave(): void {}

    public static function getCompletedNotificationBody(Import $import): string
    {
        $success = number_format($import->successful_rows);
        $failed = $import->getFailedRowsCount();

        $body = "Import chương hoàn tất: {$success} thành công.";

        if ($failed > 0) {
            $body .= " {$failed} thất bại - tải file lỗi để xem chi tiết.";
        }

        return $body;
    }

    protected function resolveSubject(string $code): ?Subject
    {
        if (! isset(self::$subjectsCache[$code])) {
            self::$subjectsCache[$code] = Subject::where('code', $code)->first();
        }

        return self::$subjectsCache[$code];
    }

    protected function normalizeCode(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }
}