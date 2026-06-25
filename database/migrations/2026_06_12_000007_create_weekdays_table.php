<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekdays', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('dow_index')->unique()->comment('0=Sunday … 6=Saturday — matches PHP date(\'w\') day-of-week number');
            $table->string('name', 20)->comment('Sanskrit name: Ravivara, Somavara…');
            $table->string('english_name', 12)->comment('English: Sunday, Monday…');
            $table->foreignId('lord_planet_id')->constrained('planets')->comment('Ruling planet of this weekday');
            $table->string('symbol', 5)->comment('Planet symbol: ☀ ☽ ♂…');
            $table->string('nature', 25)->comment('Muhurta temperament: Ugra (Fierce), Saumya (Gentle), Guru (Auspicious), Sthira (Stable)');
            $table->string('presiding_deity', 20)->comment('Primary deity worshipped on this day');
            $table->string('auspicious_activities', 120)->comment('Activities traditionally recommended on this day');
            // Extended display fields
            $table->string('deity_note', 80)->nullable()->comment('Short note about the presiding deity');
            $table->string('classification', 20)->nullable()->comment('Ugra, Saumya, Guru, or Sthira');
            $table->string('classification_note', 80)->nullable()->comment('Description of the classification');
            $table->text('info_text')->nullable()->comment('Extended paragraph about this weekday\'s significance');
            $table->json('vrats')->nullable()->comment('Array of vrat objects: name, deity, benefit, ritual, mantra, color');
            // Muhurta timing data (1-based part number within 8-equal-parts of daylight)
            $table->unsignedTinyInteger('rahu_kala_part')->nullable()->comment('Which 1/8 part of daylight is Rahu Kala (1-8)');
            $table->unsignedTinyInteger('yamaganda_part')->nullable()->comment('Which 1/8 part of daylight is Yamaganda/Yama Ghantam (1-8)');
            $table->unsignedTinyInteger('gulika_part')->nullable()->comment('Which 1/8 part of daylight is Gulika Kala (1-8)');
            $table->json('durmuhurta_parts')->nullable()->comment('Array of 1/8 part numbers that are Durmuhurta for this day');
            $table->json('gowri_sequence')->nullable()->comment('Array of 8 choghadiya_type sequence_index values for Gowri Panchangam');
            $table->string('vivah_suitability', 10)->nullable()->comment('Marriage day suitability: Uttam, Madhyam, or Varjit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekdays');
    }
};
