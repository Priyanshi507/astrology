<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karanas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique()->comment('Karana name: Bava, Balava… Naga — id order (1-11) matches lookup sequence');
            $table->string('ruling_lord', 20)->comment('Ruling deity or planet');
            $table->string('nature', 20)->comment('Movable, Auspicious, Mixed, or Inauspicious');
            $table->string('karana_type', 10)->comment('Chara (movable, repeats 8× per month) or Sthira (fixed, appears once)');
            $table->string('presiding_deity', 20)->comment('Deity of this karana');
            $table->string('favourable_activities', 120)->comment('Activities best suited to this karana');
            $table->string('display_name', 30)->nullable()->comment('Display name shown in the UI; falls back to name if null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karanas');
    }
};
