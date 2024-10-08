<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ProjectApiKey;
use App\Models\Project;

class ValidateApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-Figport-Token');

        if (!$apiKey) {
            return response()->json(['error' => 'API key is missing {'.$apiKey.'}'], 401);
        }

        $projectApiKey = ProjectApiKey::where('api_key', $apiKey)->first();

        if (!$projectApiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $project = Project::find($projectApiKey->project_id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Add the project to the request attributes
        $request->attributes->add(['project' => $project]);

        return $next($request);
    }
}
