<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\now;
use Carbon\Carbon;

class ClienteController extends Controller
{
    private $cliente;

       public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    public function index()
    {
        $perPage = 10;
        $page = request()->query('page', 1);

        $clientes = Cliente::paginate($perPage, ['*'], 'page', $page);

        $clientesFormatados = [];
        foreach ($clientes as $cliente) {
            $clienteFormatado = [
                'id_cliente' => $cliente->id,
                'Nome Cliente' => $cliente->nome,
                'Email' => $cliente->email,
                'Telefone' => $cliente->telefone,
                'Data Nascimento' => Carbon::parse($cliente->data_nascimento)->format('d/m/Y'),
                'Endereco' => $cliente->endereco,
                'Complemento' => $cliente->complemento,
                'Bairro' => $cliente->bairro,
                'CEP' => $cliente->cep,
                'Data Cadastro' => Carbon::parse($cliente->created_at)->format('d/m/Y H:i:s'),
                'Status' => $cliente->status,
            ];

            $clientesFormatados[] = $clienteFormatado;
        }

        $response = [
            'Lista de clientes' => $clientesFormatados,
            'paginação' => [
                'total' => $clientes->total(),
                'Itens por pagina' => $clientes->perPage(),
                'página atual' => $clientes->currentPage(),
                'última página' => $clientes->lastPage(),
                'URL próxima página' => $clientes->nextPageUrl(),
                'URL página anterior' => $clientes->previousPageUrl(),
                'de' => $clientes->firstItem(),
                'para' => $clientes->lastItem(),
            ],
        ];

        return response()->json($response, 200);
    }

    public function show($id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json(['mensagem' => 'Cliente não encontrado'], 404);
            }

            $clienteFormatado = [
                'Detalhes do Cliente' => [
                    'id_cliente' => $cliente->id,
                    'Nome Cliente' => $cliente->nome,
                    'Email' => $cliente->email,
                    'Telefone' => $cliente->telefone,
                    'Data Nascimento' => Carbon::parse($cliente->data_nascimento)->format('d/m/Y'),
                    'Endereco' => $cliente->endereco,
                    'Complemento' => $cliente->complemento,
                    'Bairro' => $cliente->bairro,
                    'CEP' => $cliente->cep,
                    'Data Cadastro' => Carbon::parse($cliente->created_at)->format('d/m/Y H:i:s'),
                    'Status' => $cliente->status,
                ]
            ];

            return response()->json($clienteFormatado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Ocorreu um erro ao buscar o cliente'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'bail|required',
            'email' => 'bail|required|email|unique:clientes,email',
            'telefone' => 'bail|required',
            'data_nascimento' => 'bail|required|date',
            'endereco' => 'bail|required',
            'complemento' => 'bail|nullable',
            'bairro' => 'bail|required',
            'cep' => 'bail|required',
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O :attribute deve ser um endereço de e-mail válido.',
            'unique' => 'O :attribute já está em uso!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente = $this->cliente->create($request->all());

        $clienteRenomeado = [
            'id_cliente' => $cliente->id,
            'Nome Cliente' => $cliente->nome,
            'Email' => $cliente->email,
            'Telefone' => $cliente->telefone,
            'Data Nascimento' => $cliente->data_nascimento,
            'Endereco' => $cliente->endereco,
            'Complemento' => $cliente->complemento,
            'Bairro' => $cliente->bairro,
            'CEP' => $cliente->cep
        ];

        $mensagem = 'Cliente ' . $cliente->nome . ' incluído com sucesso!';
        return response()->json(['Cliente Cadastrado' => $clienteRenomeado, 'mensagem' => $mensagem], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'bail|required',
            'email' => 'bail|required|email',
            'telefone' => 'bail|required',
            'data_nascimento' => 'bail|required|date',
            'endereco' => 'bail|required',
            'complemento' => 'bail|nullable',
            'bairro' => 'bail|required',
            'cep' => 'bail|required'
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O :attribute deve ser um endereço de e-mail válido.'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $cliente = $this->cliente->find($id);

        $clienteExistente = $this->findEmail($request->input('email'), $id);

        if ($clienteExistente) {
            return response()->json(['message' => 'Email já está em uso por outro cliente'], 422);
        }

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        $cliente->update($request->all());

        $clienteRenomeado = [
            'id_cliente' => $cliente->id,
            'Nome Cliente' => $cliente->nome,
            'Email' => $cliente->email,
            'Telefone' => $cliente->telefone,
            'Data Nascimento' => $cliente->data_nascimento,
            'Endereco' => $cliente->endereco,
            'Complemento' => $cliente->complemento,
            'Bairro' => $cliente->bairro,
            'CEP' => $cliente->cep,
            'Data Cadastro' => $cliente->data_cadastro,
            'Status' => $cliente->status,
            'Inativado em' => $cliente->deleted_at,
            'Data Atualizacao' => $cliente->updated_at,
        ];

        $mensagem = 'Cliente ' . $cliente->nome . ' atualizado com sucesso!';

       return response()->json(['Cliente Atualizado' => $clienteRenomeado, 'mensagem' => $mensagem], 201);
   }

    public function findEmail($email, $id)
    {
        try {
            $clienteExistente = Cliente::where('email', $email)
                ->where('id', '!=', $id)
                ->first();

            return $clienteExistente;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        $nomeCliente = $cliente->nome;

        $cliente->delete();
        $cliente->status = 'inativo';
        $cliente->save();

        return response()->json(['Cliente Inativado' => ['nome' => $nomeCliente], 'mensagem' => 'Cliente inativado com sucesso'], 200);
    }

}

