<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    /**
     * GET /api/projects?status=active&search=site&per_page=15
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = $request->user()->projects()
            ->withCount('tasks')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->latest('id')
            ->paginate(min(max((int) $request->query('per_page', 15), 1), 100))
            ->withQueryString();

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $request->user()->projects()->create($request->validated());

        return (new ProjectResource($project->loadCount('tasks')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load('tasks')->loadCount('tasks'));
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $project->update($request->validated());

        return new ProjectResource($project->loadCount('tasks'));
    }

    public function destroy(Project $project): Response
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
