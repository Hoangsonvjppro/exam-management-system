<?php

namespace Tests\Feature\Lecturer;

use App\Models\CourseSection;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ExamQuickCreateTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;
    private Subject $subject;
    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::query()->firstOrCreate([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $this->lecturer = User::factory()->create([
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->lecturer->assignRole('lecturer');

        $this->subject = Subject::create([
            'code' => 'IT999',
            'name' => 'Mon test tao de nhanh',
            'credits' => 3,
        ]);

        $semester = Semester::create([
            'name' => 'HK1 2026-2027',
            'year' => 2026,
            'term' => 1,
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
        ]);

        CourseSection::create([
            'name' => 'Lop test tao de nhanh',
            'code' => 'IT999-01-HK1-2627',
            'invite_code' => 'QKCRT1',
            'subject_id' => $this->subject->id,
            'semester_id' => $semester->id,
            'lecturer_id' => $this->lecturer->id,
            'max_students' => 60,
            'status' => 'active',
        ]);

        $singleChoiceType = QuestionType::create([
            'code' => 'single_choice',
            'name' => 'Mot dap an',
            'description' => 'Chi mot dap an dung',
            'answer_schema' => ['type' => 'single_choice'],
            'is_auto_grade' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->question = Question::create([
            'subject_id' => $this->subject->id,
            'chapter_id' => null,
            'question_type_id' => $singleChoiceType->id,
            'created_by' => $this->lecturer->id,
            'content' => '2 + 2 bang bao nhieu?',
            'difficulty' => 'remember',
            'answer_data' => null,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'label' => 'A',
            'content' => '3',
            'is_correct' => false,
            'order' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'label' => 'B',
            'content' => '4',
            'is_correct' => true,
            'order' => 2,
        ]);
    }

    public function test_store_returns_json_payload_for_quick_create_and_defaults_scoring_method(): void
    {
        $payload = $this->validManualExamPayload();
        unset($payload['multiple_choice_scoring_method']);

        $response = $this->actingAs($this->lecturer)
            ->postJson(route('lecturer.exams.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('exam.subject_id', $this->subject->id)
            ->assertJsonStructure([
                'success',
                'message',
                'exam' => [
                    'id',
                    'title',
                    'subject_id',
                    'subject_code',
                    'show_url',
                    'preview_url',
                    'quick_update_url',
                    'edit_url',
                ],
            ]);

        $examId = (int) $response->json('exam.id');

        $this->assertDatabaseHas('exams', [
            'id' => $examId,
            'title' => $payload['title'],
            'subject_id' => $this->subject->id,
            'created_by' => $this->lecturer->id,
            'multiple_choice_scoring_method' => 'all_or_nothing',
        ]);

        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $examId,
            'question_id' => $this->question->id,
        ]);
    }

    public function test_store_returns_422_when_manual_mode_has_no_questions(): void
    {
        $payload = $this->validManualExamPayload([
            'question_ids' => [],
        ]);

        $response = $this->actingAs($this->lecturer)
            ->postJson(route('lecturer.exams.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['question_ids']);
    }

    public function test_store_returns_json_runtime_error_when_service_throws_exception(): void
    {
        $payload = $this->validManualExamPayload();

        $examServiceMock = Mockery::mock(ExamService::class);
        $examServiceMock->shouldReceive('createExam')
            ->once()
            ->andThrow(new \RuntimeException('Khong the tao de thi nhanh.'));
        $examServiceMock->shouldReceive('createExamFromMatrix')->never();

        $this->app->instance(ExamService::class, $examServiceMock);

        $response = $this->actingAs($this->lecturer)
            ->postJson(route('lecturer.exams.store'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Khong the tao de thi nhanh.',
            ]);

        $this->assertDatabaseCount('exams', 0);
    }

    private function validManualExamPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'De thi tao nhanh 15p',
            'subject_id' => $this->subject->id,
            'description' => 'De tao tu quick modal',
            'duration_minutes' => 15,
            'exam_type' => 'practice',
            'show_score_after_submit' => true,
            'show_answers_after_submit' => false,
            'allow_late_entrance' => true,
            'late_entrance_limit_minutes' => null,
            'late_entrance_behavior' => 'fixed_end',
            'min_duration_before_submit' => 0,
            'creation_mode' => 'manual',
            'multiple_choice_scoring_method' => 'all_or_nothing',
            'question_ids' => [$this->question->id],
        ], $overrides);
    }
}
