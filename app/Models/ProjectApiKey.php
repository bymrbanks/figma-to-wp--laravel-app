<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectApiKey extends Model
{
    use HasFactory;

    // Assuming your table columns are 'id', 'project_id', and 'api_key'
    protected $fillable = ['project_id', 'api_key'];

    /**
     * Generate and save a new API key for a project.
     *
     * @param  int  $projectId
     * @return ProjectApiKey
     */
    public static function generateForProject($projectId)
    {
        $apiKey = new self();
        $apiKey->project_id = $projectId;
        $apiKey->api_key = Str::random(32); // Generate a 32 character long random string
        $apiKey->save();

        return $apiKey;
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
