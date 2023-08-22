<?php
// app/Emails/PedidoConfirmadoEmail.php

namespace App\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PedidoConfirmadoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $detalhesPedido;

    public function __construct($detalhesPedido)
    {
        $this->detalhesPedido = $detalhesPedido;
    }

    public function build()
    {
        return $this->view('emails.pedido-confirmado')
                    ->with([
                        'detalhesPedido' => $this->detalhesPedido,
                    ]);
    }
}
