<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reserva extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos
    protected $fillable = [
        'usuario_id',
        'ambiente_id',
        'hora_inicio',
        'hora_fim',
        'status',
    ];

    // Relacionamento com o ambiente
    public function ambiente()
    {
        return $this->belongsTo(Ambientes::class, 'ambiente_id');
    }

    // Relacionamento com o usuário
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relacionamento com o histórico de reservas
    public function historicoReservas()
    {
        return $this->hasMany(Historico_reserva::class, 'reserva_id');
    }
}
