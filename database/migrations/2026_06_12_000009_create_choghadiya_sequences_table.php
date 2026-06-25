<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choghadiya_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekday_id')->constrained('weekdays')->comment('Which weekday this sequence belongs to');
            $table->boolean('is_night')->comment('false = day period (sunrise→sunset), true = night period (sunset→sunrise)');
            $table->foreignId('choghadiya_type_id')->constrained('choghadiya_types')->comment('The choghadiya type for this slot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choghadiya_sequences');
    }
};
