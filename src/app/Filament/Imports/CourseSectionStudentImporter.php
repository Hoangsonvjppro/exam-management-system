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
            ImportColumn::make('ma_lop_hoc_phan')
                ->label('Mã lớp học phần')
                ->example('IT001-01-HK2-2526')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('ma_sinh_vien')
                ->label('Mã sinh viên')
                ->example('SV2026001')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('trang_thai')
                ->label('Trạng thái')
                ->example('enrolled')
                ->rules(['nullable', 'in:enrolled,dropped,completed']),
        ];
    }

    public function resolveRecord(): ?CourseSectionStudent
    {
        logger()->info('resolveRecord hit', $this->data);

        static $sections = null;
        static $students = null;

        $sections ??= CourseSection::query()->pluck('id', 'code')->all();
        $students ??= User::query()->pluck('id', 'student_code')->all();

        $sectionCode = $this->data['ma_lop_hoc_phan'] ?? null;
        $studentCode = $this->data['ma_sinh_vien'] ?? null;

        $sectionId = $sections[$sectionCode] ?? null;
        $studentId = $students[$studentCode] ?? null;

        if (! $sectionId) {
            throw new RowImportFailedException("Lớp học phần '{$sectionCode}' không tồn tại.");
        }

        if (! $studentId) {
            throw new RowImportFailedException("Sinh viên '{$studentCode}' không tồn tại.");
        }

        $this->data['course_section_id'] = $sectionId;
        $this->data['student_id'] = $studentId;
        return CourseSectionStudent::findOrNewByEnrollment(
            $sectionId,
            $studentId,
        );
    }

    protected function beforeFill(): void
    {
        logger()->info('beforeFill', $this->data);
        $this->data['status'] = $this->data['trang_thai'] ?? 'enrolled';
        unset($this->data['ma_lop_hoc_phan'], $this->data['ma_sinh_vien'], $this->data['trang_thai']);
        logger()->info('After unset', $this->data);
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