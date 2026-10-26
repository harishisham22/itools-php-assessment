<?php

use App\BuildingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->enum('building_type', [BuildingType::COMMERCIAL, BuildingType::RESIDENTIAL])->nullable();
            $table->string('category');
            $table->integer('min_kwh')->default(0);
            $table->integer('max_kwh')->nullable();
            $table->float('rate')->default(0);
            $table->boolean('is_initial_rate')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
