<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ProjectApiKey;
use App\Models\Project;

class ValidateApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $projectApiKey = $request->header('X-Figport-Token');

        $project = $this->validateApiKey($projectApiKey);

        if (!$project) {
            return response()->json(['error' => 'API Key is missing'], 401);
        }

        // Attach the project to the request
        $request->attributes->add(['project' => $project]);


        return $next($request);
    }

    protected function validateApiKey($key)
    {

        $projectKey = ProjectApiKey::where('api_key', $key)->first();

        if (!$projectKey->exists()) {
            return null;
        }

        // Assuming $projectKey->project_id contains the ID of the project you're looking for
        $projectId = $projectKey->project_id;

        // Use the Project model to find the project by its ID
        $project = Project::find($projectId)->first();

        if (!$project) {
            return null;
        }

        return $project;
    }
}
