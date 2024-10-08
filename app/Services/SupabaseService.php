<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected $client;
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_KEY');
        $this->client = new Client([
            'base_uri' => $this->supabaseUrl,
            'headers' => [
                'apikey' => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
            ],
        ]);
    }

    public function uploadImage($bucket, $filePath, $fileContent, $mimeType)
    {
        try {
            // Ensure the MIME type is a supported image format
            $supportedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            $mimeType = in_array($mimeType, $supportedMimeTypes) ? $mimeType : 'image/png';

            $response = $this->client->post("/storage/v1/object/$bucket/$filePath", [
                'body' => $fileContent,
                'headers' => [
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'false'
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'error' => 'Failed to upload or update the file: ' . $response->getBody()->getContents()
                ];
            }

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (\Exception $e) {
            Log::error('Supabase upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }



    public function getSignUrls($bucket, array $paths, $expiresIn = 180)
    {
        $body = [
            'paths' => $paths,
            'expiresIn' => $expiresIn,
        ];

        $response = $this->client->post("{$this->supabaseUrl}/storage/v1/object/sign/{$bucket}", [
            'json' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->supabaseKey,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        if ($statusCode === 200) {
            $data = json_decode($responseBody, true);
            if (isset($data)) {
                return $data;
            } else {
                throw new \Exception('Failed to get signed URLs. Response body: ' . $responseBody);
            }
        } else {
            throw new \Exception('Failed to get signed URLs. Status code: ' . $statusCode . '. Response body: ' . $responseBody);
        }
    }

    public function getImageUrls($bucket, array $paths)
    {
        try {
            // Step 1: Get the pre-signed URLs
            $signedUrls = $this->getSignUrls($bucket, $paths);

            // Step 2: Manipulate the data
            $result = [];
            foreach ($signedUrls as $index => $url) {
                if (!isset($url['error'])) {
                    $result[$index] = [
                        'error' => null,
                        'path' => $paths[$index],
                        'signedURL' => $this->supabaseUrl . '/storage/v1'. $url['signedURL']
                    ];
                } else {
                    $result[$index] = [
                        'error' => $url['error'],
                        'path' => $paths[$index],
                        'signedURL' => null
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }

    public function checkImagesExist($bucket, array $filePaths)
    {
        try {
            $signedUrls = $this->getSignUrls($bucket, $filePaths);
            
            return $signedUrls;
            $results = [];
            foreach ($signedUrls as $path => $url) {
                $results[$path] = !isset($url['error']);
            }
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Error checking image existence: ' . $e->getMessage());
            throw $e;
        }
    }
}
