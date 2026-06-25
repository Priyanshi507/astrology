<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekadashis', function (Blueprint $table) {
            $table->id();
            $table->string('lookup_key', 20)->unique()->comment('Key matching AstroCalculator: Shukla_1, Krishna_1… up to Shukla_12/Krishna_12');
            $table->string('name', 80)->comment('Ekadashi name: Kamada, Varuthini, Nirjala…');
            $table->string('paksha', 10)->comment('Shukla (bright fortnight) or Krishna (dark fortnight)');
            $table->unsignedTinyInteger('vedic_month_number')->comment('1=Chaitra … 12=Phalguna (Purnimanta convention)');
            $table->string('vedic_month_name', 20)->comment('Chaitra, Vaishakha, Jyeshtha… Phalguna');
            $table->string('mantra', 120)->comment('Primary japa mantra for this Ekadashi');
            $table->text('significance_text')->comment('Spiritual significance and merit of observing this Ekadashi');
            $table->json('rituals_list')->comment('Array of recommended rituals and observances');
            $table->string('auspicious_time_note', 100)->comment('Timing guidance, e.g. Sunrise to Dvadashi sunrise');
            $table->string('purana_reference', 120)->nullable()->comment('Source Purana and chapter for this Ekadashi\'s story');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekadashis');
    }
};
