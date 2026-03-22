<?php
// database/migrations/2024_01_01_000006_add_settings_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('theme_mode', ['light', 'dark'])->default('light');
            $table->enum('event_redirect', ['return_previous', 'visit_updated'])->default('return_previous');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme_mode', 'event_redirect']);
        });
    }
};