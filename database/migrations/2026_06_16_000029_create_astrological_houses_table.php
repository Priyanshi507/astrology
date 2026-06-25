<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('astrological_houses', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedTinyInteger('house_number')->unique();
            $table->string('description_en', 120);
            $table->text('key_themes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('astrological_houses');
    }
};
