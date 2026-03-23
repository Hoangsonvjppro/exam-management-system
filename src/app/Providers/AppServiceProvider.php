<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\File;
use App\Models\Difficulty;
use App\Models\Notification;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Tag;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\CourseSectionPolicy;
use App\Policies\DifficultyPolicy;
use App\Policies\ExamPolicy;
use App\Policies\FilePolicy;
use App\Policies\QuestionPolicy;
use App\Policies\QuestionTypePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Admin::class, AdminPolicy::class);
        Gate::policy(CourseSection::class, CourseSectionPolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(File::class, FilePolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(Difficulty::class, DifficultyPolicy::class);
        Gate::policy(QuestionType::class, QuestionTypePolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);

        View::composer('layouts.app', function ($view): void {
            $user = Auth::user();
            $unreadNotificationCount = 0;

            if ($user) {
                $unreadNotificationCount = Notification::where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();
            }

            $view->with('unreadNotificationCount', $unreadNotificationCount);
        });
    }
}
