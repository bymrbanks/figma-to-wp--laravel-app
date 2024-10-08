<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectApiKey;
use App\Services\ThemeJson;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;
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
            'pages' => 'nullable|json',
        ]);

        try {
            $user = $request->user();
            $project = Project::where('file_id', $validatedData['file_id'])->first();

            if (!$project) {
                // Create a new project if it doesn't exist
                $project = new Project();
                $project->user_id = $user->id;
                $project->file_id = $validatedData['file_id'];
            }

            // Update project properties
            $project->name = $validatedData['name'];
            $project->description = $validatedData['description'] ?? '';
            $project->variables = $validatedData['variables'] ?? '[]';  // Store as-is, it's already JSON
            $project->patterns = $validatedData['patterns'] ?? '[]';
            $project->templates = $validatedData['templates'] ?? '[]';
            $project->elements = $validatedData['elements'] ?? '[]';
            $project->parts = $validatedData['parts'] ?? '[]';
            $project->cover = $validatedData['cover'] ?? '';
            $project->pages = $validatedData['pages'] ?? '[]';

            // Update the themejson
            $themeJson = new ThemeJson($project);
            $project->themejson = json_encode($themeJson->setThemeData());

            $project->save();

            // Generate and assign a new API key if it doesn't exist
            $projectApiKey = ProjectApiKey::where('project_id', $project->id)->first();
            if (!$projectApiKey) {
                $projectApiKey = ProjectApiKey::generateForProject($project->id);
                $projectApiKey->save();
            }

            return response()->json(['message' => 'Project saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while saving the project', 'message' => $e->getMessage()], 500);
        }
    }
 
    public function upload(Request $request)
    {
        try {
            // Validate the request
            $request->validate(
                [
                    'images' => 'required|array',
                    'images.*.image' => 'required|string',
                    'images.*.filename' => 'required|string',
                    'images.*.type' => 'required|string',
                    'images.*.file_id' => 'required|string',
                ]
            );

            // Initialize an array to hold the URLs of the uploaded images
            $imageUrls = [];
            $responses = [];

            // Iterate over each image in the incoming data
            foreach ($request->input('images') as $imageData) {
                
                $decodedImageData = base64_decode($imageData['image']);
                $project = Project::where('file_id', $imageData['file_id'])->first();
                $filePath = $project->id . '/images/' . $imageData['id'];

                // Temporarily save the image data to a file
                $tempFilePath = sys_get_temp_dir() . '/' . uniqid() . '.' . pathinfo($imageData['filename'], PATHINFO_EXTENSION);
                file_put_contents($tempFilePath, $decodedImageData);

                // Get the file content
                $fileContent = file_get_contents($tempFilePath);

                // Determine the MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tempFilePath);
                finfo_close($finfo);

                // Upload the image to Supabase with the correct MIME type
                $response = $this->supabase->uploadImage('projects', $filePath, $fileContent, $mimeType);

                // Log the response
                Log::info('Supabase upload response:', ['response' => $response]);

                // Delete the temporary file
                unlink($tempFilePath);

                // Collect the image URL and response
                if ($response['success']) {
                    $imageUrls[] = $filePath;
                }
                $responses[] = $response;
            }

            // Return both the successful image URLs and all responses
            return response()->json([
                'urls' => $imageUrls,
                'responses' => $responses
            ], 200);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Upload error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function getImages(Request $request)
    {
        // Validate the request
        $request->validate([
            'filenames' => 'required|array',
            'filenames.*' => 'required|string'
        ]);

        $filenames = $request->input('filenames');

        // Get the current project
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Prepare the file paths
        $filePaths = array_map(function($filename) use ($project) {
            return $project->id . '/images/' . $filename;
        }, $filenames);
 
        try {
            // Get the image URLs
            $urls = $this->supabase->getImageUrls('projects', $filePaths);

            // Prepare the response
            $response = array_combine($filenames, $urls);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve image URLs', 'message' => $e->getMessage()], 500);
        }
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

        $patterns = $project->patterns ?? '[]';
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

        $parts = $project->parts ?? '[]';
        $data = json_decode($parts);

        return response()->json($data, 200);
    }

    public function getTemplates(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $templates = $project->templates ?? '[]';
        // ensure the template is a string
        $templates = (string)$templates;

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

    public function getPages(Request $request)
    {
        $project = $request->attributes->get('project');
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $pages = $project->pages ?? '[]';
        $data = json_decode($pages);

        return response()->json($data, 200);
    }

    public function checkImageExistence(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'image_urls' => 'required|array',
                'image_urls.*' => 'required|string',
                'project_id' => 'required|string',
            ]);

            $imageUrls = $request->input('image_urls');

            $project = Project::where('file_id', $request->input('project_id'))->first();

            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }

            // Prepare the file paths
            $filePaths = array_map(function($url) use ($project) {
                $filename = basename($url);
                return $project->id . '/images/' . $filename;
            }, $imageUrls);

            // Check file existence in Supabase using signed URLs
            $existenceResults = $this->supabase->checkImagesExist('projects', $filePaths);
        
            // Filter out the URLs where the error is not null
            $nonExistentUrls = array_filter($existenceResults, function($result) {
                return $result['error'] !== null;
            });

            // Extract only the paths from the filtered results
            $nonExistentPaths = array_map(function($result) {
                return basename($result['path']);
            }, $nonExistentUrls);

            return response()->json([
                'non_existent_urls' =>  array_values($nonExistentPaths)
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking image existence: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}