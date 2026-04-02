<?php

namespace App\Filament\Imports;

use App\Models\StudentClass;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class StudentImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('ma_sinh_vien')
                ->label('Mã sinh viên')
                ->example('SV2026001')
                ->requiredMapping()
                ->rules(['required', 'string', 'regex:/^SV\d{7}$/']),

            ImportColumn::make('ho_va_ten')
                ->label('Họ và tên')
                ->example('Nguyễn Văn A')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->label('Email')
                ->example('sv2026001@ems.edu.vn')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('so_dien_thoai')
                ->label('Số điện thoại')
                ->example('0912345678')
                ->requiredMapping()
                ->rules(['required', 'string', 'regex:/^0[0-9]{9}$/']),

            ImportColumn::make('ngay_sinh')
                ->label('Ngày sinh')
                ->example('15/08/2005')
                ->requiredMapping()
                ->rules(['required', 'date', 'before_or_equal:' . now()->subYears(15)->toDateString()]),

            ImportColumn::make('ma_lop')
                ->label('Mã lớp')
                ->example('SE23-01')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('kich_hoat')
                ->label('Kích hoạt tài khoản')
                ->example('1')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?User
    {
        $studentCode = $this->normalizeStudentCode($this->data['ma_sinh_vien'] ?? null);
        $email = $this->normalizeEmail($this->data['email'] ?? null);
        $classCode = $this->normalizeClassCode($this->data['ma_lop'] ?? null);

        $this->data['ma_sinh_vien'] = $studentCode;
        $this->data['email'] = $email;
        $this->data['ma_lop'] = $classCode;

        $class = $this->resolveClass($classCode);

        if (! $class) {
            throw new RowImportFailedException("Lớp '{$classCode}' không tồn tại.");
        }

        $recordByStudentCode = User::query()
            ->where('student_code', $studentCode)
            ->first();

        $recordByEmail = User::query()
            ->where('email', $email)
            ->first();

        if ($recordByStudentCode && $recordByEmail && $recordByStudentCode->isNot($recordByEmail)) {
            throw new RowImportFailedException(
                "MSSV '{$studentCode}' và email '{$email}' đang thuộc 2 tài khoản khác nhau.",
            );
        }

        $this->data['student_class_id'] = $class->id;
        $this->data['department_id'] = $class->major?->department_id;

        return $recordByStudentCode ?? $recordByEmail ?? new User();
    }

    public function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        $recordId = $this->record?->getKey();

        $studentCodeUniqueRule = Rule::unique(User::class, 'student_code');
        $emailUniqueRule = Rule::unique(User::class, 'email');
        $phoneUniqueRule = Rule::unique(User::class, 'phone');

        if ($recordId) {
            $studentCodeUniqueRule->ignore($recordId);
            $emailUniqueRule->ignore($recordId);
            $phoneUniqueRule->ignore($recordId);
        }

        $rules['ma_sinh_vien'][] = $studentCodeUniqueRule;
        $rules['email'][] = $emailUniqueRule;
        $rules['so_dien_thoai'][] = $phoneUniqueRule;

        return $rules;
    }

    protected function beforeValidate(): void
    {
        $this->data['ho_va_ten'] = trim((string) ($this->data['ho_va_ten'] ?? ''));
        $this->data['so_dien_thoai'] = preg_replace('/\D+/', '', (string) ($this->data['so_dien_thoai'] ?? '')) ?? '';
        $this->data['ngay_sinh'] = $this->normalizeDate($this->data['ngay_sinh'] ?? null);
    }

    protected function beforeFill(): void
    {
        $this->record->name = $this->data['ho_va_ten'];
        $this->record->student_code = $this->data['ma_sinh_vien'];
        $this->record->email = $this->data['email'];
        $this->record->phone = $this->data['so_dien_thoai'];
        $this->record->date_of_birth = $this->data['ngay_sinh'];
        $this->record->student_class_id = $this->data['student_class_id'];
        $this->record->department_id = $this->data['department_id'];
        $this->record->is_active = $this->data['kich_hoat']
            ?? ($this->record->exists ? $this->record->is_active : true);

        unset(
            $this->data['ho_va_ten'],
            $this->data['ma_sinh_vien'],
            $this->data['email'],
            $this->data['so_dien_thoai'],
            $this->data['ngay_sinh'],
            $this->data['ma_lop'],
            $this->data['kich_hoat'],
            $this->data['student_class_id'],
            $this->data['department_id'],
        );
    }

    protected function afterSave(): void
    {
        if (! $this->record->hasRole('student')) {
            $this->record->assignRole('student');
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $success = number_format($import->successful_rows);
        $failed = $import->getFailedRowsCount();

        $body = "Import sinh viên hoàn tất: {$success} thành công.";

        if ($failed > 0) {
            $body .= " {$failed} thất bại - tải file lỗi để xem chi tiết.";
        }

        return $body;
    }

    protected function resolveClass(string $classCode): ?StudentClass
    {
        static $classes = null;

        $classes ??= StudentClass::query()
            ->with('major:id,department_id')
            ->get()
            ->keyBy(fn(StudentClass $class): string => $this->normalizeClassCode($class->code));

        return $classes[$classCode] ?? null;
    }

    protected function normalizeStudentCode(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }

    protected function normalizeClassCode(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }

    protected function normalizeEmail(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    protected function normalizeDate(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        $rawValue = trim((string) $value);

        if ($rawValue === '') {
            throw new RowImportFailedException('Ngày sinh không được để trống.');
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $rawValue)->toDateString();
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($rawValue)->toDateString();
        } catch (\Throwable) {
            throw new RowImportFailedException("Ngày sinh '{$rawValue}' không hợp lệ.");
        }
    }
}
