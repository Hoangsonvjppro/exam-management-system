<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Imports\CourseSectionStudentImporter;
use App\Filament\Resources\StudentResource;
use App\Filament\Resources\Students\StudentsResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class ManageStudents extends ManageRecords
{
    protected static string $resource = StudentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm sinh viên')
                ->icon('heroicon-m-plus')
                ->after(function ($record) {
                    $record->assignRole('student');
                }),
            // Import đăng ký sinh viên hàng loạt
            ImportAction::make('import_enrollments')
                ->importer(CourseSectionStudentImporter::class)
                ->label('Import sinh viên đăng ký')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->modalHeading('Import sinh viên đăng ký từ CSV'),
            Action::make('bulkCreatePasswords')
                ->label('Tạo mật khẩu hàng loạt')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận tạo mật khẩu hàng loạt')
                ->modalDescription(new HtmlString(
                    'Hệ thống tạo mật khẩu cho sinh viên chưa có password và đã có ngày sinh.<br><br>'
                        . 'Username: [mã số sinh viên] <br> Password: ngày sinh [ddmmyyyy] (VD: 15032003).'
                ))
                ->modalSubmitActionLabel('Xác nhận tạo')
                ->action(function () {
                    $students = User::query()
                        ->whereNotNull('student_code')
                        ->whereNull('password')
                        ->whereNotNull('date_of_birth')
                        ->get();

                    if ($students->isEmpty()) {
                        Notification::make()
                            ->title('Không có sinh viên nào đủ điều kiện')
                            ->body('Tất cả sinh viên đã có mật khẩu, hoặc chưa có ngày sinh.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $success = 0;
                    $skipped = 0;

                    foreach ($students as $student) {
                        try {
                            $rawPassword = Carbon::parse($student->date_of_birth)->format('dmY');

                            $student->update([
                                'password'             => Hash::make($rawPassword),
                                'must_change_password' => true,
                            ]);

                            $success++;
                        } catch (\Throwable) {
                            $skipped++;
                        }
                    }

                    Notification::make()
                        ->title("Đã tạo mật khẩu cho {$success} sinh viên"
                            . ($skipped > 0 ? " · {$skipped} lỗi" : ''))
                        ->success()
                        ->send();
                }),
        ];
    }
}
