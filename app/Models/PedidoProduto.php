<?php
// app/Models/PedidoProduto.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoProduto extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos_produtos';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
    ];

    protected $dates = ['deleted_at'];
}
