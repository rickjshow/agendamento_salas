<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ambientes extends Model
{
    protected $fillable = [
        'nome',
        'tipo',
        'status',
        'descricao',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'ambiente_id');
    }

}
