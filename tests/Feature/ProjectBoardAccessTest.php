<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectBoardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_can_open_the_board_and_authorize_its_private_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();

        $this->actingAs($workspace->owner)
            ->get(route('projects.board', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Board')
                ->where('project.id', $project->getKey()));

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-project.'.$project->getKey(),
        ])->assertOk();
    }

    public function test_outsider_cannot_open_or_subscribe_to_a_project_board(): void
    {
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => '12345',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
            'broadcasting.connections.pusher.options.useTLS' => false,
        ]);

        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('projects.board', $project))
            ->assertForbidden();

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-project.'.$project->getKey(),
        ])->assertForbidden();
    }
}
