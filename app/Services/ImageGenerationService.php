<?php
// app/Services/ImageGenerationService.php - Uses OpenAI directly

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ImageGenerationService
{
    private $openaiApiKey;
    private $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        Log::info('ImageGenerationService initialized', [
            'has_api_key' => !empty($this->openaiApiKey),
            'api_key_preview' => $this->openaiApiKey ? 'sk-***' . substr($this->openaiApiKey, -4) : 'none'
        ]);
    }

    public function generateProductImages($genre, $productName, $productDescription)
    {
        Log::info('Starting product image generation', [
            'genre' => $genre,
            'product_name' => $productName
        ]);

        try {
            if (!$this->openaiApiKey) {
                throw new Exception('OpenAI API key not configured');
            }

            $images = [
                'white_bg' => $this->generateSingleImage($productName, $genre, 'white_background'),
                'black_tshirt' => $this->generateSingleImage($productName, $genre, 'black_tshirt'),
                'lifestyle' => $this->generateSingleImage($productName, $genre, 'lifestyle')
            ];

            Log::info('Generated images successfully', ['images' => $images]);
            return $images;

        } catch (Exception $e) {
            Log::error('Image generation failed', [
                'error' => $e->getMessage(),
                'product' => $productName
            ]);
            return $this->getFallbackImages();
        }
    }

    private function generateSingleImage($productName, $genre, $type)
    {
        $prompts = [
            'white_background' => "A mystical {$genre} t-shirt design: {$productName}. Beautiful spiritual artwork with rich vibrant colors - deep purples, teals, golds, and mystical blues. Centered composition on pure white background, detailed colorful illustration suitable for t-shirt printing",
            
            'black_tshirt' => "A realistic black t-shirt mockup featuring a colorful mystical {$genre} design for {$productName}. The design has vibrant purples, teals, and gold colors that pop against the black fabric. Professional product photography, t-shirt laid flat on white background, design centered on chest area",
            
            'lifestyle' => "A real authentic photograph of an everyday person casually wearing a black t-shirt with a colorful mystical design. Shot on iPhone, candid natural moment, real person not a model, genuine smile, natural daylight, outdoor setting like a park or coffee shop patio. Documentary style photography, not posed or staged"
        ];

        $prompt = $prompts[$type] ?? $prompts['white_background'];
        
        Log::info('Generating image', ['type' => $type, 'prompt' => substr($prompt, 0, 100) . '...']);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl . '/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
                'style' => 'vivid'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $imageUrl = $data['data'][0]['url'];
                
                Log::info('DALL-E image generated', ['type' => $type, 'url' => $imageUrl]);
                
                // Store the image
                $storedUrl = $this->storeImageFromUrl($imageUrl, $productName, $type);
                
                return $storedUrl ?: $imageUrl;
            } else {
                Log::error('DALL-E API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('DALL-E API call failed: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Image generation error', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return $this->getFallbackImageUrl($type);
        }
    }

    private function storeImageFromUrl($imageUrl, $productName, $type)
    {
        try {
            Log::info('Storing image', ['url' => $imageUrl, 'type' => $type]);
            
            $imageContent = Http::timeout(60)->get($imageUrl)->body();
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
                'error' => $e->getMessage(),
                'url' => $imageUrl
            ]);
            return null;
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
            'white_background' => 'https://via.placeholder.com/300x300/ffffff/6f42c1?text=Design',
            'black_tshirt' => 'https://via.placeholder.com/300x300/000000/ffffff?text=Mockup',
            'lifestyle' => 'https://via.placeholder.com/300x300/6f42c1/ffffff?text=Lifestyle'
        ];

        return $fallbacks[$type] ?? $fallbacks['white_background'];
    }

    public function getEstimatedCost($count)
    {
        $costPerImage = 0.04; // DALL-E 3 standard quality pricing
        $imagesPerProduct = 3;
        $totalImages = $count * $imagesPerProduct;
        $estimatedCost = $totalImages * $costPerImage;

        return [
            'total_images' => $totalImages,
            'estimated_cost_usd' => round($estimatedCost, 2),
            'cost_per_product' => round($costPerImage * $imagesPerProduct, 2)
        ];
    }
}
