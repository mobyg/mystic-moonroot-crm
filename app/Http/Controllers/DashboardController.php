<?php
// app/Http/Controllers/DashboardController.php - Updated version

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Services\EtsyApiService;

class DashboardController extends Controller
{
    private $etsyService;

    public function __construct(EtsyApiService $etsyService)
    {
        $this->etsyService = $etsyService;
    }

    public function index()
    {
        $dailyQuote = Quote::getDailyQuote();
        $salesData = $this->getSalesData();
        
        return view('dashboard', compact('dailyQuote', 'salesData'));
    }

    public function getSalesData()
    {
        $shopStats = $this->etsyService->getShopStats();
        $popularListings = $this->etsyService->getPopularListings();
        
        return [
            'weekly_sales' => $shopStats['weekly_revenue'] ?? 0,
            'monthly_sales' => $shopStats['monthly_revenue'] ?? 0,
            'weekly_orders' => $shopStats['weekly_orders'] ?? 0,
            'monthly_orders' => $shopStats['monthly_orders'] ?? 0,
            'total_orders' => $shopStats['total_orders'] ?? 0,
            'popular_products' => collect($popularListings['results'] ?? [])->take(3)->map(function ($listing) {
                return [
                    'name' => $listing['title'],
                    'image' => $listing['images'][0]['url_570xN'] ?? 'https://via.placeholder.com/80x80/6f42c1/ffffff?text=MP',
                    'sales' => rand(50, 200) // Mock sales count
                ];
            })->toArray()
        ];
    }
}