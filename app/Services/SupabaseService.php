<?php

namespace App\Services;

use GuzzleHttp\Client;

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
        $response = $this->client->post("/storage/v1/object/$bucket/$filePath", [
            'body' => $fileContent,
            'headers' => [
                'Content-Type' => $mimeType,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    // public function getImageUrl($bucket, $filePath)
    // {
    //     $expiryTimeInSeconds = 3600; // Set the expiry time as needed

    //     $url = "{$this->supabaseUrl}/storage/v1/object/{$bucket}/{$filePath}";

    //     $client = new \GuzzleHttp\Client();

    //     try {
    //         $response = $client->request('GET', $url, [
    //             'headers' => [
    //                 'apikey' => $this->supabaseKey,
    //             ],
    //         ]);

    //         $data = json_decode($response->getBody(), true);

    //         if (isset($data['signedURL'])) {
    //             return $data['signedURL'];
    //         } else {
    //             throw new \Exception('Failed to get signed URL');
    //         }
    //     } catch (\Exception $e) {
    //         // Handle any errors
    //         throw new \Exception('Error fetching signed URL: ' . $e->getMessage());
    //     }
    // }


    public function getSignUrl($bucket, $path)
    {
        $body = [
            'bucket' => $bucket,
            'path' => $path,
            'expiresIn' => 36000, // Set the expiry time as needed
        ];
    
        $response = $this->client->post("{$this->supabaseUrl}/storage/v1/object/sign/{$bucket}/{$path}", [
            'json' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->supabaseKey,
            ],
        ]);
    
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody(), true);
            if (isset($data['signedURL'])) {
                return $data['signedURL'];
            } else {
                throw new \Exception('Failed to get signed URL');
            }
        } else {
            throw new \Exception('Failed to get signed URL: ' . $response->getBody()->getContents());
        }
    }
    
    public function getImageUrl($bucket, $path)
    {
        try {
            // Step 1: Get the pre-signed URL
            $signedUrlPath = $this->getSignUrl($bucket, $path);
    
            // Step 2: Construct the full URL
            $fullUrl = $this->supabaseUrl . '/storage/v1'.$signedUrlPath;
    
            // Step 3: Make a GET request to fetch the image
            $response = $this->client->get($fullUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->supabaseKey,
                ],
            ]);
    
            if ($response->getStatusCode() === 200) {
                return $fullUrl;
            } else {
                throw new \Exception('Failed to download image: ' . $response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }
}
