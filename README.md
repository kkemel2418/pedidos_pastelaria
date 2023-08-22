# pedidos_pastelaria
API PASTELARIA

# API_PASTELARIA

## Introdução

Este projeto é uma API de uma pastelaria, desenvolvida usando a framework Lumen, que oferece endpoints para gerenciar informações sobre clientes, produtos e pedidos.

## Considerações Iniciais

Durante o desenvolvimento da API, foram enfrentados desafios com relação ao envio de e-mails, resultando na escolha das seguintes opções:

- **Envio de E-mails:** Para o envio de e-mails, considerou-se a utilização de serviços como Mailtrap e SendGrid. Porém, devido a limitações e dificuldades de acesso, essas opções não foram viáveis.

## Configuração

Para configurar o projeto localmente, siga as instruções abaixo:

1. Crie um arquivo `.env` na raiz do projeto e defina as seguintes variáveis de ambiente:


APP_NAME=Lumen
APP_ENV=local
APP_KEY=9fJplNyANbGc3I7aUMgyxfE7bMe7SPzw
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_SLACK_WEBHOOK_URL=

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=api_pastelaria
DB_USERNAME=root
DB_PASSWORD=CF_password@123

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Example app"

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

    Certifique-se de que um servidor MySQL esteja configurado e acessível.

    Inicialize o Docker para criar um ambiente Dockerizado.


Funcionalidades

A API oferece as seguintes funcionalidades para as entidades Cliente, Produto e Pedido:
Cliente

    Lista: Retorna uma lista paginada de clientes.
    Mostrar: Retorna os detalhes de um cliente específico.
    Criar: Cria um novo cliente.
    SoftDelete: Remove um cliente de forma lógica (soft delete).
    Atualizar: Atualiza as informações de um cliente.

Produto

    Lista: Retorna uma lista paginada de produtos.
    Mostrar: Retorna os detalhes de um produto específico.
    Criar: Cria um novo produto.
    SoftDelete: Remove um produto de forma lógica (soft delete).
    Atualizar: Atualiza as informações de um produto.

Pedido

    Lista: Retorna uma lista paginada de pedidos.
    Mostrar: Retorna os detalhes de um pedido específico.
    Criar: Cria um novo pedido.
    SoftDelete: Remove um pedido de forma lógica (soft delete).
    Atualizar: Atualiza as informações de um pedido.

Observações

    As operações de LIST estão paginadas, com 10 itens por página.
    O SoftDelete é aplicado nas operações de DELETE.
    Ao inativar um registro (soft delete), o mesmo não estará mais disponível e retornará a mensagem "item não encontrado".
    Há tratamento de erros implementado para cada operação.
    O projeto foi dockerizado para facilitar o ambiente de desenvolvimento e testes.

Documentação

Para uma documentação mais detalhada, incluindo exemplos de requisições, consulte a coleção do Postman:

Documentação do POSTMAN
       https://kaliamin.postman.co/workspace/New-Team-Workspace~f00cb03d-a8e8-4e62-ba53-88701bdf945b/collection/14051827-37c880f7-f397-4cb9-a27e-1866f5374bd4
       https://documenter.getpostman.com/view/14051827/2s946mZUyR


