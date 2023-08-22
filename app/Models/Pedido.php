<?php

// app/Models/Pedido.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'preco_total',
        'data_criacao',
        'status',
    ];

    protected $dates = ['deleted_at'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relacionamento muitos-para-muitos com a tabela de produtos através da tabela de relacionamento pedidos_produtos
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'pedidos_produtos', 'pedido_id', 'produto_id')
                    ->withPivot('quantidade');
    }

    // Relacionamento direto com a tabela de produtos
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
