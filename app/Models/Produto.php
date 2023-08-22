<?php

//namespace App;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Produto extends Model
{
    use SoftDeletes;

    protected $table = 'produtos';

    // Colunas que são preenchíveis em massa
    protected $fillable = [
        'nome',
        'preco',
        'foto',
    ];

    public $timestamps = true;

    // Atributo para definir o campo de soft delete na tabela
    protected $dates = ['deleted_at'];

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedidos_produtos', 'produto_id', 'pedido_id')
            ->withPivot('quantidade');
    }
}
