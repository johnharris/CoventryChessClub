<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A lead photograph for a post, shown at the top of the article and used as the
 * thumbnail on the blog index.
 *
 * References media by id and is nulled rather than cascaded if the image is
 * deleted, so removing a photograph never removes the post that used it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('featured_image_id')
                ->nullable()
                ->after('excerpt')
                ->constrained('media')
                ->nullOnDelete();

            $table->string('featured_image_caption')->nullable()->after('featured_image_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('featured_image_id');
            $table->dropColumn('featured_image_caption');
        });
    }
};
