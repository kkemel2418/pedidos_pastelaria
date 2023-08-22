<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 100);
            $table->decimal('preco', 10, 2);
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['ativo', 'inativo','cancelado'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();

            // Use collation and engine options
            $table->collation = 'utf8mb4_bin';
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
