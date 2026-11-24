<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /**
     * GET /api/projects/{project}/tasks?status=todo&priority=high
     */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('priority'), fn ($query, $priority) => $query->where('priority', $priority))
            ->orderBy('id')
            ->paginate(min(max((int) $request->query('per_page', 15), 1), 100))
            ->withQueryString();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $task = $project->tasks()->create($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task): Response
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
