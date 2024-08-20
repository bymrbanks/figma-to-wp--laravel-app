<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectApiKey;
use App\Services\ThemeJson;
use App\Services\SupabaseService;

class ProjectController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

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
            'cover' => 'nullable|string',
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
                $project->cover = $validatedData['cover'] ?? '';



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
                $project->templates = $validatedData['templates'] ?? [];
                $project->elements = $validatedData['elements'] ?? [];
                $project->parts = $validatedData['parts'] ?? [];
                $project->cover = $validatedData['cover'] ?? '';

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



    public function upload(Request $request)
    {

        try {
            // Validate the request
            $request->validate([
                'image' => 'required|string',
                'filename' => 'required|string',
                'type' => 'required|string',
                'file_id' => 'required|string',
            ]);

            // Decode the Base64 image
            $imageData = base64_decode($request->input('image'));
            $project = Project::where('file_id', $request->input('file_id'))->first();
            $filePath = $project->id . '/images/' . $request->input('filename') . '.' . $request->input('type');


            // Temporarily save the image data to a file
            $tempFilePath = sys_get_temp_dir() . '/' . uniqid() . '.' . pathinfo($request->input('filename'), PATHINFO_EXTENSION);
            file_put_contents($tempFilePath, $imageData);

            // Get the file content
            $fileContent = file_get_contents($tempFilePath);

            // Determine the MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tempFilePath);
            finfo_close($finfo);

            // Upload the image to Supabase with the correct MIME type
            $this->supabase->uploadImage('projects', $filePath, $fileContent, $mimeType);


            // Delete the temporary file
            unlink($tempFilePath);


            // Return the image URL in the response
            return response()->json(['url' => $this->supabase->getImageUrl('projects', $filePath)], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle HTTP request errors
            return response()->json(['error' => 'Failed to upload image' . $e->getMessage(), 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function getImage($filename, Request $request)
    {
        if (!$filename) {
            return response()->json(['error' => 'Filename not provided'], 400);
        }


        // return response()->json(['url' => "cover.png"], 200);
        // get the current project
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $filePath = $project->id . '/images/' . $filename;

        $url = $this->supabase->getImageUrl('projects', $filePath);
        $url = utf8_encode($url);
        return response()->json(['url' => $url]);
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

    public function getTemplates(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $templates = $project->templates ?? [];
        $data = json_decode($templates);

        return response()->json($data, 200);
    }


    // create a function to get the project by api key
    public function getThemeData(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $parts = $project->variables ?? [];
        $data = json_decode($parts);

        return response()->json($data, 200);
    }
}
