<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('festival_rules', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique()->comment('URL-safe identifier, e.g. maha-shivratri');
            $table->string('name', 120)->comment('Display name, e.g. Maha Shivratri');
            $table->enum('type', ['festival', 'vrat', 'jayanti'])->comment('Type of observance');
            $table->string('category', 40)->comment('ekadashi, purnima, amavasya, pradosh, chaturthi, ashtami, navratri, shivratri, sankranti, shraddha, jayanti, festival, national, etc.');
            $table->string('icon', 10)->comment('Emoji icon for display');

            // --- trigger ---
            $table->enum('trigger_type', ['tithi', 'solar_ingress', 'gregorian', 'nakshatra_dow', 'dow_masa'])
                  ->comment('How the festival date is determined');

            // Tithi-based fields
            $table->unsignedTinyInteger('tithi_number')->nullable()->comment('Tithi 1-15; null for non-tithi or range-based rules');
            $table->unsignedTinyInteger('tithi_range_start')->nullable()->comment('Start tithi for range-based rules');
            $table->unsignedTinyInteger('tithi_range_end')->nullable()->comment('End tithi for range-based rules');
            $table->string('paksha', 10)->nullable()->comment('Shukla, Krishna, or null (both pakshas)');
            $table->json('masa_filter')->nullable()->comment('Array of masa names this rule applies to; null = any masa');

            // Day-of-week / offset
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Sunday … 6=Saturday; null = any day');
            $table->smallInteger('day_offset')->default(0)->comment('0 = same tithi day, -1 = day before, +1 = day after');

            // Gregorian trigger
            $table->string('gregorian_date', 5)->nullable()->comment('MM-DD for gregorian trigger_type, e.g. 01-26');

            // Nakshatra / solar trigger
            $table->unsignedTinyInteger('nakshatra_index')->nullable()->comment('0-indexed nakshatra number (0=Ashwini … 26=Revati) for nakshatra_dow trigger');
            $table->unsignedTinyInteger('sun_sign_index')->nullable()->comment('0-indexed zodiac sign number (0=Mesha … 11=Meena) for solar_ingress/nakshatra_dow trigger');

            // Recurrence
            $table->boolean('is_recurring')->default(false)->comment('true = monthly recurring; false = annual');

            // Content
            $table->text('significance');
            $table->json('rituals')->comment('Array of ritual strings');
            $table->string('mantra', 300)->nullable();
            $table->text('details')->nullable();
            $table->string('vidhi_title', 100)->nullable()->comment('Optional heading for detailed puja vidhi section');

            // Display grouping
            $table->string('display_group', 40)->nullable()->comment('UI group name for organising festivals: Purnima, Shiva, Ganesha, etc.');
            $table->string('chip_label', 30)->nullable()->comment('Short chip/badge label shown next to the festival name');

            // Sorting
            $table->smallInteger('priority')->default(100)->comment('Lower value = higher priority when multiple festivals share the same day');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festival_rules');
    }
};
