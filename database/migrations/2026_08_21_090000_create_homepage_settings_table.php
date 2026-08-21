<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_settings', function (Blueprint $table): void {
            $table->id();
            $table->text('hero_fen');
            $table->string('hero_orientation', 5)->default('white');
            $table->string('hero_caption', 180)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
