# CerradoBurguer

## Sobre o nosso projeto

O **CerradoBurguer** é um sistema desenvolvido para uma hamburgueria, com o objetivo de facilitar o atendimento aos clientes e a organização do estabelecimento.

O projeto começou a partir de um protótipo desenvolvido no **Canva** e, foi transformado em um sistema funcional utilizando **HTML, CSS, PHP e MySQL**.

A ideia é permitir que o cliente faça seu pedido de forma simples, enquanto o administrador consegue acompanhar e gerenciar as principais informações da hamburgueria.

## Funcionalidades

### Área do cliente

* Página inicial;
* Cardápio organizado por categorias;
* Hambúrgueres, bebidas, acompanhamentos e combos;
* Cadastro e login;
* Perfil do cliente;
* Carrinho de compras;
* Alteração e remoção de produtos do carrinho;
* Escolha entre retirada e entrega;
* Cadastro de endereço;
* Escolha da forma de pagamento;
* Opção de troco;
* Confirmação do pedido;
* Promoções;
* Avaliações e contato.

### Área administrativa

O painel administrativo foi desenvolvido com base no protótipo do Canva e permite organizar diferentes partes do sistema, como:

* Produtos;
* Categorias;
* Promoções;
* Clientes;
* Pedidos;
* Entregas;
* Avaliações;
* Encomendas;
* Relatórios;
* Configurações.

Também é possível cadastrar, editar e excluir produtos, definir preços, tempo de preparo, disponibilidade para entrega e outras informações necessárias para o funcionamento da hamburgueria.

## Tecnologias utilizadas

* **HTML5** — estrutura das páginas;
* **CSS3** — estilização e identidade visual;
* **PHP** — funcionalidades e processamento do sistema;
* **MySQL** — armazenamento dos dados;
* **PDO** — conexão entre PHP e MySQL;
* **JavaScript** — interações da interface;
* **Font Awesome** — ícones do painel administrativo;
* **Docker** — execução do PHP e MySQL em containers;
* **Git e GitHub** — controle de versões;
* **Canva** — criação do protótipo.

## Banco de dados

O sistema utiliza o banco de dados:

text
cerrado_burguer

Entre as principais tabelas estão:

* `usuario` — dados dos clientes;
* `categoria` — categorias dos produtos;
* `produto` — produtos do cardápio;
* `pedido` — pedidos realizados;
* `itens_pedidos` — produtos de cada pedido;
* `encomenda` — encomendas;
* `itens_encomenda` — itens das encomendas.

## Protótipo

Antes da implementação, as principais telas foram planejadas no **Canva**. O protótipo serviu como nossa referência para a criação das páginas e para definir a organização visual do sistema.

Entre as telas utilizadas como referência estão:

* Página inicial;
* Login;
* Painel administrativo;
* Produtos;
* Categorias;
* Promoções;
* Clientes;
* Pedidos;
* Entregas;
* Avaliações;
* Encomendas;
* Relatórios.

Durante a programação, algumas telas foram adaptadas para funcionar com o banco de dados e as funcionalidades do sistema.

## Docker

O projeto utiliza Docker para executar o PHP com Apache e o MySQL sem precisar configurar esses serviços manualmente no computador.

### Instalação

É necessário instalar o **Docker Desktop**.

Depois, abra o terminal na pasta:

powershell
cd C:\xampp\htdocs\Cerrado_Burguer\Hamburguer


Execute:

powershell
docker compose up -d --build


Depois disso, o sistema pode ser acessado em:

text
http://localhost:8080


Para parar os containers:

powershell
docker compose down


Para iniciar novamente:

powershell
docker compose up -d


Os dados do MySQL são armazenados em um volume do Docker para evitar que sejam perdidos ao parar os containers.

## Git

O Git é utilizado para acompanhar as alterações feitas no projeto.

Depois de instalar o Git, é possível verificar a instalação com:

powershell
git --version


Na pasta do projeto, os principais comandos utilizados são:

powershell
git init
git add .
git commit -m "Descrição da alteração"


Para enviar as alterações para o GitHub:

powershell
git push


## Como executar

Com o Docker instalado:

powershell
cd C:\xampp\htdocs\Cerrado_Burguer\Hamburguer
docker compose up -d --build

Depois, abra:

http://localhost:8080

O banco de dados é criado a partir do arquivo:

text
crud/database.sql


## Equipe

* **Allana Carneiro Santos**
* **Thiago Vieira de Sousa**
* **Esther Alves dos Santos**
* **Felipe Flausino de Souza Pereira**

