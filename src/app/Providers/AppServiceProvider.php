<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\File;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\CourseSectionPolicy;
use App\Policies\ExamPolicy;
use App\Policies\FilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
    }
}
