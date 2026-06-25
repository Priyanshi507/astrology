<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planets', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('vimshottari_order')->unique()->comment('Position in the 120-year Vimshottari dasha cycle: 0=Ketu…8=Mercury');
            $table->string('name', 50)->comment('English display name: Sun, Moon…');
            $table->string('vedic_name', 50)->comment('Sanskrit name: Surya, Chandra, Mangala…');
            $table->string('symbol', 10)->comment('Unicode symbol: ☀ ☽ ♂…');
            $table->string('color_hex', 10)->nullable()->comment('UI color for charts and badges');
            $table->string('abbreviation', 4)->comment('Two-letter chart abbreviation: Su, Mo, Ma…');
            $table->unsignedTinyInteger('vimshottari_dasha_years')->comment('Years in the 120-year dasha cycle');
            $table->boolean('is_always_retrograde')->default(false)->comment('True for Rahu/Ketu — always move retrograde');
            // Extended reference fields (not used in calculations)
            $table->string('nature', 20)->nullable()->comment('Malefic, Benefic, or Neutral');
            $table->string('karaka_en', 80)->nullable()->comment('Significations as natural significator');
            $table->decimal('naisargika_bala', 8, 4)->nullable()->comment('Natural strength in Shadbala');
            $table->decimal('min_shadbala_rupas', 8, 4)->nullable()->comment('Minimum required Shadbala rupas');
            $table->decimal('exaltation_degree', 8, 4)->nullable()->comment('Sidereal longitude of maximum exaltation');
            $table->unsignedTinyInteger('moolatrikona_sign_idx')->nullable()->comment('0-indexed sign number of Moolatrikona sign');
            $table->unsignedTinyInteger('moolatrikona_from')->nullable()->comment('Degree start of Moolatrikona within the sign');
            $table->unsignedTinyInteger('moolatrikona_to')->nullable()->comment('Degree end of Moolatrikona within the sign');
            $table->unsignedTinyInteger('dig_bala_strongest_house')->nullable()->comment('House where this planet has maximum Dig Bala');
            $table->json('friendly_sign_indices')->nullable()->comment('Array of 0-indexed sign numbers where planet is friendly');
            $table->json('enemy_sign_indices')->nullable()->comment('Array of 0-indexed sign numbers where planet is enemy');
            $table->json('gochar_auspicious_houses')->nullable()->comment('Houses considered auspicious for transit (Gochar)');
            $table->tinyInteger('latta_offset')->nullable()->comment('Latta (kick) degree offset for transit danger zones');
            $table->string('gemstone', 40)->nullable()->comment('Associated gemstone for remedial measures');
            $table->string('metal', 30)->nullable()->comment('Associated metal');
            $table->string('lucky_day', 12)->nullable()->comment('Most auspicious weekday for this planet');
            $table->unsignedTinyInteger('numerology_number')->nullable()->comment('Numerological number (1-9)');
            $table->string('significations', 255)->nullable()->comment('Key significations: career, health, relationships');
            $table->string('themes', 255)->nullable()->comment('Thematic associations');
            $table->string('rules_signs', 60)->nullable()->comment('Signs this planet rules');
            $table->string('exaltation_text', 80)->nullable()->comment('Description of exaltation sign and degree');
            $table->string('debilitation_text', 80)->nullable()->comment('Description of debilitation sign and degree');
            $table->string('hora_quality', 10)->nullable()->comment('Day or Night hora quality');
            $table->string('hora_metal', 30)->nullable()->comment('Metal associated with this planet\'s hora');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planets');
    }
};
