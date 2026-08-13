<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records how strong an enquirer says they are, so whoever is on the door on a
 * club night has some idea what to expect before the person arrives.
 *
 * Nullable on purpose: the question is optional on the form, and any enquiries
 * recorded before this column existed have no answer to give.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('playing_strength', 20)
                ->nullable()
                ->after('enquiry_type');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('playing_strength');
        });
    }
};
