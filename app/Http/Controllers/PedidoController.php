<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\PedidoProduto;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Emails\PedidoConfirmadoEmail;
use Carbon\Carbon;


class PedidoController extends Controller
{
    private $pedido;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    public function index()
{
    $perPage = 10;
    $page = request()->query('page', 1);

    $pedidos = Pedido::select('pedidos.id as id_pedido', 'clientes.id as id_cliente', 'clientes.nome as nome_cliente', 'pedidos.data_criacao')
        ->selectRaw('SUM(produtos.preco * pedidos_produtos.quantidade) as preco_total')
        ->join('clientes', 'clientes.id', '=', 'pedidos.cliente_id')
        ->join('pedidos_produtos', 'pedidos_produtos.pedido_id', '=', 'pedidos.id')
        ->join('produtos', 'produtos.id', '=', 'pedidos_produtos.produto_id')
        ->groupBy('pedidos.id', 'clientes.id', 'clientes.nome', 'pedidos.data_criacao')
        ->paginate($perPage, ['*'], 'page', $page);

    $pedidosFormatados = $pedidos->map(function ($pedido) {
        return [
            'id_pedido' => $pedido->id_pedido,
            'id_cliente' => $pedido->id_cliente,
            'Nome Cliente' => $pedido->nome_cliente,
            'Preço Total' => 'R$ ' . number_format($pedido->preco_total, 2, ',', '.'),
            'Data Criação' => Carbon::parse($pedido->data_criacao)->format('d/m/Y H:i:s'),
        ];
    });

    $response = [
        'Lista de pedidos' => $pedidosFormatados, // Alteração na chave "data"
        'paginação' => [
            'total' => $pedidos->total(),
            'Itens por página' => $pedidos->perPage(),
            'página atual' => $pedidos->currentPage(),
            'última página' => $pedidos->lastPage(),
            'URL próxima página' => $pedidos->nextPageUrl(),
            'URL página anterior' => $pedidos->previousPageUrl(),
            'de' => $pedidos->firstItem(),
            'to' => $pedidos->lastItem(),
        ],
    ];

    return response()->json($response, 200);
}

    public function show($id)
    {
        try {
            $pedido = Pedido::with('cliente')->find($id);

            if (!$pedido) {
                return response()->json(['message' => 'Pedido não encontrado'], 404);
            }

            $produtosPedido = DB::table('pedidos_produtos')
                                ->select('pedidos_produtos.quantidade', 'produtos.id as produto_id', 'produtos.nome as nome_produto', 'produtos.preco')
                                ->join('produtos', 'produtos.id', '=', 'pedidos_produtos.produto_id')
                                ->where('pedidos_produtos.pedido_id', $id)
                                ->get();

            $detalhesPedido = [
                'id_pedido' => $pedido->id,
                'cliente' => [
                    'id_cliente' => $pedido->cliente->id,
                    'nome' => $pedido->cliente->nome
                ],
                'data_criacao' => Carbon::parse($pedido->data_criacao)->format('d/m/Y H:i:s'),
                'itens' => [],
                'valor_total' => 0,
            ];

            if ($produtosPedido->isEmpty()) {
                return response()->json(['message' => 'Nenhum item encontrado para este pedido'], 200);
            }

            foreach ($produtosPedido as $produtoPedido) {
                $quantidade = $produtoPedido->quantidade;
                $valorTotalItem = $produtoPedido->preco * $quantidade;

                $detalhesPedido['itens'][] = [
                    'produto_id' => $produtoPedido->produto_id,
                    'Nome Produto' => $produtoPedido->nome_produto,
                    'Quantidade' => $quantidade,
                    'Preco unitário' => number_format($produtoPedido->preco, 2, ',', '.'),
                    'Valor Total' => number_format($valorTotalItem, 2, ',', '.'),
                ];

                $detalhesPedido['valor_total'] += $valorTotalItem;
            }

            $detalhesPedido['valor_total'] = number_format($detalhesPedido['valor_total'], 2, ',', '.');

            return response()->json($detalhesPedido);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Ocorreu um erro ao buscar o pedido'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'bail|required|exists:clientes,id',
            'status' => 'bail|nullable|in:ativo,inativo,cancelado',
            'produtosQuantidades' => 'bail|required|array',
            'produtosQuantidades.*.produto_id' => 'bail|required|exists:produtos,id',
            'produtosQuantidades.*.quantidade' => 'bail|required|numeric|min:1',
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'exists' => 'O :attribute selecionado não existe.',
            'in' => 'O :attribute selecionado é inválido.',
            'array' => 'O campo :attribute deve ser um array.',
            'numeric' => 'O campo :attribute deve ser numérico.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pedido = $this->pedido->create([
            'cliente_id' => $request->input('cliente_id'),
            'status' => $request->input('status', 'ativo'),
        ]);

        $detalhesPedido = [
            'id_pedido' => $pedido->id,
            'cliente' => [
                'id' => $pedido->cliente_id,
                'nome' => $pedido->cliente->nome,
            ],
            'status' => $pedido->status,
            'preco_total' => 0, // Inicializa o preço total como zero
            'itens' => [],
            'data_cadastro' => Carbon::parse($pedido->created_at)->format('d/m/Y H:i:s'),
        ];

        foreach ($request->input('produtosQuantidades') as $produtoQuantidade) {
            $produto_id = $produtoQuantidade['produto_id'];
            $quantidade = $produtoQuantidade['quantidade'];

            $produto = Produto::find($produto_id);
            if (!$produto) {
                return response()->json(['error' => "Produto com ID $produto_id não encontrado"], 404);
            }

            $valorTotalItem = $produto->preco * $quantidade;

            $detalhesPedido['itens'][] = [
                'produto_id' => $produto_id,
                'Nome produto' => $produto->nome,
                'Preço unitário' => 'R$ ' . number_format($produto->preco, 2, ',', '.'),
                'Quantidade' => $quantidade,
                'Valor Total' => 'R$ ' . number_format($valorTotalItem, 2, ',', '.'),
            ];

            $detalhesPedido['preco_total'] += $valorTotalItem;

            DB::table('pedidos_produtos')->insert([
                'pedido_id' => $pedido->id,
                'produto_id' => $produto_id,
                'quantidade' => $quantidade,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $pedido->update(['preco_total' => $detalhesPedido['preco_total']]);

        $detalhesPedido['preco_total'] = 'R$ ' . number_format($detalhesPedido['preco_total'], 2, ',', '.');

        return response()->json([
            'Detalhes do Pedido' => $detalhesPedido,
            'mensagem' => 'Pedido incluído com sucesso!'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['error' => "Pedido com ID $id não encontrado"], 404);
        }

        $pedido->update([
            'cliente_id' => $request->input('cliente_id'),
            'data_criacao' => $request->input('data_criacao'),
            'status' => $request->input('status', 'ativo'),
        ]);

        $pedido->produtos()->detach();
        $precoTotal = 0;

        foreach ($request->input('produtosQuantidades') as $produtoQuantidade) {
            $produto_id = $produtoQuantidade['produto_id'];
            $quantidade = $produtoQuantidade['quantidade'];

            $produto = Produto::find($produto_id);
            if (!$produto) {
                return response()->json(['error' => "Produto com ID $produto_id não encontrado"], 404);
            }

            $pedido->produtos()->attach($produto_id, ['quantidade' => $quantidade]);

            $precoTotal += $produto->preco * $quantidade;
        }

        $pedido->preco_total = number_format($precoTotal, 2, '.', '');
        $pedido->data_criacao = Carbon::parse($request->input('data_criacao'))->format('Y-m-d H:i:s');
        $pedido->save();

        $pedido->load('cliente');
        $pedidoData = [
            'Número do pedido ID' => $pedido->id,
            'cliente_id' => $pedido->cliente_id,
            'Nome Cliente' => $pedido->cliente->nome,
            'Data Criação' => $pedido->data_criacao,
            'Preço Total' => $pedido->preco_total,
            'Status' => $pedido->status,
            'Data Alteração' => $pedido->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'Pedido Atualizado' => $pedidoData,
            'mensagem' => 'Pedido atualizado com sucesso!'
        ], 200);
    }

    public function destroy($id)
    {
        $pedido = Pedido::withTrashed()->find($id);

        if (!$pedido) {
            return response()->json(['mensagem' => 'Pedido não encontrado'], 404);
        }

        if ($pedido->trashed()) {
            return response()->json(['mensagem' => 'Pedido já está inativo'], 400);
        }

        $pedido->status = 'cancelado';
        $pedido->save();
        $pedido->delete();

        $precoTotalFormatado = number_format($pedido->preco_total, 2, ',', '.');

        $dataAlteracaoFormatada = Carbon::parse($pedido->updated_at)->format('d/m/Y H:i:s');

        $deletedAt = $pedido->deleted_at
            ? Carbon::parse($pedido->deleted_at)->format('d/m/Y H:i:s')
            : null;

        return response()->json([
            'Pedido Cancelado' => [
                'Número do pedido ID' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'Nome Cliente' => $pedido->cliente->nome,
                'Data Criação' => $pedido->data_criacao,
                'Preço Total' => $precoTotalFormatado,
                'Status' => 'cancelado',
                'Data Alteracao' => $dataAlteracaoFormatada,
                'Cancelado em' => $deletedAt,
            ],
            'mensagem' => 'Pedido cancelado com sucesso'
        ], 200);
    }

}
