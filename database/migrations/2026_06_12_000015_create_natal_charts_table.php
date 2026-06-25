<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('natal_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Owner; NULL for guest/anonymous charts');
            $table->string('chart_label', 80)->default('My Chart')->comment('User-given name for this chart');
            $table->date('birth_date');
            $table->time('birth_time')->nullable()->comment('Local time of birth; NULL means noon was used');
            $table->string('birth_city_name', 100)->nullable();
            $table->decimal('birth_latitude', 9, 6)->comment('Decimal degrees, negative = South');
            $table->decimal('birth_longitude', 9, 6)->comment('Decimal degrees, negative = West');
            $table->decimal('birth_utc_offset', 4, 2)->comment('UTC offset at birth location, e.g. 5.5 for IST');
            $table->string('birth_timezone_identifier', 50)->nullable();
            $table->json('planet_positions_json')->comment('Computed sidereal longitudes for all 9 planets plus ASC — never re-computed from DB');
            $table->decimal('ascendant_degree', 8, 4)->comment('Sidereal ascendant degree (0–360)');
            $table->foreignId('ascendant_zodiac_sign_id')->nullable()->constrained('zodiac_signs')->nullOnDelete();
            $table->decimal('ayanamsa_value', 8, 6)->comment('Lahiri ayanamsa at birth JD');
            $table->json('vimshottari_dasha_balance_json')->nullable()->comment('Dasha balance at birth: lord, years remaining, sub-periods');
            $table->boolean('is_primary_chart')->default(false)->comment('One primary chart per user for quick access');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('natal_charts');
    }
};
