CREATE DATABASE IF NOT EXISTS cerrado_burguer;

USE cerrado_burguer;

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(255),
    telefone VARCHAR(20),
    endereco VARCHAR(500),
    cpf VARCHAR(14) UNIQUE
);

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100),
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500),
    imagem VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS produto (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500),
    preco DECIMAL(7,2) NOT NULL,
    status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
    imagem VARCHAR(255),
    tempo_preparo INT NOT NULL DEFAULT 0,
    promocao ENUM('Sim', 'Não') NOT NULL DEFAULT 'Não',
    disponivel_entrega ENUM('Sim', 'Não') NOT NULL DEFAULT 'Sim',

    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
);

CREATE TABLE IF NOT EXISTS pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL,

    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    tipo_entrega ENUM('Retirada', 'Entrega')
        NOT NULL DEFAULT 'Retirada',

    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(100) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    setor VARCHAR(100) DEFAULT NULL,
    informacao_adicional VARCHAR(500) DEFAULT NULL,

    forma_pagamento VARCHAR(50) DEFAULT NULL,
    troco VARCHAR(50) DEFAULT NULL,

    observacao VARCHAR(500) DEFAULT NULL,

    status VARCHAR(50)
        NOT NULL DEFAULT 'Pedido recebido',

    data_pedido DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pedido_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS itens_pedidos (
    id_item_pedidos INT AUTO_INCREMENT PRIMARY KEY,

    id_produto INT NOT NULL,
    id_pedido INT NOT NULL,

    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(7,2) NOT NULL,

    CONSTRAINT fk_itens_produto
        FOREIGN KEY (id_produto)
        REFERENCES produto(id_produto),

    CONSTRAINT fk_itens_pedido
        FOREIGN KEY (id_pedido)
        REFERENCES pedido(id_pedido)
);

USE cerrado_burguer;

-- =========================================
-- CATEGORIAS
-- =========================================

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Acompanhamentos', 'Acompanhamentos do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria WHERE nome = 'Acompanhamentos'
);

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Bebidas', 'Bebidas do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria WHERE nome = 'Bebidas'
);

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Hambúrgueres', 'Hambúrgueres do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria WHERE nome = 'Hambúrgueres'
);

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Combos', 'Combos do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria WHERE nome = 'Combos'
);


-- =========================================
-- ACOMPANHAMENTOS
-- =========================================

INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Batata Frita - 500g',
    'Batata frita - 500g',
    7.80,
    'Ativo',
    'batatafrita.png',
    10,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Batata Frita - 500g'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Porção de anéis de cebola – 500g',
    'Porção de anéis de cebola - 500g',
    8.99,
    'Ativo',
    'Anelcebola.png',
    10,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Porção de anéis de cebola – 500g'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Bolinho de mandioca com carne seca – 500g',
    'Bolinho de mandioca com carne seca - 500g',
    12.90,
    'Ativo',
    'bolinhoM.png',
    15,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Bolinho de mandioca com carne seca – 500g'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Molho barbecue – 150g',
    'Molho barbecue - 150g',
    4.99,
    'Ativo',
    'Barbecue.png',
    5,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Molho barbecue – 150g'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Maionese Temperada – 150g',
    'Maionese temperada - 150g',
    2.99,
    'Ativo',
    'temperada.png',
    5,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Maionese Temperada – 150g'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Maionese de alho – 150g',
    'Maionese de alho - 150g',
    2.50,
    'Ativo',
    'MdeAlho.png',
    5,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Maionese de alho – 150g'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT
    id_categoria,
    'Geleia de pimenta – 150g',
    'Geleia de pimenta - 150g',
    5.00,
    'Ativo',
    'Gpimenta.png',
    5,
    'Não',
    'Sim'
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto WHERE nome = 'Geleia de pimenta – 150g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Coca Cola – 1L', 'Coca Cola - 1 litro', 8.99, 'Ativo', 'coca.png', 2, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Coca Cola – 1L');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Suco natural de caju – 500ml', 'Suco natural de caju - 500ml', 5.99, 'Ativo', 'Sucocaju.png', 5, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Suco natural de caju – 500ml');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Suco natural de laranja – 500ml', 'Suco natural de laranja - 500ml', 6.99, 'Ativo', 'Slaranja.png', 5, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Suco natural de laranja – 500ml');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Pepsi – 500ml', 'Pepsi - 500ml', 8.99, 'Ativo', 'pepsi.png', 2, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Pepsi – 500ml');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Fanta – 1L', 'Fanta - 1 litro', 10.99, 'Ativo', 'Fanta.png', 2, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Fanta – 1L');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Suco de Uva – 500ml', 'Suco de uva - 500ml', 5.99, 'Ativo', 'sucoUva.png', 5, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Suco de Uva – 500ml');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria, 'Guaraná – 1L', 'Guaraná - 1 litro', 12.99, 'Ativo', 'guarana.png', 2, 'Não', 'Sim'
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Guaraná – 1L');

INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Cheddar Burguer',
'Pão brioche, cheddar duplo e carne dupla',
28.00,
'Ativo',
'Amburguer_card - Copia.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Cheddar Burguer');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Tropical Burguer',
'Carne, queijo, abacaxi grelhado e molho agridoce',
30.00,
'Ativo',
'Burguer2.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Tropical Burguer');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'X-Burguer',
'Carne, alface, tomate, bacon e cheddar',
38.00,
'Ativo',
'burguer3.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'X-Burguer');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Smash Burguer Duplo',
'Dois smash, queijo e molho da casa',
25.00,
'Ativo',
'burguer4.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Smash Burguer Duplo');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Burguer Simples',
'Carne, queijo, alface e tomate',
20.00,
'Ativo',
'burger5.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Burguer Simples');


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Supremo Burguer',
'Carne, picles, alface, tomate e maionese',
30.00,
'Ativo',
'burguer7.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (SELECT 1 FROM produto WHERE nome = 'Supremo Burguer');



INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'2 Acompanhamentos de sua preferência',
'Uma porção de anéis de cebola, bolinho de mandioca com carne seca ou batata frita 500g',
17.90,
'Ativo',
'Co1.png',
15,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = '2 Acompanhamentos de sua preferência'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'3 Supremo Burguer',
'3 Supremo Burguer',
87.90,
'Ativo',
'amburgueres.png',
25,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = '3 Supremo Burguer'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Cheddar Burguer e Coca-Cola',
'Cheddar Burguer com Coca-Cola 1L',
28.90,
'Ativo',
'Amburguer_card.png',
20,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Cheddar Burguer e Coca-Cola'
);


INSERT INTO produto
(id_categoria, nome, descricao, preco, status, imagem, tempo_preparo, promocao, disponivel_entrega)
SELECT id_categoria,
'Bolinho de mandioca com carne seca e 2 molhos',
'Bolinho de mandioca com carne seca, molho barbecue, geleia de pimenta e maionese temperada',
16.00,
'Ativo',
'bolinhoM.png',
15,
'Não',
'Sim'
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Bolinho de mandioca com carne seca e 2 molhos'
);

ALTER TABLE categoria
ADD COLUMN status ENUM('Ativa', 'Inativa') NOT NULL DEFAULT 'Ativa';