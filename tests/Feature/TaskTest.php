<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_task_in_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Write the docs',
            'priority' => 'high',
            'due_date' => '2030-01-15',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Write the docs')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.status', 'todo')
            ->assertJsonPath('data.due_date', '2030-01-15');
    }

    public function test_user_cannot_add_tasks_to_someone_elses_project(): void
    {
        $project = Project::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/projects/{$project->id}/tasks", ['title' => 'Nope'])->assertForbidden();
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_creation_is_validated(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/projects/{$project->id}/tasks", ['status' => 'blocked'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'status']);
    }

    public function test_tasks_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Task::factory()->for($project)->create(['status' => 'todo']);
        Task::factory()->for($project)->create(['status' => 'done']);
        Task::factory()->for($project)->create(['status' => 'done']);

        Sanctum::actingAs($user);

        $this->getJson("/api/projects/{$project->id}/tasks?status=done")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_update_a_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for(Project::factory()->for($user))->create(['status' => 'todo']);

        Sanctum::actingAs($user);

        $this->patchJson("/api/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');
    }

    public function test_user_cannot_touch_someone_elses_task(): void
    {
        $task = Task::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/tasks/{$task->id}")->assertForbidden();
        $this->patchJson("/api/tasks/{$task->id}", ['status' => 'done'])->assertForbidden();
        $this->deleteJson("/api/tasks/{$task->id}")->assertForbidden();
    }

    public function test_user_can_delete_a_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for(Project::factory()->for($user))->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/tasks/{$task->id}")->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
