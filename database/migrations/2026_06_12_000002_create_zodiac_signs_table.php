<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zodiac_signs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sort_order')->unique()->comment('0=Mesha(Aries) … 11=Meena(Pisces) — used as 0-indexed rashi key in calculations');
            $table->string('name', 30)->comment('Vedic/Sanskrit name: Mesha, Vrishabha…');
            $table->string('english_name', 30)->comment('Western name: Aries, Taurus…');
            $table->string('symbol', 5)->comment('Unicode glyph: ♈ ♉…');
            $table->string('abbreviation', 4)->nullable()->comment('Western 2-letter abbreviation: Ar, Ta, Ge…');
            $table->foreignId('lord_planet_id')->constrained('planets')->comment('Ruling planet of this sign');
            $table->string('element', 10)->comment('Fire, Earth, Air, Water');
            $table->string('modality', 10)->comment('Cardinal, Fixed, Mutable — repeated every 3 signs');
            $table->string('varna', 15)->nullable()->comment('Social order: Brahmin, Kshatriya, Vaishya, Shudra — used in Ashtakoot Varna Koot');
            $table->json('vasya_signs')->nullable()->comment('0-indexed sign indices this sign commands — used in Ashtakoot Vasya Koot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zodiac_signs');
    }
};
