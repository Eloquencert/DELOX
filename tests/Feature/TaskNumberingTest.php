<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_numbers_increment_per_project(): void
    {
        $project = Project::factory()->create();
        $column = Column::factory()->for($project)->create();
        $anotherProject = Project::factory()->create();
        $anotherColumn = Column::factory()->for($anotherProject)->create();

        $firstTask = Task::factory()->forProject($project, $column)->create();
        $secondTask = Task::factory()->forProject($project, $column)->create();
        $firstTaskInAnotherProject = Task::factory()
            ->forProject($anotherProject, $anotherColumn)
            ->create();

        $this->assertSame(1, $firstTask->number);
        $this->assertSame(2, $secondTask->number);
        $this->assertSame(1, $firstTaskInAnotherProject->number);
    }
}
