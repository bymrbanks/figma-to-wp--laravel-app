<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'file_id' => 'required|string',
        ]);

        try {
            // Create and save the project

            // Attempt to find a project with the given file_id
            $existingProject = Project::where('file_id', $validatedData['file_id'])->first();
            
            $user = $request->user();
            
            if ($existingProject) {
                // If a project with the file_id exists, update it
                $existingProject->name = $validatedData['name'];
                $existingProject->description = $validatedData['description'];
                $existingProject->last_imported_date = now();
                $existingProject->save();
            
                // Return a response indicating the project was updated
                return response()->json(['message' => 'Project updated successfully'], 200);
            } else {
                // If no project exists with the file_id, create a new one
                $project = new Project();
                $project->user_id = $user->id;
                $project->name = $validatedData['name'];
                $project->description = $validatedData['description'];
                $project->file_id = $validatedData['file_id'];
                $project->last_imported_date = now();
                $project->save();
            
                // Return a response indicating the project was created
                return response()->json(['message' => 'Project created successfully'], 201);
            }
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Project creation failed: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => 'Failed to create the project.'], 500);
        }
    }
}
