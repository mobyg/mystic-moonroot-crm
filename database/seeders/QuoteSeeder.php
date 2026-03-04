<?php
// database/seeders/QuoteSeeder.php - Updated version

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuoteSeeder extends Seeder
{
    public function run()
    {
        $categories = ['success', 'business'];
        $quotes = collect();
        
        foreach ($categories as $category) {
            // Try to fetch multiple batches to get 365 quotes
            for ($batch = 0; $batch < 5; $batch++) {
                try {
                    $response = Http::withHeaders([
                        'X-Api-Key' => config('services.ninja.api_key')
                    ])->get('https://api.api-ninjas.com/v1/quotes', [
                        'category' => $category
                    ]);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        foreach ($data as $quoteData) {
                            // Only add quotes under 300 characters
                            if (strlen($quoteData['quote']) <= 300) {
                                $quotes->push([
                                    'quote' => $quoteData['quote'],
                                    'author' => $quoteData['author'],
                                    'category' => $category,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    }
                    
                    // Add delay to respect API rate limits
                    sleep(1);
                    
                } catch (\Exception $e) {
                    Log::error("Failed to fetch quotes for category {$category}, batch {$batch}: " . $e->getMessage());
                    continue;
                }
            }
        }
        
        // Add some spiritual/nature quotes that fit the brand
        $spiritualQuotes = [
            ['quote' => 'The earth has music for those who listen.', 'author' => 'Shakespeare', 'category' => 'success'],
            ['quote' => 'In every walk with nature, one receives far more than they seek.', 'author' => 'John Muir', 'category' => 'success'],
            ['quote' => 'The moon does not fight. It attacks no one. It does not worry. It does not try to crush others. It keeps to its course, but by its very nature, it gently influences.', 'author' => 'Ming-Dao Deng', 'category' => 'success'],
            ['quote' => 'Trust the process. Your time is coming. Just do the work and the results will handle themselves.', 'author' => 'Tony Gaskins', 'category' => 'business'],
            ['quote' => 'The cave you fear to enter holds the treasure you seek.', 'author' => 'Joseph Campbell', 'category' => 'success'],
            ['quote' => 'Everything you need is inside you – you just need to access it.', 'author' => 'Buddha', 'category' => 'success'],
            ['quote' => 'Magic happens when you do not give up, even though you want to.', 'author' => 'Unknown', 'category' => 'business'],
            ['quote' => 'The universe conspires to help you achieve your dreams when you are aligned with your purpose.', 'author' => 'Unknown', 'category' => 'success'],
        ];
        
        foreach ($spiritualQuotes as $quote) {
            $quotes->push(array_merge($quote, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // If we still don't have enough quotes, duplicate some with variations
        while ($quotes->count() < 100) {
            $existingQuote = $quotes->random();
            $quotes->push($existingQuote);
        }
        
        // Insert quotes in chunks to avoid memory issues
        $quotes->chunk(50)->each(function ($chunk) {
            Quote::insert($chunk->toArray());
        });
        
        $this->command->info('Seeded ' . $quotes->count() . ' quotes successfully!');
    }
}