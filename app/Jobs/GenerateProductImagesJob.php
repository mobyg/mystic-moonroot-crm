<?php
// app/Jobs/GenerateProductImagesJob.php - Updated

namespace App\Jobs;

use App\Models\Product;
use App\Services\ImageGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProductImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;
    public $maxExceptions = 1;

    protected $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    public function handle(ImageGenerationService $imageService)
    {
        Log::info("Starting image generation job for product: {$this->productId}");

        $product = Product::find($this->productId);
        
        if (!$product) {
            Log::error("Product not found for image generation: {$this->productId}");
            return;
        }

        // Update product status
        $product->update(['status' => 'In Progress']);
        
        Log::info("Updated product status to In Progress: {$product->name}");

        try {
            // Generate images
            $images = $imageService->generateProductImages(
                $product->genre,
                $product->name,
                $product->description
            );

            // Update product with generated images
            $product->update([
                'images' => $images,
                'status' => 'Complete'
            ]);

            Log::info("Image generation completed successfully for product: {$product->name}", [
                'images' => $images
            ]);

        } catch (\Exception $e) {
            Log::error("Image generation failed for product {$product->name}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $product->update([
                'status' => 'Draft',
                'images' => $imageService->getFallbackImages()
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Image generation job completely failed for product {$this->productId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $product = Product::find($this->productId);
        if ($product) {
            $imageService = new ImageGenerationService();
            $product->update([
                'status' => 'Draft',
                'images' => $imageService->getFallbackImages()
            ]);
        }
    }
}