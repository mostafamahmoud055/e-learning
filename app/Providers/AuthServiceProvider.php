<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\ModulePolicy;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        Module::class => ModulePolicy::class,
        Lesson::class => LessonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {

        $this->registerPolicies();

        Gate::define('create-update-delete-assign-module-lesson-assessment-question', function ($user, $course) {
            return $user->role == 'teacher' && $user->id == $course->user_id;
        });


        Gate::define('delete-resource', function ($user, $resource) {
            $course_id = $resource->resourceable->course_id;
            $course = Course::find($course_id);
            return $user->id == $course->user_id;
        });

        Gate::define('student-search', function ($user) {
            return $user->role == 'teacher' || Auth::id() == $user->id;
        });

        // VerifyEmail::toMailUsing (function ($notifiable, $url) {

        //     return (new MailMessage)
        //     ->subject('Verify Email Address')
        //     ->line('Click the button below to verify your email address.')
        //     ->action('Verify Email Address', 'http://127.0.0.1:8000/');
        //     });
    }
}
