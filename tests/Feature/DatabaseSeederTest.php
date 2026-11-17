<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Workspace;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_demo_project_with_the_expected_board(): void
    {
        $this->seed(DatabaseSeeder::class);

        $workspace = Workspace::query()->where('slug', 'delox-demo')->firstOrFail();
        $project = Project::query()->where('key', 'DLX')->firstOrFail();

        $this->assertSame(
            ['Backlog', 'In Progress', 'In Review', 'Done'],
            $project->columns()->pluck('name')->all(),
        );
        $this->assertSame($workspace->getKey(), $project->workspace_id);
        $this->assertDatabaseCount('tasks', 6);
    }
}
