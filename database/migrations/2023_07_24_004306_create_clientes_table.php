<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('email', 100)->unique();
            $table->string('telefone', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('endereco', 100)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 50)->nullable();
            $table->string('cep', 10)->nullable();
            $table->timestamp('data_cadastro')->useCurrent();
            $table->enum('status', ['ativo', 'inativo', 'cancelado'])->nullable()->default('ativo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
