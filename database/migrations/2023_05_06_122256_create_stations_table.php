<?php

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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();

            $table->decimal('latitude', 10, 9)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('no action');

            $table->timestamps();
        });

        DB::statement("SELECT AddGeometryColumn('public', 'stations', 'position', 4326, 'POINT', 2)");
        DB::statement('CREATE INDEX sidx_stations_position ON stations USING GIST (position)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
