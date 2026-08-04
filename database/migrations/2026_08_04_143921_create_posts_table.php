<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A post is either an ordinary article ('general'), a single chess position
     * ('position', built from a FEN) or an annotated game ('game', built from a
     * PGN). The chess columns are simply ignored for general posts.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('general');   // general | position | game
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();         // Markdown

            // --- Chess: single position posts ---
            $table->string('fen')->nullable();
            $table->string('orientation')->default('white'); // white | black
            $table->string('side_to_move')->nullable();      // w | b, derived from the FEN
            $table->string('caption')->nullable();           // e.g. "White to play and win"
            $table->string('solution')->nullable();          // revealed on click

            // --- Chess: annotated game posts ---
            $table->longText('pgn')->nullable();
            $table->string('white_player')->nullable();
            $table->string('black_player')->nullable();
            $table->string('result')->nullable();            // 1-0 | 0-1 | 1/2-1/2 | *
            $table->string('event')->nullable();
            $table->date('played_on')->nullable();

            // --- Publishing ---
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
