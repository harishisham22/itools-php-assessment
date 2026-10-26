<?php

use App\BuildingType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class)->constrained();
            $table->enum('type', [BuildingType::COMMERCIAL, BuildingType::RESIDENTIAL])->nullable();
            $table->integer('month')->nullable();
            $table->float('usability')->default(0);
            $table->string('state')->nullable();
            $table->float('bill')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
