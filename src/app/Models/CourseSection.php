<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CourseSection Model — Lớp học phần.
 *
 * @property int    $id
 * @property string $code          Mã lớp: CS101-01-HK1-2526
 * @property int    $subject_id
 * @property int    $semester_id
 * @property int    $lecturer_id
 * @property int    $max_students
 * @property string $status        active|archived|cancelled
 */
class CourseSection extends Model
{
    protected $fillable = [
        'code',
        'name',
        'invite_code',
        'subject_id',
        'semester_id',
        'lecturer_id',
        'max_students',
        'status',
    ];

    protected function casts(): array
    {
        return ['max_students' => 'integer'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /**
     * Sinh viên đăng ký lớp (N-N qua course_section_students).
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_section_students', 'course_section_id', 'student_id')
            ->withPivot('status', 'enrolled_at')
            ->withTimestamps();
    }

    /**
     * Lịch học chi tiết.
     */
    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOwnedBy(Builder $query, int $lecturerId): Builder
    {
        return $query->where('lecturer_id', $lecturerId);
    }

    public function scopeWithInviteCode(Builder $query, string $code): Builder
    {
        return $query->where('invite_code', $code);
    }

    // ── Helpers ────────────────────────────────────────────────

    // Số sinh viên đang enrolled (không phải dropped)
    public function getEnrolledCountAttribute(): int
    {
        return $this->students()->wherePivot('status', 'enrolled')->count();
    }

    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'course_section_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'course_section_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'course_section_id');
    }

    public function gradeColumns(): HasMany
    {
        return $this->hasMany(GradeColumn::class);
    }

    // ── Import helpers ─────────────────────────────────────────

    public static function findByCode(string $code): ?static
    {
        return static::where('code', trim($code))->first();
    }

    public static function findOrNewByCode(string $code): static
    {
        return static::firstOrNew(['code' => trim($code)]);
    }
    // ── Code Generation ────────────────────────────────────────

    public static function generateCode(int|string|null $subjectId, int|string|null $semesterId): ?string
    {
        if (blank($subjectId) || blank($semesterId)) {
            return null;
        }

        $subject = Subject::find($subjectId);
        $semester = Semester::find($semesterId);

        if (!$subject || !$semester) {
            return null;
        }

        $groupNumber = static::resolveNextMissingGroupNumber((int) $subject->id, (int) $semester->id);

        $termCode = match ((int) $semester->term) {
            1 => 'HK1',
            2 => 'HK2',
            3 => 'HK3',
            default => 'HK' . (int) $semester->term,
        };

        $startYear = (int) $semester->year;
        $yearCode = sprintf('%02d%02d', $startYear % 100, ($startYear + 1) % 100);

        return strtoupper(sprintf(
            '%s-%02d-%s-%s',
            $subject->code,
            $groupNumber,
            $termCode,
            $yearCode,
        ));
    }

    protected static function resolveNextMissingGroupNumber(int $subjectId, int $semesterId): int
    {
        $used = static::query()
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->pluck('code')
            ->map(function ($code) {
                $parts = explode('-', $code);
                return isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $expected = 1;

        foreach ($used as $n) {
            if ($n === $expected) {
                $expected++;
                continue;
            }
            if ($n > $expected) {
                break;
            }
        }

        return $expected;
    }
}
