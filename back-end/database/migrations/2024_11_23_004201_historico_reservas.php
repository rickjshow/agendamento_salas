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
        Schema::create('historico_reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id');
            $table->text('alteracoes');
            $table->timestamp('modificado_em')->useCurrent();
            $table->foreign('reserva_id')->references('id')->on('reservas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_reservas');
    }
};
