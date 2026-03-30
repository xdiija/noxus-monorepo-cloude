<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'inscricao_estadual',
        'email',
        'cnpj',
        'phone_1',
        'phone_2',
        'status',
    ];
}
