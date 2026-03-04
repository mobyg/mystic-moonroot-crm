<?php
// routes/web.php - Complete version

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
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
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/generate', [ProductController::class, 'store'])->name('products.generate');
    Route::post('/products/{product}/status', [ProductController::class, 'updateStatus'])->name('products.status');
    Route::post('/products/{product}/discontinue', [ProductController::class, 'discontinue'])->name('products.discontinue');
    
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
});
