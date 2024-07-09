<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'file_id' => 'nullable|string',
        ]);

        $project = new Project();
        $project->name = $request->name;
        $project->user_id = $request->user->id;
        // set other fields
        $project->save();

        return response()->json($project, 201);
    }
}
