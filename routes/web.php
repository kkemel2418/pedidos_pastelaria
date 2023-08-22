<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProdutoController;

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('/teste', function () use ($router) {
    try {
        $result = DB::select('SELECT 1');
        return response()->json(['message' => 'Conexão com o banco de dados bem-sucedida!']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao conectar-se ao banco de dados: ' . $e->getMessage()]);
    }
});
