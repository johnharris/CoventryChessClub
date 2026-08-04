<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The whitelist is the gate for account creation: an email address must
     * appear here, and be unclaimed, before anyone can register an account.
     */
    public function up(): void
    {
        Schema::create('whitelist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            // Role the account is given when the invitation is claimed.
            $table->string('role')->default('member');
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('claimed_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('invited_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whitelist_entries');
    }
};
