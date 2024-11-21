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
    
}
