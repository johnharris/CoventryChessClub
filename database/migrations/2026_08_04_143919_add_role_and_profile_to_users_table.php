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
        Schema::table('users', function (Blueprint $table) {
            // 'admin' or 'member'. Admins manage the whitelist, all posts and all pages.
            $table->string('role')->default('member')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('display_name')->nullable()->after('name');
            $table->string('ecf_code')->nullable()->after('display_name');
            $table->unsignedSmallInteger('ecf_rating')->nullable()->after('ecf_code');
            $table->text('bio')->nullable()->after('ecf_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'is_active', 'display_name', 'ecf_code', 'ecf_rating', 'bio',
            ]);
        });
    }
};
