<?php

namespace App\Filament\Imports;

use App\Models\CourseSection;
use App\Models\CourseSectionStudent;
use App\Models\User;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CourseSectionStudentImporter extends Importer
{
    protected static ?string $model = CourseSectionStudent::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('section_code')
                ->label('Mã lớp học phần')
                ->example('IT001-01-HK1-2526')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('student_code')
                ->label('Mã sinh viên')
                ->example('SV2023001')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('status')
                ->label('Trạng thái')
                ->example('enrolled')
                ->rules(['nullable', 'in:enrolled,dropped,completed']),
        ];
    }

    public function resolveRecord(): ?CourseSectionStudent
    {
        $section = CourseSection::findByCode($this->data['section_code']);
        $student = User::findByStudentCode($this->data['student_code']);

        if (! $section) {
            throw new RowImportFailedException("Lớp học phần '{$this->data['section_code']}' không tồn tại.");
            return null;
        }
        if (! $student) {
            throw new RowImportFailedException("Sinh viên '{$this->data['student_code']}' không tồn tại.");
            return null;
        }

        $enrollment = CourseSectionStudent::findOrNewByEnrollment(
            $section->id,
            $student->id,
        );

        return $enrollment;
    }

    protected function beforeSave(): void
    {
        // Remove virtual columns, set defaults
        unset($this->data['section_code'], $this->data['student_code']);
        $this->data['status'] ??= 'enrolled';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $success = number_format($import->successful_rows);
        $failed  = $import->getFailedRowsCount();

        $body = "Import đăng ký sinh viên hoàn tất: {$success} thành công.";
        if ($failed > 0) $body .= " {$failed} thất bại — tải file lỗi để xem chi tiết.";

        return $body;
    }
}