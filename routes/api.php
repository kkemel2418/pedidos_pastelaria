<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Http\Request;

$router->group(['prefix' => '/api'], function() use($router){

    $router->get('/produto', 'ProdutoController@index');
    $router->get('/produto/{id}', 'ProdutoController@show');
    $router->post('/produto','ProdutoController@store');
    //$router->put('/produto/{id}','ProdutoController@update'); esta certo
    $router->post('/produto/update/{id}' , 'ProdutoController@update');

    $router->delete('/produto/{id}','ProdutoController@destroy');

    $router->get('/cliente', 'ClienteController@index');
    $router->get('/cliente/{id}', 'ClienteController@show');
    $router->post('/cliente','ClienteController@store');
    $router->put('/cliente/{id}','ClienteController@update');
    $router->delete('/cliente/{id}','ClienteController@destroy');

    $router->get('/pedido', 'PedidoController@index');
    $router->get('/pedido/{id}', 'PedidoController@show');
    $router->post('/pedido','PedidoController@store');
    $router->put('/pedido/{id}','PedidoController@update');
    $router->delete('/pedido/{id}','PedidoController@destroy');

    $router->get('/send_email' ,'Mailcontroller@mail');

 });


