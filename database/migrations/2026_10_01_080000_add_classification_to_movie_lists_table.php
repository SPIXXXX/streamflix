<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movie_lists', function (Blueprint $table) {
            $table->string('classification_type', 20)->nullable()->after('is_featured');
            $table->string('classification', 120)->nullable()->after('classification_type');
        });
    }

    public function down(): void
    {
        Schema::table('movie_lists', function (Blueprint $table) {
            $table->dropColumn(['classification_type', 'classification']);
        });
    }
};
