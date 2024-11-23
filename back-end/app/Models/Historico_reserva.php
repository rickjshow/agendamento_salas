<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historico_reserva extends Model
{
    protected $table = 'historico_reservas';

    protected $fillable = [
        'reserva_id',
        'alteracoes',
        'modificado_em',
    ];

    public $timestamps = false; // Como você usa 'modificado_em', desative timestamps automáticos.

    // Relacionamento com a reserva
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
