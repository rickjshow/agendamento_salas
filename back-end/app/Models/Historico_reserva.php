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

    public $timestamps = false; 
}

