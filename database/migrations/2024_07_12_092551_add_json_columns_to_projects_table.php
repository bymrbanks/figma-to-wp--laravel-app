<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add JSON columns
            $table->json('variables')->nullable()->after('last_imported_date');
            $table->json('patterns')->nullable()->after('variables');
            $table->json('templates')->nullable()->after('patterns');
            $table->json('elements')->nullable()->after('templates');
            $table->json('parts')->nullable()->after('elements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Remove JSON columns
            $table->dropColumn(['variables', 'patterns', 'templates', 'elements', 'parts']);
        });
    }
};
