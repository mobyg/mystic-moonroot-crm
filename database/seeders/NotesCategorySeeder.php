<?php
// database/seeders/NotesCategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NoteCategory;

class NotesCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = ['To Do', 'Note'];
        
        foreach ($categories as $category) {
            NoteCategory::create(['name' => $category]);
        }
    }
}