<?php

namespace App\Filament\Imports;

use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class LecturerImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('ma_giang_vien')
                ->label('Mã giảng viên')
                ->example('GV_001')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('ho_va_ten')
                ->label('Họ và tên')
                ->example('Nguyễn Văn A')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->label('Email')
                ->example('gv@ems.edu.vn')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('so_dien_thoai')
                ->label('Số điện thoại')
                ->example('0912345678')
                ->requiredMapping()
                ->rules(['required', 'string', 'regex:/^0[0-9]{9}$/']),

            ImportColumn::make('ngay_sinh')
                ->label('Ngày sinh')
                ->example('15/08/1990')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): ?User
    {
        $lecturerCode = $this->normalizeLecturerCode($this->data['ma_giang_vien'] ?? null);
        $email        = $this->normalizeEmail($this->data['email'] ?? null);

        // Ghi lại giá trị đã normalize vào data để các bước sau dùng nhất quán
        $this->data['ma_giang_vien'] = $lecturerCode;
        $this->data['email']         = $email;

        $recordByCode  = User::query()->where('lecturer_code', $lecturerCode)->first();
        $recordByEmail = User::query()->where('email', $email)->first();

        // Conflict: cùng code & email nhưng khác user
        if ($recordByCode && $recordByEmail && $recordByCode->isNot($recordByEmail)) {
            throw new RowImportFailedException(
                "Mã giảng viên '{$lecturerCode}' và email '{$email}' đang thuộc 2 tài khoản khác nhau."
            );
        }

        return $recordByCode ?? $recordByEmail ?? new User();
    }

    public function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        $recordId = $this->record?->getKey();

        $codeRule  = Rule::unique(User::class, 'lecturer_code');
        $emailRule = Rule::unique(User::class, 'email');
        $phoneRule = Rule::unique(User::class, 'phone');

        if ($recordId) {
            $codeRule->ignore($recordId);
            $emailRule->ignore($recordId);
            $phoneRule->ignore($recordId);
        }

        $rules['ma_giang_vien'][] = $codeRule;
        $rules['email'][]         = $emailRule;
        $rules['so_dien_thoai'][] = $phoneRule;

        return $rules;
    }

    // ------------------------------------------------------------------ hooks

    protected function beforeValidate(): void
    {
        $this->data['ho_va_ten']    = trim((string) ($this->data['ho_va_ten'] ?? ''));
        $this->data['so_dien_thoai'] = preg_replace('/\D+/', '', (string) ($this->data['so_dien_thoai'] ?? ''));
        $this->data['ngay_sinh']    = $this->normalizeDate($this->data['ngay_sinh'] ?? null);
    }

    protected function beforeFill(): void
    {
        // Gán trực tiếp vào record, không để Filament fill qua column mapping
        $this->record->name          = $this->data['ho_va_ten'];
        $this->record->lecturer_code = $this->data['ma_giang_vien'];
        $this->record->email         = $this->data['email'];
        $this->record->phone         = $this->data['so_dien_thoai'];
        $this->record->date_of_birth = $this->data['ngay_sinh'];  // Y-m-d

        // Mật khẩu mặc định = ddmmyyyy của ngày sinh (chỉ set khi tạo mới)
        if (! $this->record->exists) {
            $password = $this->buildPassword($this->data['ngay_sinh']);
            $this->record->password              = Hash::make($password);
            $this->record->must_change_password  = true;
        }

        unset(
            $this->data['ho_va_ten'],
            $this->data['ma_giang_vien'],
            $this->data['email'],
            $this->data['so_dien_thoai'],
            $this->data['ngay_sinh'],
        );
    }

    protected function afterSave(): void
    {
        if (! $this->record->hasRole('lecturer')) {
            $this->record->assignRole('lecturer');
        }
    }

    // --------------------------------------------------------------- helpers

    /**
     * Tạo mật khẩu dạng ddmmyyyy từ chuỗi ngày Y-m-d.
     * Ví dụ: "1990-08-15"  →  "15081990"
     */
    protected function buildPassword(string $dateYmd): string
    {
        return Carbon::createFromFormat('Y-m-d', $dateYmd)->format('dmY');
    }

    protected function normalizeLecturerCode(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }

    protected function normalizeEmail(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    /**
     * Chuẩn hóa ngày sinh về Y-m-d, hỗ trợ nhiều định dạng đầu vào.
     */
    protected function normalizeDate(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            throw new RowImportFailedException('Ngày sinh không được để trống.');
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->toDateString();
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            throw new RowImportFailedException("Ngày sinh '{$raw}' không hợp lệ.");
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $success = number_format($import->successful_rows);
        $failed  = $import->getFailedRowsCount();

        $body = "Import giảng viên hoàn tất: {$success} thành công.";

        if ($failed > 0) {
            $body .= " {$failed} thất bại - tải file lỗi để xem chi tiết.";
        }

        return $body;
    }
}