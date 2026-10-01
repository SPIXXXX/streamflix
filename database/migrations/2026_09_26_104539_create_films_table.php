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
    Schema::create('films', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('synopsis')->nullable();
        $table->string('genre')->nullable();
        $table->unsignedSmallInteger('release_year')->nullable();
        $table->string('poster_path')->nullable();
        $table->string('cast')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
