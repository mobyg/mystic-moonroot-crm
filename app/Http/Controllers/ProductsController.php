<?php
// app/Http/Controllers/ProductsController.php - Updated with AI generation

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ImageGenerationService;
use App\Jobs\GenerateProductImagesJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    private $imageService;

    public function __construct(ImageGenerationService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('status') && !empty($request->status)) {
            $query->whereIn('status', $request->status);
        }
        
        if (!$request->has('show_discontinued') || !$request->show_discontinued) {
            $query->where('status', '!=', 'Discontinued');
        }
        
        $products = $query->orderBy('created_at', 'desc')->get();
        
        if ($request->ajax()) {
            return view('products.grid', compact('products'))->render();
        }
        
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'genre' => 'required|string|max:255',
            'count' => 'required|integer|min:1|max:20'
        ]);

        // Check if OpenAI API key is configured
        if (!config('services.openai.api_key')) {
            return response()->json([
                'success' => false, 
                'message' => 'OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.'
            ], 400);
        }

        // Increase PHP execution time for image generation
        set_time_limit(300); // 5 minutes

        try {
            // Get cost estimate
            $costEstimate = $this->imageService->getEstimatedCost($request->count);
            
            // Generate products with AI
            $products = $this->generateProductsWithAI($request->genre, $request->count);
            
            return response()->json([
                'success' => true, 
                'message' => "Generated {$request->count} product(s) successfully!",
                'products_created' => count($products),
                'cost_estimate' => $costEstimate
            ]);

        } catch (\Exception $e) {
            Log::error('Product generation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate products: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateProductsWithAI($genre, $count)
    {
        $designThemes = $this->getAIDesignThemes($genre);
        $products = [];
        
        for ($i = 0; $i < $count; $i++) {
            $theme = $designThemes[array_rand($designThemes)];
            
            // Create product with placeholder images
            $product = Product::create([
                'name' => $theme['name'],
                'description' => $theme['description'],
                'category' => 'T-Shirt',
                'status' => 'Draft',
                'genre' => $genre,
                'images' => [
                    'white_bg' => 'https://via.placeholder.com/300x300/ffffff/6f42c1?text=Generating...',
                    'black_tshirt' => 'https://via.placeholder.com/300x300/000000/ffffff?text=Generating...',
                    'lifestyle' => 'https://via.placeholder.com/300x300/6f42c1/ffffff?text=Generating...'
                ]
            ]);
            
            // Run image generation synchronously (no queue worker needed)
            GenerateProductImagesJob::dispatchSync($product->id);
            
            $products[] = $product;
        }
        
        return $products;
    }

    private function getAIDesignThemes($genre)
    {
        $themes = [
            'witchy' => [
                ['name' => 'Lunar Witch Circle', 'description' => 'A sacred circle of moon phases with mystical pentagram at center, surrounded by magical herbs and crystals that enhance intuition and spell work'],
                ['name' => 'Crystal Grid Magic', 'description' => 'Powerful healing crystals arranged in sacred geometry formation with energy lines connecting amethyst, rose quartz, and clear quartz for manifestation'],
                ['name' => 'Herb Witch Apothecary', 'description' => 'Vintage-inspired botanical illustration featuring lavender, sage, rosemary and other magical herbs with hand-drawn labels and mystical symbols'],
                ['name' => 'Moon Goddess Silhouette', 'description' => 'Elegant silhouette of a goddess figure reaching toward crescent moon with flowing hair that transforms into constellation patterns'],
                ['name' => 'Tarot Card Mystique', 'description' => 'Beautiful tarot-inspired design featuring The High Priestess energy with celestial symbols, keys to wisdom, and intuitive third eye imagery'],
                ['name' => 'Cauldron Brew Magic', 'description' => 'Enchanted cauldron with mystical steam forming magical symbols, surrounded by spell ingredients and flickering candle flames'],
                ['name' => 'Pentagram Nature Mandala', 'description' => 'Sacred five-pointed star intertwined with natural elements like vines, flowers, and tree roots creating a protective earth mandala'],
                ['name' => 'Witch Hat Constellation', 'description' => 'Classic pointed witch hat silhouette filled with starry night sky and constellation patterns that tell ancient magical stories']
            ],
            'spiritual' => [
                ['name' => 'Chakra Activation Mandala', 'description' => 'Seven chakras aligned in perfect harmony with intricate mandala patterns radiating healing energy and rainbow light frequencies'],
                ['name' => 'Third Eye Awakening', 'description' => 'Mystical third eye symbol with lotus petals opening to reveal cosmic consciousness and spiritual insight with sacred geometry patterns'],
                ['name' => 'Om Sacred Vibration', 'description' => 'Beautiful Om symbol with sound wave vibrations radiating outward, incorporating Hindu spiritual elements and meditation energy'],
                ['name' => 'Lotus Enlightenment', 'description' => 'Blooming lotus flower emerging from still water with Buddha hand mudras and golden light representing spiritual awakening'],
                ['name' => 'Metatrons Cube Sacred Geometry', 'description' => 'Complex sacred geometry pattern of Metatrons Cube with interconnected circles and divine proportions revealing universal creation'],
                ['name' => 'Meditation Mountain Zen', 'description' => 'Peaceful mountain silhouette with Buddha figure in meditation pose, surrounded by floating prayer flags and peaceful energy'],
                ['name' => 'Tree of Life Chakras', 'description' => 'Ancient tree of life with seven chakra points along the trunk, roots deep in earth energy and branches reaching cosmic consciousness'],
                ['name' => 'Yin Yang Balance', 'description' => 'Traditional yin yang symbol with flowing water and fire elements representing perfect balance between masculine and feminine energies']
            ],
            'nature' => [
                ['name' => 'Mountain Spirit Guardian', 'description' => 'Majestic mountain range with spirit animals like wolves and eagles soaring through misty peaks under starry celestial sky'],
                ['name' => 'Forest Temple Ancient', 'description' => 'Mystical forest clearing with ancient stone circle and towering old growth trees whose branches form natural cathedral archways'],
                ['name' => 'Ocean Wave Energy', 'description' => 'Powerful ocean waves with golden sunset light streaming through water droplets creating rainbow prisms and healing energy'],
                ['name' => 'Earth Element Mandala', 'description' => 'Four elements mandala featuring earth stones, air clouds, fire flames, and water waves in perfect elemental balance and harmony'],
                ['name' => 'Celestial Tree Connection', 'description' => 'Cosmic tree with roots extending to earth center and branches reaching up to touch stars, moon, and planets in night sky'],
                ['name' => 'Wolf Pack Moon Call', 'description' => 'Silhouettes of wolf pack howling at full moon with forest backdrop and mystical energy connecting wild spirit to lunar power'],
                ['name' => 'Sunrise Mountain Peak', 'description' => 'Dramatic mountain peak catching first light of dawn with golden rays illuminating the landscape and awakening earth energy'],
                ['name' => 'Desert Cactus Bloom', 'description' => 'Southwest desert scene with blooming cacti and succulents under starry sky representing resilience and hidden beauty in harsh conditions']
            ],
            'mystical' => [
                ['name' => 'Ancient Rune Circle', 'description' => 'Circle of powerful Viking runes with elder futhark symbols glowing with mystical energy against dark cosmic background'],
                ['name' => 'Crystal Ball Visions', 'description' => 'Ornate crystal ball on ceremonial stand with swirling galaxies and future visions visible within the mystical sphere'],
                ['name' => 'Dragon Protection Sigil', 'description' => 'Majestic dragon coiled around protective sigil with ancient Celtic knotwork and flames that transform into mystical energy'],
                ['name' => 'Celestial Map Navigation', 'description' => 'Vintage-style star map with constellation patterns, celestial coordinates, and mystical symbols for navigating cosmic realms'],
                ['name' => 'Phoenix Fire Rebirth', 'description' => 'Phoenix rising from ashes with brilliant fire wings spread wide, symbolizing transformation and eternal spiritual renewal'],
                ['name' => 'Spell Book Ancient', 'description' => 'Open grimoire with floating magical symbols, quill pen writing mystical incantations, and candle flame illuminating ancient wisdom'],
                ['name' => 'Key to Mysteries', 'description' => 'Ornate skeleton key with mystical engravings unlocking doorway to other dimensions with cosmic energy flowing through portal'],
                ['name' => 'Time Spiral Portal', 'description' => 'Swirling time spiral with past, present, and future symbols flowing through dimensional gateway lined with mystical runes']
            ],
            'healing' => [
                ['name' => 'Crystal Healing Grid', 'description' => 'Geometric arrangement of healing crystals with energy lines connecting amethyst, rose quartz, and selenite for chakra balancing'],
                ['name' => 'Reiki Healing Hands', 'description' => 'Gentle hands channeling golden healing energy with light rays and universal life force symbols flowing from palms'],
                ['name' => 'Medicine Wheel Sacred', 'description' => 'Native American medicine wheel with four directions, animal spirits, and healing plants representing holistic wellness'],
                ['name' => 'Sound Healing Frequencies', 'description' => 'Singing bowls with visible sound waves and healing frequencies radiating rainbow colors that promote cellular regeneration'],
                ['name' => 'Herbal Healing Garden', 'description' => 'Abundance of medicinal plants like echinacea, calendula, and lavender with healing properties and earth energy'],
                ['name' => 'Angel Healing Wings', 'description' => 'Protective angel wings surrounding heart chakra with divine light and healing energy flowing to promote emotional wellness'],
                ['name' => 'Water Blessing Ceremony', 'description' => 'Sacred water droplets with blessing symbols and purification energy that cleanses negative energy and promotes healing'],
                ['name' => 'Light Body Activation', 'description' => 'Human silhouette with chakras glowing and light body energy field expanding outward with healing rainbow frequencies']
            ],
            'goddess' => [
                ['name' => 'Divine Moon Goddess', 'description' => 'Graceful goddess silhouette with flowing hair that becomes moonbeams, crowned with crescent moon and surrounded by lunar energy'],
                ['name' => 'Earth Mother Gaia', 'description' => 'Nurturing earth goddess figure with mountains as her body, rivers as her veins, and forests as her flowing hair'],
                ['name' => 'Venus Love Goddess', 'description' => 'Beautiful goddess symbol with rose petals, love hearts, and feminine power symbols representing divine love and attraction'],
                ['name' => 'Warrior Goddess Shield', 'description' => 'Fierce goddess warrior with sacred shield decorated with protective symbols, standing strong in feminine power and courage'],
                ['name' => 'Wisdom Goddess Owl', 'description' => 'Goddess figure with owl companion representing ancient wisdom, intuition, and the ability to see truth in darkness'],
                ['name' => 'Sea Goddess Flowing', 'description' => 'Ocean goddess with flowing seaweed hair and pearl crown, commanding the tides and representing emotional depth and intuition'],
                ['name' => 'Fire Goddess Phoenix', 'description' => 'Powerful fire goddess with phoenix spirit animal, representing transformation, passion, and the creative life force energy'],
                ['name' => 'Star Goddess Celestial', 'description' => 'Cosmic goddess figure made of stars and galaxies with celestial crown, representing connection to infinite universe and divine feminine']
            ]
        ];

        return $themes[strtolower($genre)] ?? $themes['spiritual'];
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Product::STATUSES)
        ]);

        $product->update(['status' => $request->status]);
        
        return response()->json(['success' => true]);
    }

    public function discontinue(Request $request, Product $product)
    {
        $request->validate([
            'confirmation' => 'required|in:DISCONTINUE'
        ]);

        $product->update(['status' => 'Discontinued']);
        
        return response()->json(['success' => true]);
    }

    public function regenerateImages(Product $product)
    {
        if (!config('services.openai.api_key')) {
            return response()->json([
                'success' => false, 
                'message' => 'OpenAI API key not configured.'
            ], 400);
        }

        try {
            $product->update(['status' => 'In Progress']);
            GenerateProductImagesJob::dispatchSync($product->id);

            return response()->json([
                'success' => true,
                'message' => 'Image regeneration complete!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Image regeneration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadImages(Product $product)
    {
        try {
            $zip = new \ZipArchive();
            $fileName = storage_path('app/temp/' . \Str::slug($product->name) . '_images.zip');
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            if ($zip->open($fileName, \ZipArchive::CREATE) === TRUE) {
                foreach ($product->images as $type => $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $imageContent = file_get_contents($url);
                        $zip->addFromString($type . '_' . $product->name . '.png', $imageContent);
                    }
                }
                $zip->close();

                $product->update(['status' => 'Downloaded']);

                return response()->download($fileName)->deleteFileAfterSend(true);
            }

            return response()->json(['success' => false, 'message' => 'Failed to create zip file'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Download failed'], 500);
        }
    }
}