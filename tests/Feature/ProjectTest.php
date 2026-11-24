<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_projects(): void
    {
        $this->getJson('/api/projects')->assertUnauthorized();
    }

    public function test_user_only_sees_their_own_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(2)->for($user)->create();
        Project::factory()->create(); // belongs to somebody else

        Sanctum::actingAs($user);

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_projects_can_be_filtered_by_status_and_search(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user)->create(['name' => 'Website redesign']);
        Project::factory()->for($user)->archived()->create(['name' => 'Old blog']);

        Sanctum::actingAs($user);

        $this->getJson('/api/projects?status=archived')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/projects?search=website')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_a_project(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/projects', [
            'name' => 'Website',
            'description' => 'New marketing site',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Website')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.tasks_count', 0);

        $this->assertDatabaseHas('projects', ['name' => 'Website']);
    }

    public function test_project_creation_is_validated(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/projects', ['status' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_show_includes_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Task::factory()->count(3)->for($project)->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/projects/{$project->id}")
            ->assertOk()
            ->assertJsonCount(3, 'data.tasks')
            ->assertJsonPath('data.tasks_count', 3);
    }

    public function test_user_cannot_view_someone_elses_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/projects/{$project->id}")->assertForbidden();
    }

    public function test_user_can_update_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->patchJson("/api/projects/{$project->id}", ['name' => 'Renamed', 'status' => 'archived'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed')
            ->assertJsonPath('data.status', 'archived');
    }

    public function test_user_cannot_update_someone_elses_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/projects/{$project->id}", ['name' => 'Hacked'])->assertForbidden();
    }

    public function test_user_can_delete_their_project_and_its_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Task::factory()->count(2)->for($project)->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/projects/{$project->id}")->assertNoContent();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_missing_project_returns_404_json(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/projects/999')->assertNotFound();
    }
}
