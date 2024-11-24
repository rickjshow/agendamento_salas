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
            $table->string('nome_usuario_responsavel')->nullable();
            $table->string('nome_usuario_alteracao')->nullable();
            $table->text('alteracoes');
            $table->string('nome_ambiente')->nullable();
            $table->timestamp('hora_inicio')->nullable();
            $table->timestamp('hora_fim')->nullable();
            $table->timestamp('modificado_em')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_reservas');
    }
};
