<?php

namespace Tests\Feature\Lecturer;

use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CourseSectionAjaxTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;
    private Subject $subject;
    private Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $this->lecturer = User::factory()->create([
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->lecturer->assignRole($lecturerRole);

        $this->subject = Subject::create([
            'code' => 'CS101',
            'name' => 'Lập trình Java',
            'credits' => 3,
        ]);

        $this->semester = Semester::create([
            'name' => 'HK1 2025-2026',
            'year' => 2025,
            'term' => 1,
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
        ]);
    }

    // ── STORE: AJAX (wantsJson) ─────────────────────────────────

    public function test_store_returns_json_with_html_when_called_via_ajax(): void
    {
        $data = [
            'name' => 'Lập trình Java — Nhóm 1',
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'max_students' => 50,
        ];

        $response = $this->actingAs($this->lecturer)
            ->postJson(route('lecturer.classes.store'), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'html'])
            ->assertJson(['success' => true]);

        // Verify the section was created in DB
        $this->assertDatabaseHas('course_sections', [
            'name' => 'Lập trình Java — Nhóm 1',
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'lecturer_id' => $this->lecturer->id,
        ]);

        // Verify HTML contains the class name
        $html = $response->json('html');
        $this->assertStringContainsString('Lập trình Java — Nhóm 1', $html);
    }

    public function test_store_returns_422_with_validation_errors_via_ajax(): void
    {
        $data = [
            // Missing 'name', 'subject_id', 'semester_id'
        ];

        $response = $this->actingAs($this->lecturer)
            ->postJson(route('lecturer.classes.store'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'subject_id', 'semester_id']);
    }

    public function test_store_redirects_when_not_ajax(): void
    {
        $data = [
            'name' => 'Lập trình C — Nhóm 2',
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
        ];

        $response = $this->actingAs($this->lecturer)
            ->post(route('lecturer.classes.store'), $data);

        // Traditional form submit → redirect (not JSON)
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // ── UPDATE: AJAX (wantsJson) ────────────────────────────────

    public function test_update_returns_json_with_html_when_called_via_ajax(): void
    {
        $section = CourseSection::create([
            'name' => 'Lớp cũ',
            'code' => 'CS101-01-HK1-2526',
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'lecturer_id' => $this->lecturer->id,
            'invite_code' => 'ABC123',
            'max_students' => 100,
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Lớp mới đã sửa',
            'max_students' => 80,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->lecturer)
            ->putJson(route('lecturer.classes.update', $section), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'html'])
            ->assertJson(['success' => true]);

        // Verify the section was updated in DB
        $this->assertDatabaseHas('course_sections', [
            'id' => $section->id,
            'name' => 'Lớp mới đã sửa',
            'max_students' => 80,
        ]);
    }

    public function test_update_redirects_when_not_ajax(): void
    {
        $section = CourseSection::create([
            'name' => 'Lớp cũ',
            'code' => 'CS101-02-HK1-2526',
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'lecturer_id' => $this->lecturer->id,
            'invite_code' => 'DEF456',
            'max_students' => 100,
            'status' => 'active',
        ]);

        $data = [
            'name' => 'Lớp cập nhật',
            'max_students' => 60,
            'status' => 'archived',
        ];

        $response = $this->actingAs($this->lecturer)
            ->put(route('lecturer.classes.update', $section), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // ── INDEX: Loads data for slide-over ────────────────────────

    public function test_index_loads_subjects_and_semesters_for_slide_over(): void
    {
        $response = $this->actingAs($this->lecturer)
            ->get(route('lecturer.classes.index'));

        $response->assertStatus(200);
        $response->assertViewHas('subjects');
        $response->assertViewHas('semesters');
        $response->assertViewHas('sections');
    }
}
