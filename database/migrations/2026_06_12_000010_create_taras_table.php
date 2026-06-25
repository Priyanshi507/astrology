<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taras', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tara_number')->unique()->comment('1=Janma … 9=Atimitra — distance group from birth nakshatra');
            $table->string('name', 20)->comment('Tara name: Janma, Sampat, Vipat, Kshema, Pratyari, Sadhaka, Naidhana, Mitra, Atimitra');
            $table->boolean('is_auspicious')->comment('Whether this tara is considered auspicious for muhurta selection');
            $table->string('auspiciousness_type', 25)->comment('Neutral, Auspicious, Inauspicious, or Highly Auspicious');
            $table->string('icon_symbol', 5)->comment('Display icon: ✦ ✗ ★ ⚠');
            $table->string('bphs_reference', 120)->comment('Source reference from Brihat Parashara Hora Shastra Chapter 26');
            $table->string('phala_description', 255)->comment('Effect and practical guidance for this tara');
            $table->smallInteger('scoring_bonus')->comment('Muhurta score adjustment: positive for auspicious taras, negative for inauspicious');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taras');
    }
};
