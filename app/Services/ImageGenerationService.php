<?php
// app/Services/ImageGenerationService.php - Updated to use Emergent Universal Key via Python microservice

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ImageGenerationService
{
    private $imageServiceUrl;

    public function __construct()
    {
        // Python image generation microservice URL
        $this->imageServiceUrl = env('IMAGE_SERVICE_URL', 'http://localhost:8002');
        Log::info('ImageGenerationService initialized', [
            'service_url' => $this->imageServiceUrl
        ]);
    }

    public function generateProductImages($genre, $productName, $productDescription)
    {
        Log::info('Starting product image generation via Emergent', [
            'genre' => $genre,
            'product_name' => $productName
        ]);

        try {
            // Call the Python microservice to generate all 3 images
            $response = Http::timeout(300)->post($this->imageServiceUrl . '/generate-product-images', [
                'genre' => $genre,
                'product_name' => $productName,
                'product_description' => $productDescription
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success'] && !empty($data['images'])) {
                    $images = [];
                    
                    foreach ($data['images'] as $type => $base64) {
                        if ($base64) {
                            // Store the image and get URL
                            $storedUrl = $this->storeBase64Image($base64, $productName, $type);
                            $images[$type] = $storedUrl;
                        } else {
                            $images[$type] = $this->getFallbackImageUrl($type);
                        }
                    }
                    
                    Log::info('Generated images successfully via Emergent', ['images' => array_keys($images)]);
                    return $images;
                }
            }
            
            Log::error('Image service call failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return $this->getFallbackImages();

        } catch (Exception $e) {
            Log::error('Image generation failed', [
                'error' => $e->getMessage(),
                'product' => $productName
            ]);
            return $this->getFallbackImages();
        }
    }

    private function storeBase64Image($base64, $productName, $type)
    {
        try {
            $imageContent = base64_decode($base64);
            $filename = $this->generateFilename($productName, $type);
            $path = 'products/' . $filename;
            
            // Store in public disk
            Storage::disk('public')->put($path, $imageContent);
            
            $storedUrl = Storage::url($path);
            
            Log::info('Image stored successfully', [
                'path' => $path,
                'url' => $storedUrl
            ]);
            
            return $storedUrl;

        } catch (Exception $e) {
            Log::error('Image storage failed', [
                'error' => $e->getMessage()
            ]);
            return $this->getFallbackImageUrl($type);
        }
    }

    private function generateFilename($productName, $type)
    {
        $slug = \Str::slug($productName);
        $timestamp = now()->format('YmdHis');
        return "{$slug}_{$type}_{$timestamp}.png";
    }

    public function getFallbackImages()
    {
        return [
            'white_bg' => 'https://via.placeholder.com/300x300/ffffff/6f42c1?text=Mystic+Design',
            'black_tshirt' => 'https://via.placeholder.com/300x300/000000/ffffff?text=T-Shirt+Mockup',
            'lifestyle' => 'https://via.placeholder.com/300x300/6f42c1/ffffff?text=Lifestyle+Photo'
        ];
    }

    private function getFallbackImageUrl($type)
    {
        $fallbacks = [
            'white_bg' => 'https://via.placeholder.com/300x300/ffffff/6f42c1?text=Design',
            'black_tshirt' => 'https://via.placeholder.com/300x300/000000/ffffff?text=Mockup',
            'lifestyle' => 'https://via.placeholder.com/300x300/6f42c1/ffffff?text=Lifestyle'
        ];

        return $fallbacks[$type] ?? $fallbacks['white_bg'];
    }

    public function getEstimatedCost($count)
    {
        // Emergent Universal Key pricing
        $costPerImage = 0.02;
        $imagesPerProduct = 3;
        $totalImages = $count * $imagesPerProduct;
        $estimatedCost = $totalImages * $costPerImage;

        return [
            'total_images' => $totalImages,
            'estimated_cost_usd' => round($estimatedCost, 2),
            'cost_per_product' => round($costPerImage * $imagesPerProduct, 2),
            'provider' => 'Emergent Universal Key'
        ];
    }
}
