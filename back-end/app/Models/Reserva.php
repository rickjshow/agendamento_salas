<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'usuario_id',
        'ambiente_id',
        'hora_inicio',
        'hora_fim',
        'status',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ambiente()
    {
        return $this->belongsTo(Ambientes::class, 'ambiente_id');
    }
}
