<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectApiKey;
use App\Services\ThemeJson;

class ProjectController extends Controller
{
    public function store(Request $request)
    {

        // Validation
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'file_id' => 'required|string',
            'variables' => 'nullable|json',
            'patterns' => 'nullable|json',
            'templates' => 'nullable|json',
            'elements' => 'nullable|json',
            'parts' => 'nullable|json',
            'themejson' => 'nullable|json',
        ]);

        try {
            $user = $request->user();
            $project = Project::where('file_id', $validatedData['file_id'])->first();

            if ($project) {
                // If a project with the file_id exists, update it
                $project->name = $validatedData['name'];
                $project->description = $validatedData['description'] ?? '';
                $project->variables = $validatedData['variables'] ?? [];
                $project->patterns = $validatedData['patterns'] ?? [];
                $project->templates = $validatedData['templates'] ?? [];
                $project->elements = $validatedData['elements'] ?? [];
                $project->parts = $validatedData['parts'] ?? [];
                

                // Update the themejson
                $themeJson = new ThemeJson($project);
                $project->themejson = $themeJson->setThemeData($project);

                // Continue updating other fields...
                $project->save();

                // Generate and assign a new API key
                $projectApiKey = ProjectApiKey::generateForProject($project->id);
                if ($projectApiKey) {
                    $projectApiKey->save();
                }

            } else {
                // Create a new project
                $project = new Project();
                $project->user_id = $user->id;
                $project->name = $validatedData['name'];
                $project->description = $validatedData['description'] ?? '';
                $project->file_id = $validatedData['file_id'];
                $project->variables = $validatedData['variables'] ?? [];
                $project->patterns = $validatedData['patterns'] ?? [];

                // Update the themejson
                $themeJson = new ThemeJson($project);
                $project->themejson = $themeJson->setThemeData();

                // Continue setting other fields...
                $project->save();

                // Generate and assign a new API key
                $projectApiKey = ProjectApiKey::generateForProject($project->id);
                $projectApiKey->save();
            }

            return response()->json(['message' => 'Project saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while saving the project'], 500);
        }
    }

    protected function validateApiKey($key)
    {
        // Implement your logic to validate the API key
        // For example, check if the key exists in your database
    }

    // create a function to get the project by api key
    public function getThemeJson(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $themeJson = new ThemeJson($project);
        $data = $themeJson->setThemeData();
        
        return response()->json($data, 200);
    }


    // create a function to get the project by api key
    public function getPatterns(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $patterns = $project->patterns ?? [];
        $data = json_decode($patterns);
        
        return response()->json($data, 200);
    }

    // create a function to get the project by api key
    public function getParts(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $parts = $project->parts ?? [];
        $data = json_decode($parts);
        
        return response()->json($data, 200);
    }
}
