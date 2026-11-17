<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Services\FractionalIndexingService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@delox.test'],
            ['name' => 'Delox Demo', 'password' => 'password'],
        );

        $workspace = Workspace::query()->firstOrCreate(
            ['slug' => 'delox-demo'],
            ['name' => 'Delox Demo Workspace', 'owner_id' => $user->getKey()],
        );

        $project = Project::query()->firstOrCreate(
            ['workspace_id' => $workspace->getKey(), 'key' => 'DLX'],
            ['name' => 'Delox Project', 'description' => 'Sample project for exploring Delox.'],
        );

        $indexing = new FractionalIndexingService;
        $columnNames = ['Backlog', 'In Progress', 'In Review', 'Done'];
        $columns = [];
        $previousPosition = null;

        foreach ($columnNames as $columnName) {
            $position = $indexing->generateKeyBetween($previousPosition, null);
            $columns[$columnName] = Column::query()->firstOrCreate(
                ['project_id' => $project->getKey(), 'name' => $columnName],
                ['position' => $position],
            );
            $previousPosition = $position;
        }

        $sampleTasks = [
            'Backlog' => ['Design task workflow', 'Write onboarding copy'],
            'In Progress' => ['Build project board', 'Add task creation flow'],
            'In Review' => ['Review accessibility states'],
            'Done' => ['Set up project foundation'],
        ];

        foreach ($sampleTasks as $columnName => $titles) {
            $previousTaskPosition = null;

            foreach ($titles as $title) {
                $position = $indexing->generateKeyBetween($previousTaskPosition, null);
                Task::query()->firstOrCreate(
                    ['project_id' => $project->getKey(), 'title' => $title],
                    [
                        'column_id' => $columns[$columnName]->getKey(),
                        'creator_id' => $user->getKey(),
                        'priority' => 'medium',
                        'position' => $position,
                    ],
                );
                $previousTaskPosition = $position;
            }
        }
    }
}
