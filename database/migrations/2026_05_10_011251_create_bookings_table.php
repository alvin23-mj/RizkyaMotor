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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('meeting_date');
            $table->string('meeting_time');
            $table->string('type'); // 'pembelian' (buying inspection) or 'penjualan' (selling appraisal)
            $table->string('car_brand')->nullable();
            $table->string('car_model')->nullable();
            $table->integer('car_year')->nullable();
            $table->bigInteger('car_price')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('menunggu'); // menunggu, disetujui, selesai, dibatalkan
            $table->timestamps();

            $table->foreign('car_id')->references('id')->on('cars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
