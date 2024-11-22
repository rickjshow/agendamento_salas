<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->unsignedBigInteger('usuario_id'); 
            $table->text('mensagem'); 
            $table->string('tipo'); // Tipo de notificação (ex: lembrete, reserva, cancelamento)
            $table->timestamp('criado_em'); // Data e hora de criação da notificação
            $table->timestamps(); // Campos created_at e updated_at automáticos

            // Chave estrangeira
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificacoes');
    }
};
