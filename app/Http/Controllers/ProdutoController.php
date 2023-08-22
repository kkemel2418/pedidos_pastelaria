<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

use App\Models\Produto;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class ProdutoController extends Controller
{

    private $produto;

    public function __construct(Produto $produto)
    {
        $this->produto = $produto;
    }

    public function index()
    {
        $perPage = 10;
        $page = request()->query('page', 1);

        $produtos = Produto::paginate($perPage, ['*'], 'page', $page);

        // Formatando os produtos e as datas para o padrão brasileiro
        $produtosFormatados = $produtos->map(function ($produto) {
            return [
                'id_produto' => $produto->id,
                'Nome Produto' => $produto->nome,
                'Preço' => number_format($produto->preco, 2, ',', '.'),
                'Foto' => '/public' . $produto->foto,
                'Status' => $produto->status,
                'Data Criação' => $produto->created_at->format('d/m/Y H:i:s'),
                'Data Atualização' => $produto->updated_at->format('d/m/Y H:i:s'),
            ];
        });

        $response = [
            'Lista de produtos' => $produtosFormatados, // Alteração da chave "data" para "Lista de produtos"
            'paginação' => [
                'total' => $produtos->total(),
                'itens por pag' => $produtos->perPage(),
                'pag atual' => $produtos->currentPage(),
                'última pag' => $produtos->lastPage(),
                'URL próxima pagina' => $produtos->nextPageUrl(),
                'URL página anterior' => $produtos->previousPageUrl(),
                'de' => $produtos->firstItem(),
                'para' => $produtos->lastItem(),
            ],
        ];

        return response()->json($response, 200);
    }

    public function show($id)
    {
        try {
            $produto = $this->produto->findOrFail($id);

            $produtoEmPortugues = [
                'id_produto' => $produto->id,
                'Nome produto' => $produto->nome,
                'Preço' => number_format($produto->preco, 2, ',', '.'),
                'Foto' => '/public' . $produto->foto,
                'Status' => $produto->status,
                'Criado em' => Carbon::parse($produto->created_at)->format('d/m/Y H:i:s'),
                'Atualizado em' => Carbon::parse($produto->updated_at)->format('d/m/Y H:i:s'),
            ];

            return response()->json([
                'Detalhes do produto' => $produtoEmPortugues, // Alteração da chave "data" para "Detalhes do produto"
                'mensagem' => 'Produto encontrado com sucesso!',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'mensagem' => 'Produto não encontrado',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nome' => 'required',
            'preco' => 'required|numeric',
            'foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'nome.required' => 'O campo nome é obrigatório.',
            'preco.required' => 'O campo preço é obrigatório.',
            'preco.numeric' => 'O campo preço deve ser um valor numérico com duas casas decimais (por exemplo, 10.50).',
            'foto.image' => 'O arquivo enviado não é uma imagem válida.',
            'foto.mimes' => 'O arquivo enviado deve ser do tipo jpeg, png, jpg ou gif.',
            'foto.max' => 'O tamanho máximo permitido para a imagem é de 2MB.'
        ]);

        $data = $request->all(); // Inicialize a variável $data com todos os dados do request

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nomeFoto = $foto->getClientOriginalName();

            // Salve a foto na pasta "public/images" com o nome original da foto
            $foto->storeAs('public/images', $nomeFoto);
            // Adicione o campo "foto" com o caminho completo da imagem no array $data
            $data['foto'] = '/images/' . $nomeFoto;
        }

        $produto = $this->produto->create($data); // Use a variável $data para criar o produto

        $dataCriacao = Carbon::parse($produto->created_at)->format('d/m/Y H:i:s');
        $dataAtualizacao = Carbon::parse($produto->updated_at)->format('d/m/Y H:i:s');

        $produtoRenomeado = [
            'Nome produto' => $produto->nome,
            'Preco' => $produto->preco,
            'Data Criação' => $dataCriacao,
            'Data Atualização' => $dataAtualizacao,
            'Foto' => '/public' . $produto->foto,
            'id_produto' => $produto->id,
        ];

        return response()->json(['Produto incluído' => $produtoRenomeado, 'mensagem' => 'Produto incluído com sucesso!'], 201);
    }



   /* public function update(Request $request, $id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        $messages = [
            'preco.numeric' => 'O campo preço deve ser um valor numérico com duas casas decimais (por exemplo, 10.50).',
            'foto.image' => 'O arquivo enviado não é uma imagem válida.',
            'foto.mimes' => 'O arquivo enviado deve ser do tipo jpeg, png, jpg ou gif.',
            'foto.max' => 'O tamanho máximo permitido para a imagem é de 2MB.'
        ];

        $this->validate($request, [
            'preco' => 'numeric',
            'foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nomeFoto = $foto->getClientOriginalName();

            // Salvar a imagem na pasta "public/images"
            $foto->storeAs('images', $nomeFoto, 'public');

            // Definir o caminho completo da imagem no objeto Produto
            $produto->foto = '/images/' . $nomeFoto;
        }

        if ($request->has('nome')) {
            $produto->nome = $request->input('nome');
        }

        if ($request->has('preco')) {
            $produto->preco = $request->input('preco');
        }

        $produto->save();

        $dataCriacao = Carbon::parse($produto->created_at)->format('d/m/Y H:i:s');
        $dataAtualizacao = Carbon::parse($produto->updated_at)->format('d/m/Y H:i:s');

        $produtoRenomeado = [
            'Nome Produto' => $produto->nome,
            'Preço' => $produto->preco,
            'Data Criação' => $dataCriacao,
            'Data Atualização' => $dataAtualizacao,
            'Foto' => $produto->foto, // Apenas o nome do arquivo será retornado
            'id_produto' => $produto->id,
        ];

        return response()->json(['Produto Atualizado' => $produtoRenomeado, 'mensagem' => 'Produto atualizado com sucesso!'], 201);
    }*/

    public function update(Request $request, $id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        $messages = [
            'preco.numeric' => 'O campo preço deve ser um valor numérico com duas casas decimais (por exemplo, 10.50).',
            'foto.image' => 'O arquivo enviado não é uma imagem válida.',
            'foto.mimes' => 'O arquivo enviado deve ser do tipo jpeg, png, jpg ou gif.',
            'foto.max' => 'O tamanho máximo permitido para a imagem é de 2MB.'
        ];

        $this->validate($request, [
            'preco' => 'numeric',
            'foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nomeFoto = $foto->getClientOriginalName();

            // Salvar a imagem na pasta "public/images"
            $foto->storeAs('public/images', $nomeFoto);

            // Definir o caminho completo da imagem no objeto Produto
            $produto->foto = '/images/' . $nomeFoto;
        }

        if ($request->has('nome')) {
            $produto->nome = $request->input('nome');
        }

        if ($request->has('preco')) {
            $produto->preco = $request->input('preco');
        }

        $produto->save();

        $dataCriacao = Carbon::parse($produto->created_at)->format('d/m/Y H:i:s');
        $dataAtualizacao = Carbon::parse($produto->updated_at)->format('d/m/Y H:i:s');

        $produtoRenomeado = [
            'Nome Produto' => $produto->nome,
            'Preço' => $produto->preco,
            'Data Criação' => $dataCriacao,
            'Data Atualização' => $dataAtualizacao,
            'Foto' => '/public' . $produto->foto,
            'id_produto' => $produto->id,
        ];

        return response()->json(['Produto Atualizado' => $produtoRenomeado, 'mensagem' => 'Produto atualizado com sucesso!'], 201);
    }


    public function destroy($id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        $produto->delete();
        $produto->status = 'cancelado';
        $produto->save();

        $dataCriacao = Carbon::parse($produto->created_at)->format('d/m/Y H:i:s');
        $dataAtualizacao = Carbon::parse($produto->updated_at)->format('d/m/Y H:i:s');

        $produtoRenomeado = [
            'id_produto' => $produto->id,
            'Nome Produto' => $produto->nome,
            'Preco' => number_format($produto->preco, 2, ',', '.'),
            'Foto' => '/public' . $produto->foto,
            'Status' => $produto->status,
            'Data Criaçao' => $dataCriacao,
            'Data atualização' => $dataAtualizacao,
        ];

        return response()->json(['Produto Deletado' => $produtoRenomeado, 'mensagem' => 'Produto cancelado com sucesso'], 200);
    }

}
