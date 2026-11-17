<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => null,
            'column_id' => Column::factory(),
            'creator_id' => User::factory(),
            'assignee_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'position' => fake()->unique()->lexify('a????'),
            'due_date' => fake()->optional()->date(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Task $task): void {
            if ($task->project_id === null) {
                $task->project_id = Column::query()
                    ->findOrFail($task->column_id)
                    ->project_id;
            }
        });
    }

    public function forProject(Project $project, ?Column $column = null): static
    {
        $column ??= $project->columns()->first()
            ?? Column::factory()->for($project)->create([
                'name' => 'Backlog',
                'position' => 'a0',
            ]);

        if ((int) $column->project_id !== (int) $project->getKey()) {
            throw new InvalidArgumentException('The task column must belong to the given project.');
        }

        return $this->state([
            'project_id' => $project->getKey(),
            'column_id' => $column->getKey(),
        ]);
    }
}
