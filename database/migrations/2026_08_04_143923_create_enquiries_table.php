<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Contact form submissions. Stored in the database so nothing is lost if
     * outbound email is not configured, and optionally emailed on as well.
     */
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            // General interest, junior section, coaching, etc.
            $table->string('enquiry_type')->default('general');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['is_archived', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
