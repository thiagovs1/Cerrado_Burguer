CREATE DATABASE IF NOT EXISTS cerrado_burguer;
USE cerrado_burguer;

-- =====================================================
-- USUÁRIOS / CLIENTES
-- =====================================================

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(255),
    telefone VARCHAR(20),
    endereco VARCHAR(500),
    cpf VARCHAR(14) UNIQUE,
    status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- CATEGORIAS
-- =====================================================

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100),
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500),
    imagem VARCHAR(255) DEFAULT NULL,
    status ENUM('Ativa','Inativa') NOT NULL DEFAULT 'Ativa',
    cor VARCHAR(20) DEFAULT '#8B0000'
);

-- =====================================================
-- PRODUTOS
-- =====================================================

CREATE TABLE IF NOT EXISTS produto (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500),
    preco DECIMAL(7,2) NOT NULL,
    status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
    imagem VARCHAR(255),
    tempo_preparo INT NOT NULL DEFAULT 0,
    promocao ENUM('Sim','Não') NOT NULL DEFAULT 'Não',
    disponivel_entrega ENUM('Sim','Não') NOT NULL DEFAULT 'Sim',

    FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
);

-- =====================================================
-- PEDIDOS
-- =====================================================

CREATE TABLE IF NOT EXISTS pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    tipo_entrega ENUM('Retirada','Entrega')
        NOT NULL DEFAULT 'Retirada',

    cidade VARCHAR(100),
    estado VARCHAR(100),
    endereco VARCHAR(255),
    setor VARCHAR(100),
    informacao_adicional VARCHAR(500),
    forma_pagamento VARCHAR(50),
    troco VARCHAR(50),
    observacao VARCHAR(500),

    status VARCHAR(50)
        NOT NULL DEFAULT 'Pedido recebido',

    data_pedido DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id)
);

-- =====================================================
-- ITENS DOS PEDIDOS
-- =====================================================

CREATE TABLE IF NOT EXISTS itens_pedidos (
    id_item_pedidos INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    id_pedido INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(7,2) NOT NULL,

    FOREIGN KEY (id_produto)
        REFERENCES produto(id_produto),

    FOREIGN KEY (id_pedido)
        REFERENCES pedido(id_pedido)
);

-- =====================================================
-- CATEGORIAS PADRÃO
-- =====================================================

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Acompanhamentos',
       'Acompanhamentos do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria
    WHERE nome = 'Acompanhamentos'
);

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Bebidas',
       'Bebidas do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria
    WHERE nome = 'Bebidas'
);

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Hambúrgueres',
       'Hambúrgueres do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria
    WHERE nome = 'Hambúrgueres'
);

INSERT INTO categoria (tipo, nome, descricao)
SELECT 'Cardápio', 'Combos',
       'Combos do Cerrado Burguer'
WHERE NOT EXISTS (
    SELECT 1 FROM categoria
    WHERE nome = 'Combos'
);

-- =====================================================
-- PRODUTOS - ACOMPANHAMENTOS
-- =====================================================

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Batata Frita - 500g',
       'Batata frita - 500g',
       7.80,
       'batatafrita.png',
       10
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Batata Frita - 500g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Porção de anéis de cebola – 500g',
       'Porção de anéis de cebola - 500g',
       8.99,
       'Anelcebola.png',
       10
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Porção de anéis de cebola – 500g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Bolinho de mandioca com carne seca – 500g',
       'Bolinho de mandioca com carne seca - 500g',
       12.90,
       'bolinhoM.png',
       15
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Bolinho de mandioca com carne seca – 500g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Molho barbecue – 150g',
       'Molho barbecue - 150g',
       4.99,
       'Barbecue.png',
       5
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Molho barbecue – 150g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Maionese Temperada – 150g',
       'Maionese temperada - 150g',
       2.99,
       'temperada.png',
       5
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Maionese Temperada – 150g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Maionese de alho – 150g',
       'Maionese de alho - 150g',
       2.50,
       'MdeAlho.png',
       5
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Maionese de alho – 150g'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Geleia de pimenta – 150g',
       'Geleia de pimenta - 150g',
       5.00,
       'Gpimenta.png',
       5
FROM categoria
WHERE nome = 'Acompanhamentos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Geleia de pimenta – 150g'
);

-- =====================================================
-- PRODUTOS - BEBIDAS
-- =====================================================

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Coca Cola – 1L',
       'Coca Cola - 1 litro',
       8.99,
       'coca.png',
       2
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Coca Cola – 1L'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Suco natural de caju – 500ml',
       'Suco natural de caju - 500ml',
       5.99,
       'Sucocaju.png',
       5
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Suco natural de caju – 500ml'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Suco natural de laranja – 500ml',
       'Suco natural de laranja - 500ml',
       6.99,
       'Slaranja.png',
       5
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Suco natural de laranja – 500ml'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Pepsi – 500ml',
       'Pepsi - 500ml',
       8.99,
       'pepsi.png',
       2
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Pepsi – 500ml'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Fanta – 1L',
       'Fanta - 1 litro',
       10.99,
       'Fanta.png',
       2
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Fanta – 1L'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Suco de Uva – 500ml',
       'Suco de uva - 500ml',
       5.99,
       'sucoUva.png',
       5
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Suco de Uva – 500ml'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Guaraná – 1L',
       'Guaraná - 1 litro',
       12.99,
       'guarana.png',
       2
FROM categoria
WHERE nome = 'Bebidas'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Guaraná – 1L'
);

-- =====================================================
-- PRODUTOS - HAMBÚRGUERES
-- =====================================================

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Cheddar Burguer',
       'Pão brioche, cheddar duplo e carne dupla',
       28.00,
       'Amburguer_card - Copia.png',
       20
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Cheddar Burguer'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Tropical Burguer',
       'Carne, queijo, abacaxi grelhado e molho agridoce',
       30.00,
       'Burguer2.png',
       20
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Tropical Burguer'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'X-Burguer',
       'Carne, alface, tomate, bacon e cheddar',
       38.00,
       'burguer3.png',
       20
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'X-Burguer'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Smash Burguer Duplo',
       'Dois smash, queijo e molho da casa',
       25.00,
       'burguer4.png',
       20
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Smash Burguer Duplo'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Burguer Simples',
       'Carne, queijo, alface e tomate',
       20.00,
       'burger5.png',
       20
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Burguer Simples'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Supremo Burguer',
       'Carne, picles, alface, tomate e maionese',
       30.00,
       'burguer7.png',
       20
FROM categoria
WHERE nome = 'Hambúrgueres'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Supremo Burguer'
);

-- =====================================================
-- COMBOS
-- =====================================================

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       '2 Acompanhamentos de sua preferência',
       'Uma porção de anéis de cebola, bolinho de mandioca com carne seca ou batata frita 500g',
       17.90,
       'Co1.png',
       15
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = '2 Acompanhamentos de sua preferência'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       '3 Supremo Burguer',
       '3 Supremo Burguer',
       87.90,
       'amburgueres.png',
       25
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = '3 Supremo Burguer'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Cheddar Burguer e Coca-Cola',
       'Cheddar Burguer com Coca-Cola 1L',
       28.90,
       'Amburguer_card.png',
       20
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Cheddar Burguer e Coca-Cola'
);

INSERT INTO produto
(id_categoria, nome, descricao, preco, imagem, tempo_preparo)
SELECT id_categoria,
       'Bolinho de mandioca com carne seca e 2 molhos',
       'Bolinho de mandioca com carne seca, molho barbecue, geleia de pimenta e maionese temperada',
       16.00,
       'bolinhoM.png',
       15
FROM categoria
WHERE nome = 'Combos'
AND NOT EXISTS (
    SELECT 1 FROM produto
    WHERE nome = 'Bolinho de mandioca com carne seca e 2 molhos'
);

-- =====================================================
-- ENCOMENDAS
-- =====================================================

CREATE TABLE IF NOT EXISTS encomenda (
    id_encomenda INT AUTO_INCREMENT PRIMARY KEY,
    nome_fornecedor VARCHAR(255) NOT NULL,
    tipo_fornecedor ENUM('Bebidas','Ingredientes') NOT NULL,
    data_encomenda DATE NOT NULL,
    data_prevista DATE NOT NULL,
    observacao VARCHAR(500),

    status ENUM(
        'Pendente',
        'Em Andamento',
        'Recebida',
        'Cancelada'
    ) NOT NULL DEFAULT 'Pendente',

    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00
);

-- =====================================================
-- ITENS DAS ENCOMENDAS
-- =====================================================

CREATE TABLE IF NOT EXISTS itens_encomenda (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_encomenda INT NOT NULL,
    nome_item VARCHAR(255) NOT NULL,
    tipo ENUM('Bebida','Ingrediente') NOT NULL,
    unidade VARCHAR(30) NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    valor_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_encomenda)
        REFERENCES encomenda(id_encomenda)
        ON DELETE CASCADE
);


