<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tithis', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sort_order')->unique()->comment('0-indexed: 0=Shukla Pratipada … 14=Purnima, 15=Krishna Pratipada … 29=Amavasya');
            $table->string('name', 30)->comment('Tithi name: Pratipada, Dwitiya… Purnima, Amavasya');
            $table->string('paksha', 10)->comment('Lunar fortnight: Shukla (waxing) or Krishna (waning)');
            $table->unsignedTinyInteger('tithi_number')->comment('Tithi within paksha: 1–15');
            $table->string('ruling_lord', 30)->comment('Planet or deity that rules this tithi');
            $table->string('nature', 30)->comment('Muhurta quality: Nanda, Bhadra, Jaya, Rikta, or Purna');
            $table->string('presiding_deity', 30)->comment('Deity associated with this tithi');
            // Vrat data
            $table->string('vrat_name', 80)->nullable()->comment('Name of the vrat observed on this tithi, if any');
            $table->string('vrat_deity', 40)->nullable()->comment('Deity worshipped during the vrat');
            $table->string('vrat_benefit', 120)->nullable()->comment('Spiritual benefit of observing the vrat');
            $table->string('vrat_ritual', 200)->nullable()->comment('Brief description of the ritual procedure');
            $table->string('vrat_mantra', 120)->nullable()->comment('Primary mantra for the vrat');
            $table->string('vrat_color', 10)->nullable()->comment('UI color for the vrat badge');
            // Muhurta suitability
            $table->string('vivah_suitability', 10)->nullable()->comment('Marriage muhurta suitability: Uttam or Varjit');
            // Pitru Paksha
            $table->string('shraddha_name', 80)->nullable()->comment('Shraddha name for Krishna paksha tithis during Pitru Paksha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tithis');
    }
};
