<?php
// app/Services/ImageGenerationService.php - Final version with consistent designs

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

            // Step 1: Generate ONE design image on white background
            $designPath = $this->generateDesignOnly($productName, $genre, $productDescription);
            
            if (!$designPath) {
                throw new Exception('Failed to generate design image');
            }

            // Step 2: Create mockups using the SAME design
            $flatMockupPath = $this->createMockup($designPath, $productName, 'flat');
            $lifestyleMockupPath = $this->createMockup($designPath, $productName, 'lifestyle');

            $images = [
                'white_bg' => Storage::url($designPath),
                'black_tshirt' => $flatMockupPath ? Storage::url($flatMockupPath) : $this->getFallbackImageUrl('flat'),
                'lifestyle' => $lifestyleMockupPath ? Storage::url($lifestyleMockupPath) : $this->getFallbackImageUrl('lifestyle')
            ];

            Log::info('Generated all images with consistent design', ['product' => $productName]);
            return $images;

        } catch (Exception $e) {
            Log::error('Image generation failed', [
                'error' => $e->getMessage(),
                'product' => $productName
            ]);
            return $this->getFallbackImages();
        }
    }

    private function generateDesignOnly($productName, $genre, $description)
    {
        $prompt = "A mystical mandala artwork on a PURE WHITE (#FFFFFF) background.
Theme: {$productName} - {$genre} style with celestial symbols, mystical elements.
Colors: Vibrant purples, teals, golds, blacks, and mystical blues.

CRITICAL REQUIREMENTS:
- Background MUST be pure white (#FFFFFF), not gray, not cream
- Design centered with 20% margins on all sides  
- Compact circular/contained design suitable for chest print area
- NO text, NO words, NO letters
- High contrast design that pops against white
- Print-ready quality illustration";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl . '/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'hd',
                'style' => 'vivid'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $imageUrl = $data['data'][0]['url'];
                
                // Download and store
                $imageContent = Http::timeout(60)->get($imageUrl)->body();
                $filename = \Str::slug($productName) . '_design_' . now()->format('YmdHis') . '.png';
                $path = 'products/' . $filename;
                
                Storage::disk('public')->put($path, $imageContent);
                
                Log::info('Design generated and stored', ['path' => $path]);
                return $path;
            }

            Log::error('DALL-E API failed', ['body' => $response->body()]);
            return null;

        } catch (Exception $e) {
            Log::error('Design generation error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function createMockup($designPath, $productName, $mockupType)
    {
        try {
            // Get paths
            $designFullPath = storage_path('app/public/' . $designPath);
            $templatePath = $this->getMockupTemplate($mockupType);

            Log::info('Creating mockup', [
                'type' => $mockupType,
                'design_path' => $designFullPath,
                'template_path' => $templatePath,
                'design_exists' => file_exists($designFullPath),
                'template_exists' => file_exists($templatePath)
            ]);

            if (!file_exists($designFullPath)) {
                throw new Exception('Design file not found: ' . $designFullPath);
            }

            if (!file_exists($templatePath)) {
                Log::warning('Mockup template not found, using AI fallback', ['type' => $mockupType, 'path' => $templatePath]);
                return $this->generateAIMockup($productName, $mockupType);
            }

            // Load images using GD
            $design = $this->loadImage($designFullPath);
            $template = $this->loadImage($templatePath);

            if (!$design) {
                throw new Exception('Could not load design image');
            }
            if (!$template) {
                throw new Exception('Could not load template image');
            }

            $templateWidth = imagesx($template);
            $templateHeight = imagesy($template);
            $designWidth = imagesx($design);
            $designHeight = imagesy($design);

            Log::info('Image dimensions', [
                'template' => "{$templateWidth}x{$templateHeight}",
                'design' => "{$designWidth}x{$designHeight}"
            ]);

            // Remove background from design (detect from corners)
            $design = $this->removeBackground($design);

            // Calculate placement config
            $config = $this->getPlacementConfig($mockupType, $templateWidth, $templateHeight);

            // Resize design to fit
            $newWidth = $config['width'];
            $newHeight = $config['height'];
            
            $resizedDesign = imagecreatetruecolor($newWidth, $newHeight);
            imagesavealpha($resizedDesign, true);
            $transparent = imagecolorallocatealpha($resizedDesign, 0, 0, 0, 127);
            imagefill($resizedDesign, 0, 0, $transparent);
            
            imagecopyresampled(
                $resizedDesign, $design,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $designWidth, $designHeight
            );

            // Composite onto template
            imagecopy($template, $resizedDesign, $config['x'], $config['y'], 0, 0, $newWidth, $newHeight);

            // Save result
            $filename = \Str::slug($productName) . "_{$mockupType}_" . now()->format('YmdHis') . '.png';
            $outputPath = storage_path('app/public/products/' . $filename);
            
            if (!file_exists(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            imagepng($template, $outputPath);

            // Cleanup
            imagedestroy($design);
            imagedestroy($template);
            imagedestroy($resizedDesign);

            Log::info('Mockup created successfully', ['type' => $mockupType, 'path' => $filename]);

            return 'products/' . $filename;

        } catch (Exception $e) {
            Log::error('Mockup creation failed', [
                'error' => $e->getMessage(),
                'type' => $mockupType,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return $this->generateAIMockup($productName, $mockupType);
        }
    }

    private function loadImage($path)
    {
        $info = getimagesize($path);
        if (!$info) return null;

        switch ($info[2]) {
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($path);
                break;
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($path);
                break;
            default:
                return null;
        }

        if ($img) {
            imagesavealpha($img, true);
        }
        return $img;
    }

    private function removeBackground($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Sample corners to detect background color
        $corners = [
            imagecolorsforindex($image, imagecolorat($image, 5, 5)),
            imagecolorsforindex($image, imagecolorat($image, $width - 5, 5)),
            imagecolorsforindex($image, imagecolorat($image, 5, $height - 5)),
            imagecolorsforindex($image, imagecolorat($image, $width - 5, $height - 5))
        ];

        $bgR = (int)(($corners[0]['red'] + $corners[1]['red'] + $corners[2]['red'] + $corners[3]['red']) / 4);
        $bgG = (int)(($corners[0]['green'] + $corners[1]['green'] + $corners[2]['green'] + $corners[3]['green']) / 4);
        $bgB = (int)(($corners[0]['blue'] + $corners[1]['blue'] + $corners[2]['blue'] + $corners[3]['blue']) / 4);

        $tolerance = 30;

        // Create new image with transparency
        $newImage = imagecreatetruecolor($width, $height);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                
                if (abs($color['red'] - $bgR) < $tolerance &&
                    abs($color['green'] - $bgG) < $tolerance &&
                    abs($color['blue'] - $bgB) < $tolerance) {
                    // Make transparent
                    imagesetpixel($newImage, $x, $y, $transparent);
                } else {
                    // Keep original color
                    $newColor = imagecolorallocatealpha($newImage, $color['red'], $color['green'], $color['blue'], $color['alpha']);
                    imagesetpixel($newImage, $x, $y, $newColor);
                }
            }
        }

        imagedestroy($image);
        return $newImage;
    }

    private function getMockupTemplate($type)
    {
        $templates = [
            'flat' => storage_path('app/mockups/black_tshirt_flat.png'),
            'lifestyle' => storage_path('app/mockups/lifestyle_blank.png')
        ];
        return $templates[$type] ?? $templates['flat'];
    }

    private function getPlacementConfig($mockupType, $width, $height)
    {
        // Design sizes adjusted per template
        $configs = [
            'flat' => [
                // For the realistic black t-shirt flat lay (486x608)
                'x' => (int)(($width - 220) / 2),
                'y' => (int)($height * 0.25),
                'width' => 220,
                'height' => 220
            ],
            'lifestyle' => [
                // For the lifestyle photo template (774x1161)
                // Centered on chest, below face
                'x' => (int)(($width - 180) / 2),
                'y' => (int)($height * 0.52),
                'width' => 180,
                'height' => 180
            ]
        ];
        return $configs[$mockupType] ?? $configs['flat'];
    }

    private function generateAIMockup($productName, $mockupType)
    {
        // Fallback: generate via AI if template compositing fails
        $prompts = [
            'flat' => "Professional product photo of a black t-shirt laid flat on white background with a colorful mystical circular mandala design on chest. Vibrant purples, teals, golds. E-commerce style, centered.",
            'lifestyle' => "Real candid photo of person wearing black t-shirt with colorful mystical mandala design. Shot on iPhone, natural lighting, cafe setting, genuine smile."
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl . '/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => $prompts[$mockupType] ?? $prompts['flat'],
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard'
            ]);

            if ($response->successful()) {
                $imageUrl = $response->json()['data'][0]['url'];
                $imageContent = Http::timeout(60)->get($imageUrl)->body();
                $filename = \Str::slug($productName) . "_{$mockupType}_" . now()->format('YmdHis') . '.png';
                Storage::disk('public')->put('products/' . $filename, $imageContent);
                return 'products/' . $filename;
            }
        } catch (Exception $e) {
            Log::error('AI mockup fallback failed', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    public function getFallbackImages()
    {
        return [
            'white_bg' => 'https://via.placeholder.com/300x300/ffffff/6f42c1?text=Design',
            'black_tshirt' => 'https://via.placeholder.com/300x300/000000/ffffff?text=Mockup',
            'lifestyle' => 'https://via.placeholder.com/300x300/6f42c1/ffffff?text=Lifestyle'
        ];
    }

    private function getFallbackImageUrl($type)
    {
        $fallbacks = [
            'flat' => 'https://via.placeholder.com/300x300/000000/ffffff?text=Mockup',
            'lifestyle' => 'https://via.placeholder.com/300x300/6f42c1/ffffff?text=Lifestyle'
        ];
        return $fallbacks[$type] ?? $fallbacks['flat'];
    }

    public function getEstimatedCost($count)
    {
        // Only 1 AI-generated image per product (the design)
        // Mockups are composited from templates
        $costPerImage = 0.08; // DALL-E 3 HD
        $totalImages = $count;
        $estimatedCost = $totalImages * $costPerImage;

        return [
            'total_images' => $totalImages,
            'estimated_cost_usd' => round($estimatedCost, 2),
            'cost_per_product' => round($costPerImage, 2)
        ];
    }
}
