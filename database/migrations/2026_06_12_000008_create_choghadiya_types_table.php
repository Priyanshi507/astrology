<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choghadiya_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sequence_index')->unique()->comment('0=Rog 1=Char 2=Labha 3=Amrit 4=Kaal 5=Shubha 6=Udveg — index used in choghadiya sequence arrays');
            $table->string('name', 10)->comment('Choghadiya name: Rog, Char, Labha, Amrit, Kaal, Shubha, Udveg');
            $table->string('ruling_planet', 12)->comment('Planet that governs this choghadiya period');
            $table->string('nature', 25)->comment('Inauspicious, Neutral, Auspicious, or Highly Auspicious');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choghadiya_types');
    }
};
