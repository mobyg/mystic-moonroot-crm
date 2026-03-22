<?php
// routes/web.php - Complete version

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotesController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update']);
    
    // Products
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::post('/products/generate', [ProductsController::class, 'store'])->name('products.generate');
    Route::post('/products/{product}/status', [ProductsController::class, 'updateStatus'])->name('products.status');
    Route::post('/products/{product}/discontinue', [ProductsController::class, 'discontinue'])->name('products.discontinue');
    
    Route::post('/products/{product}/regenerate', [ProductsController::class, 'regenerateImages'])->name('products.regenerate');
    Route::get('/products/{product}/download', [ProductsController::class, 'downloadImages'])->name('products.download');
    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('sales');
    
    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('/calendar/events/{event}/move', [CalendarController::class, 'move'])->name('calendar.move');
    
    // Notes
    Route::resource('notes', NotesController::class);
    
    // Placeholder routes
    Route::get('/marketing', function () {
        return view('coming-soon', ['title' => 'Marketing']);
    })->name('marketing');
    
    Route::get('/blog', function () {
        return view('coming-soon', ['title' => 'Blog']);
    })->name('blog');

    Route::get('/test-generation/{product}', function(\App\Models\Product $product) {
        if (!config('services.openai.api_key')) {
            return response()->json(['error' => 'OpenAI API key not set']);
        }
        
        try {
            $imageService = new \App\Services\ImageGenerationService();
            
            // Test the service directly
            $images = $imageService->generateProductImages(
                $product->genre,
                $product->name,
                $product->description
            );
            
            $product->update(['images' => $images, 'status' => 'Complete']);
            
            return response()->json([
                'success' => true,
                'product' => $product->fresh(),
                'generated_images' => $images
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    Route::get('/debug-products', function() {
        $products = \App\Models\Product::latest()->take(5)->get();
        
        return response()->json([
            'products' => $products->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'status' => $p->status,
                    'images' => $p->images,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at
                ];
            }),
            'queue_jobs_waiting' => DB::table('jobs')->count(),
            'queue_failed_jobs' => DB::table('failed_jobs')->count(),
        ]);
    });
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
