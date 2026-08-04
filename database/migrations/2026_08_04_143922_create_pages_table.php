<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Editable standing pages such as Fixtures, Teams, Coaching and Juniors.
     * Admins edit these in the dashboard; they appear in the main navigation.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body')->nullable();       // Markdown
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_nav')->default(true);
            $table->unsignedSmallInteger('nav_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
