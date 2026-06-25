<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nakshatras', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sort_order')->unique()->comment('1=Ashwini … 27=Revati — 1-based, used as (sort_order - 1) for 0-indexed nakshatra number');
            $table->string('name', 40)->comment('Nakshatra name: Ashwini, Bharani…');
            $table->string('deity', 60)->comment('Presiding deity: Ashwini Kumaras, Yama…');
            $table->foreignId('lord_planet_id')->constrained('planets')->comment('Vimshottari dasha lord of this nakshatra');
            $table->foreignId('zodiac_sign_id')->constrained('zodiac_signs')->comment('Primary zodiac sign where this nakshatra resides');
            $table->decimal('starting_degree', 8, 4)->comment('Sidereal longitude where this nakshatra begins (0-360)');
            $table->string('gana', 12)->comment('Temperament: Deva, Manushya, or Rakshasa — used in marriage compatibility');
            $table->string('yoni', 20)->comment('Animal symbol: Horse, Elephant, Serpent… — used in marriage compatibility');
            $table->string('nadi', 10)->comment('Nadi Dosha group: Adi, Madhya, or Antya — critical for marriage compatibility');
            $table->string('tattva', 10)->comment('Pancha Bhuta element cycle (repeating): Fire, Earth, Ether, Air, Water');
            $table->string('guna', 10)->comment('Quality cycle (repeating 9×3): Sattvic, Rajasic, Tamasic');
            $table->string('muhurta_quality', 30)->comment('Muhurta quality name: Kshipra, Ugra, Dhruva, Mridu, Chara, Tikshna, Mishra');
            $table->unsignedTinyInteger('muhurta_auspiciousness_score')->comment('0=Highly Auspicious, 1=Auspicious, 2=Moderate, 3=Inauspicious, 4=Highly Inauspicious');
            // Muhurta suitability columns
            $table->string('vivah_suitability', 10)->nullable()->comment('Marriage muhurta: Uttam, Madhyam, or Varjit');
            $table->string('griha_pravesh_suitability', 10)->nullable()->comment('House entry muhurta: Good, Bad, or Neutral');
            $table->string('vahana_suitability', 10)->nullable()->comment('Vehicle purchase muhurta: Good, Bad, or Neutral');
            $table->string('mundan_suitability', 10)->nullable()->comment('First haircut muhurta: Good, Bad, or Neutral');
            $table->string('sampatti_suitability', 10)->nullable()->comment('Property/wealth muhurta: Good, Bad, or Neutral');
            $table->boolean('is_panchak')->default(false)->comment('True for the 5 nakshatras in Panchak (Dhanishtha through Revati)');
            // Display/reference fields
            $table->string('good_for', 120)->nullable()->comment('Activities especially suited to this nakshatra');
            $table->string('avoid', 80)->nullable()->comment('Activities to avoid during this nakshatra');
            $table->string('display_color', 10)->nullable()->comment('UI accent color for this nakshatra');
            $table->text('deity_description')->nullable()->comment('Extended description of the presiding deity');
            $table->text('description')->nullable()->comment('Nakshatra mythology and significance');
            $table->string('muhurta_type_label', 40)->nullable()->comment('Muhurta type label e.g. Laghu/Kshipra, Sthira/Dhruva');
            $table->string('muhurta_type_desc', 120)->nullable()->comment('Short description of the muhurta type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nakshatras');
    }
};
