<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacoes extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = ['usuario_id', 'mensagem', 'tipo', 'criado_em'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
