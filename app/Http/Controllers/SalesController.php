<?php
// app/Http/Controllers/SalesController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EtsyApiService;
use Carbon\Carbon;

class SalesController extends Controller
{
    private $etsyService;

    public function __construct(EtsyApiService $etsyService)
    {
        $this->etsyService = $etsyService;
    }

    public function index()
    {
        $salesData = $this->prepareSalesChartData();
        $recentSales = $this->getRecentSales();
        
        return view('sales.index', compact('salesData', 'recentSales'));
    }

    private function prepareSalesChartData()
    {
        // Generate last 30 days of sales data
        $salesByDay = [];
        $revenueByDay = [];
        $ordersByDay = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayKey = $date->format('M j');
            
            // Mock data with some realistic variation
            $baseRevenue = rand(80, 300);
            $baseOrders = rand(3, 12);
            
            $salesByDay[] = $dayKey;
            $revenueByDay[] = $baseRevenue;
            $ordersByDay[] = $baseOrders;
        }
        
        return [
            'labels' => $salesByDay,
            'revenue' => $revenueByDay,
            'orders' => $ordersByDay,
            'total_revenue_30d' => array_sum($revenueByDay),
            'total_orders_30d' => array_sum($ordersByDay),
            'avg_order_value' => round(array_sum($revenueByDay) / array_sum($ordersByDay), 2)
        ];
    }

    private function getRecentSales()
    {
        $receipts = $this->etsyService->getShopReceipts();
        
        return collect($receipts['results'] ?? [])->map(function ($receipt) {
            return [
                'id' => $receipt['receipt_id'],
                'date' => Carbon::createFromTimestamp($receipt['creation_timestamp'])->format('M j, Y'),
                'customer' => str_replace('@example.com', '@*****.com', $receipt['buyer_email']),
                'product' => $receipt['transactions'][0]['title'] ?? 'Unknown Product',
                'amount' => '$' . number_format($receipt['total_price'], 2),
                'status' => 'Completed'
            ];
        })->take(20);
    }
}