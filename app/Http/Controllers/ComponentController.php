<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Component;
use App\Http\Resources\ComponentResource;

class ComponentController extends Controller
{
    /**
     * Retrieve a list of all components.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $components = Component::all();

        return response()->json([
            'success' => true,
            'data' => ComponentResource::collection($components),
        ]);
    }

    /**
     * Retrieve a single component by its slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            return response()->json([
                'success' => false,
                'message' => 'Component not found.',
            ], 404);
        } 

        return response()->json([
            'success' => true,
            'data' => new ComponentResource($component),
        ]);
    }
}