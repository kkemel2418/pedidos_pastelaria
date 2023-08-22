<!DOCTYPE html>
<html>
<head>
    <title>Pedido Confirmado</title>
</head>
<body>
    <h1>Seu pedido foi confirmado</h1>

    <p>Olá {{ $detalhesPedido['cliente']['nome'] }},</p>
    <p>Seu pedido de número {{ $detalhesPedido['id_pedido'] }} foi confirmado com sucesso!</p>

    <!-- Aqui, você pode adicionar mais informações sobre o pedido, como lista de produtos, total, etc. -->
</body>
</html>
