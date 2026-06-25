<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yogas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique()->comment('Yoga name: Vishkambha, Priti, Ayushman… — id order matches yoga sequence 1-27');
            $table->string('nature', 15)->comment('Auspicious or Inauspicious');
            $table->string('ruling_lord', 20)->comment('Planet that rules this yoga');
            $table->string('presiding_deity', 30)->comment('Deity associated with this yoga');
            $table->string('classification', 15)->comment('Subha (auspicious), Ashubha (inauspicious), or Mahavisha (very inauspicious)');
            $table->string('description', 255)->comment('Short effect/guidance for this yoga');
            $table->string('advice_text', 120)->nullable()->comment('Practical daily advice for observing this yoga');
            $table->string('mood_label', 30)->nullable()->comment('Display label: Auspicious, Inauspicious, Highly Inauspicious');
            $table->string('color_hex', 10)->nullable()->comment('UI accent color keyed to classification');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yogas');
    }
};
