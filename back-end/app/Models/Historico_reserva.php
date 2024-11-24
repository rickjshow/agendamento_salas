<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historico_reserva extends Model
{
    protected $table = 'historico_reservas';

    protected $fillable = [
        'reserva_id',
        'nome_usuario_responsavel',
        'nome_usuario_alteracao',
        'nome_ambiente',
        'hora_inicio',
        'hora_fim',
        'alteracoes',
        'modificado_em',
    ];

    public $timestamps = false;

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
