<?php

namespace App\Filament\Imports;

use App\Models\Subject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class SubjectImporter extends Importer
{
    protected static ?string $model = Subject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('ma_mon_hoc')
                ->label('Mã môn học')
                ->example('IT001')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:50']),

            ImportColumn::make('ten_mon_hoc')
                ->label('Tên môn học')
                ->example('Cơ sở dữ liệu')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('so_tin_chi')
                ->label('Số tín chỉ')
                ->example('3')
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:1', 'max:10']),
        ];
    }

    public function resolveRecord(): ?Subject
    {
        $subjectCode = $this->normalizeCode($this->data['ma_mon_hoc']);

        return Subject::firstOrNew([
            'code' => $subjectCode,
        ]);
    }

    public function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        $recordId = $this->record?->getKey();

        $uniqueRule = Rule::unique(Subject::class, 'code');

        if ($recordId) {
            $uniqueRule->ignore($recordId);
        }

        $rules['ma_mon_hoc'][] = $uniqueRule;

        return $rules;
    }

    protected function beforeValidate(): void
    {
        $this->data['ma_mon_hoc'] = $this->normalizeCode($this->data['ma_mon_hoc']);
        $this->data['ten_mon_hoc'] = trim((string) $this->data['ten_mon_hoc']);
    }

    protected function beforeFill(): void
    {
        $this->record->code = $this->data['ma_mon_hoc'];
        $this->record->name = $this->data['ten_mon_hoc'];
        $this->record->credits = (int) $this->data['so_tin_chi'];

        unset(
            $this->data['ma_mon_hoc'],
            $this->data['ten_mon_hoc'],
            $this->data['so_tin_chi'],
        );
    }

    protected function afterSave(): void {}

    public static function getCompletedNotificationBody(Import $import): string
    {
        $success = number_format($import->successful_rows);
        $failed = $import->getFailedRowsCount();

        $body = "Import môn học hoàn tất: {$success} thành công.";

        if ($failed > 0) {
            $body .= " {$failed} thất bại - tải file lỗi để xem chi tiết.";
        }

        return $body;
    }

    protected function normalizeCode(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }
}
