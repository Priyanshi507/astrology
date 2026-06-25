<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('murtis', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('murti_index')->unique()->comment('Formula result: (Vara + Birth Nak + Moon Nak) mod 4 → 0=Swarna … 3=Loha');
            $table->string('name', 30)->comment('Metal name: Swarna (Gold), Rajata (Silver), Tamra (Copper), Loha (Iron)');
            $table->string('symbol', 5)->comment('Display symbol: ◈ ◇ ◉ ◆');
            $table->string('quality_description', 25)->comment('Highly Auspicious, Auspicious, Moderate, or Inauspicious');
            $table->unsignedTinyInteger('rank_order')->comment('1=best (Swarna) … 4=worst (Loha)');
            $table->string('bphs_reference', 120)->comment('Source from BPHS Chapter 87 + Muhurta Chintamani');
            $table->text('phala_description')->comment('Detailed effect for marriage, travel, and business');
            $table->string('upaya_remedy', 120)->comment('Remedial measure (upaya) to improve inauspicious murti');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('murtis');
    }
};
