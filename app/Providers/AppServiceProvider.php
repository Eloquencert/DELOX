<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Policies\ColumnPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
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
        Gate::policy(Workspace::class, WorkspacePolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Column::class, ColumnPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
