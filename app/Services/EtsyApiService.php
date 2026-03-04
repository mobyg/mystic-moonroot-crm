<?php
// app/Services/EtsyApiService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EtsyApiService
{
    private $apiKey;
    private $sharedSecret;
    private $baseUrl = 'https://openapi.etsy.com/v3/application';

    public function __construct()
    {
        $this->apiKey = config('services.etsy.api_key');
        $this->sharedSecret = config('services.etsy.shared_secret');
    }

    public function getShopStats($shopId = null)
    {
        // Cache for 15 minutes to avoid rate limiting
        return Cache::remember('etsy_shop_stats', 900, function () use ($shopId) {
            try {
                // For now, we'll use mock data as Etsy API v3 requires OAuth 2.0 setup
                // In production, you'd implement the full OAuth flow
                return $this->getMockShopStats();
                
                /*
                // Real API implementation would look like:
                $response = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Authorization' => 'Bearer ' . $accessToken
                ])->get("{$this->baseUrl}/shops/{$shopId}/stats");

                if ($response->successful()) {
                    return $response->json();
                }
                */
            } catch (\Exception $e) {
                Log::error('Etsy API Error: ' . $e->getMessage());
                return $this->getMockShopStats();
            }
        });
    }

    public function getShopReceipts($shopId = null, $limit = 100)
    {
        return Cache::remember('etsy_shop_receipts', 900, function () use ($shopId, $limit) {
            try {
                // Mock data for now
                return $this->getMockReceipts();
            } catch (\Exception $e) {
                Log::error('Etsy Receipts API Error: ' . $e->getMessage());
                return $this->getMockReceipts();
            }
        });
    }

    public function getPopularListings($shopId = null, $limit = 10)
    {
        return Cache::remember('etsy_popular_listings', 1800, function () use ($shopId, $limit) {
            try {
                return $this->getMockPopularListings();
            } catch (\Exception $e) {
                Log::error('Etsy Popular Listings API Error: ' . $e->getMessage());
                return $this->getMockPopularListings();
            }
        });
    }

    private function getMockShopStats()
    {
        // Realistic mock data based on Mystic Moonroot's theme
        return [
            'weekly_revenue' => rand(800, 1500),
            'monthly_revenue' => rand(3000, 6000),
            'weekly_orders' => rand(25, 60),
            'monthly_orders' => rand(120, 250),
            'total_orders' => rand(1500, 3000),
            'last_updated' => now()->toISOString()
        ];
    }

    private function getMockReceipts()
    {
        $receipts = [];
        for ($i = 0; $i < 50; $i++) {
            $receipts[] = [
                'receipt_id' => rand(100000, 999999),
                'buyer_email' => 'customer' . rand(1, 1000) . '@example.com',
                'creation_timestamp' => now()->subDays(rand(0, 30))->timestamp,
                'total_price' => rand(15, 85),
                'currency_code' => 'CAD',
                'buyer_user_id' => rand(10000, 99999),
                'transactions' => [
                    [
                        'title' => $this->getRandomProductName(),
                        'price' => rand(15, 85),
                        'quantity' => rand(1, 3)
                    ]
                ]
            ];
        }
        return ['results' => $receipts];
    }

    private function getMockPopularListings()
    {
        $products = [
            'Mystic Moon Phases T-Shirt',
            'Sacred Tree of Life Hoodie',
            'Crystal Energy Alignment Tee',
            'Pentagram Protection Shirt',
            'Chakra Balance Design',
            'Forest Guardian Mystical Tee',
            'Third Eye Awakening Shirt',
            'Luna Goddess Moon Tee',
            'Herbal Apothecary Design',
            'Sacred Geometry Pattern'
        ];

        $listings = [];
        for ($i = 0; $i < 6; $i++) {
            $listings[] = [
                'listing_id' => rand(100000, 999999),
                'title' => $products[array_rand($products)],
                'price' => rand(25, 65),
                'currency_code' => 'CAD',
                'quantity' => rand(5, 50),
                'views' => rand(100, 1000),
                'num_favorers' => rand(10, 100),
                'images' => [
                    [
                        'url_570xN' => 'https://via.placeholder.com/80x80/' . 
                            ['6f42c1', '28a745', 'dc3545', 'ffc107', '17a2b8', '6c757d'][rand(0, 5)] . 
                            '/ffffff?text=' . substr($products[$i % count($products)], 0, 2)
                    ]
                ]
            ];
        }
        return ['results' => $listings];
    }

    private function getRandomProductName()
    {
        $adjectives = ['Mystic', 'Sacred', 'Ancient', 'Cosmic', 'Ethereal', 'Divine'];
        $nouns = ['Moon', 'Tree', 'Crystal', 'Energy', 'Spirit', 'Goddess', 'Guardian'];
        $items = ['T-Shirt', 'Hoodie', 'Tank Top', 'Long Sleeve'];
        
        return $adjectives[array_rand($adjectives)] . ' ' . 
               $nouns[array_rand($nouns)] . ' ' . 
               $items[array_rand($items)];
    }
}