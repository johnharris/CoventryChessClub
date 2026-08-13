<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Images uploaded by members for use in posts.
 *
 * Recorded in a table rather than simply left on disk so the club can see what
 * has been uploaded, who uploaded it, and remove it again — which matters for
 * photographs of people, and for keeping a shared hosting account inside its
 * disk allowance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Nulled rather than deleted if a member's account is removed, so
            // that images already used in published posts do not vanish.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Relative paths on the public disk, e.g. uploads/2026/08/name.jpg
            $table->string('path');
            $table->string('display_path')->nullable();
            $table->string('thumb_path')->nullable();

            $table->string('original_name');
            $table->string('mime_type', 60);
            $table->unsignedInteger('size');
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();

            // Alternative text, for readers using a screen reader and for when
            // an image fails to load.
            $table->string('alt_text')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
