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
            $table->id(); // PK
            $table->unsignedBigInteger('reserva_id'); // FK
            $table->text('alteracoes'); // Descrição das alterações
            $table->timestamp('modificado_em')->useCurrent(); // Data e hora da modificação

            // Chave estrangeira para a tabela reservas
            $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('cascade');

            $table->timestamps(); // Para created_at e updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_reservas');
    }
};
