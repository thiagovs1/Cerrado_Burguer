CREATE DATABASE IF NOT EXISTS cerrado_burguer
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cerrado_burguer;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS itens_pedidos;
DROP TABLE IF EXISTS itens_encomenda;
DROP TABLE IF EXISTS pedido;
DROP TABLE IF EXISTS produto;
DROP TABLE IF EXISTS encomenda;
DROP TABLE IF EXISTS categoria;
DROP TABLE IF EXISTS usuario;

CREATE TABLE usuario (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    endereco VARCHAR(500) DEFAULT NULL,
    cpf VARCHAR(14) DEFAULT NULL,
    status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    UNIQUE KEY cpf (cpf)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categoria (
    id_categoria INT NOT NULL AUTO_INCREMENT,
    tipo VARCHAR(100) DEFAULT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500) DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    status ENUM('Ativa','Inativa') NOT NULL DEFAULT 'Ativa',
    cor VARCHAR(20) DEFAULT '#8B0000',

    PRIMARY KEY (id_categoria)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE encomenda (
    id_encomenda INT NOT NULL AUTO_INCREMENT,
    nome_fornecedor VARCHAR(255) NOT NULL,
    tipo_fornecedor ENUM('Bebidas','Ingredientes') NOT NULL,
    data_encomenda DATE NOT NULL,
    data_prevista DATE NOT NULL,
    observacao VARCHAR(500) DEFAULT NULL,
    status ENUM(
        'Pendente',
        'Em Andamento',
        'Recebida',
        'Cancelada'
    ) NOT NULL DEFAULT 'Pendente',
    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    PRIMARY KEY (id_encomenda)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE produto (
    id_produto INT NOT NULL AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500) DEFAULT NULL,
    preco DECIMAL(7,2) NOT NULL,
    preco_anterior DECIMAL(7,2) DEFAULT NULL,
    status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
    imagem VARCHAR(255) DEFAULT NULL,
    tempo_preparo INT NOT NULL DEFAULT 0,
    promocao VARCHAR(10) NOT NULL,
    disponivel_entrega VARCHAR(10) NOT NULL DEFAULT 'Sim',

    PRIMARY KEY (id_produto),
    KEY id_categoria (id_categoria),

    CONSTRAINT produto_ibfk_1
        FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido (
    id_pedido INT NOT NULL AUTO_INCREMENT,
    id_usuario INT DEFAULT NULL,

    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tipo_entrega ENUM('Retirada','Entrega') NOT NULL DEFAULT 'Retirada',
    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(100) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    setor VARCHAR(100) DEFAULT NULL,
    informacao_adicional VARCHAR(500) DEFAULT NULL,
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    troco VARCHAR(50) DEFAULT NULL,
    observacao VARCHAR(500) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pedido recebido',
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_pedido),
    KEY id_usuario (id_usuario),

    CONSTRAINT pedido_ibfk_1
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE itens_encomenda (
    id_item INT NOT NULL AUTO_INCREMENT,
    id_encomenda INT NOT NULL,
    nome_item VARCHAR(255) NOT NULL,
    tipo ENUM('Bebida','Ingrediente') NOT NULL,
    unidade VARCHAR(30) NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    valor_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (id_item),
    KEY id_encomenda (id_encomenda),

    CONSTRAINT itens_encomenda_ibfk_1
        FOREIGN KEY (id_encomenda)
        REFERENCES encomenda(id_encomenda)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE itens_pedidos (
    id_item_pedidos INT NOT NULL AUTO_INCREMENT,
    id_produto INT NOT NULL,
    id_pedido INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(7,2) NOT NULL,

    PRIMARY KEY (id_item_pedidos),
    KEY id_produto (id_produto),
    KEY id_pedido (id_pedido),

    CONSTRAINT itens_pedidos_ibfk_1
        FOREIGN KEY (id_produto)
        REFERENCES produto(id_produto),

    CONSTRAINT itens_pedidos_ibfk_2
        FOREIGN KEY (id_pedido)
        REFERENCES pedido(id_pedido)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuario
(id, email, senha, nome, telefone, endereco, cpf, status, data_cadastro)
VALUES
(
    1,
    'thiago.sousa13@estudante.ifto.edu.br',
    '$2y$12$xjwUbMNXYfDMQBLn9w.bUuDMJJDQrEiQJS12ynjJVsDaGuytO8AOq',
    'th',
    '6399876543311',
    NULL,
    '8901822',
    'Ativo',
    '2026-09-23 11:05:51'
),
(
    2,
    'admin123@hamburgueria.c',
    '$2y$12$lBADUmYTax2fHYQ8etRbXORA6d/fapFV7ESCzilEMVw6gOqHv8NmK',
    'th',
    '63998765433',
    NULL,
    '00000000',
    'Ativo',
    '2026-09-23 12:24:30'
);

INSERT INTO categoria
(id_categoria, tipo, nome, descricao, imagem, status, cor)
VALUES
(
    1,
    'Cardápio',
    'Acompanhamentos',
    'Acompanhamentos do Cerrado Burguer',
    '6ab3b8eb0db27.png',
    'Ativa',
    '#aba0a0'
),
(
    2,
    'Cardápio',
    'Bebidas',
    'Bebidas do Cerrado Burguer',
    '6ab3b8c233e2f.png',
    'Ativa',
    '#8b0000'
),
(
    3,
    'Cardápio',
    'Hamburgueres',
    'Hamburgueres do Cerrado Burguer',
    '6ab3b61729507.png',
    'Ativa',
    '#884927'
),
(
    4,
    'Cardápio',
    'Combos',
    'Combos do Cerrado Burguer',
    '6ab3b8f597a62.png',
    'Ativa',
    '#8b0000'
);

INSERT INTO produto
(
    id_produto,
    id_categoria,
    nome,
    descricao,
    preco,
    preco_anterior,
    status,
    imagem,
    tempo_preparo,
    promocao,
    disponivel_entrega
)
VALUES
(1,1,'Batata Frita - 500g','Batata frita - 500g',7.80,NULL,'Ativo','batatafrita.png',10,'Não','Sim'),
(2,1,'Porção de anéis de cebola – 500g','Porção de anéis de cebola - 500g',8.99,NULL,'Ativo','Anelcebola.png',10,'Não','Sim'),
(3,1,'Bolinho de mandioca com carne seca – 500g','Bolinho de mandioca com carne seca - 500g',12.90,NULL,'Ativo','bolinhoM.png',15,'Não','Sim'),
(4,1,'Molho barbecue – 150g','Molho barbecue - 150g',4.99,NULL,'Ativo','Barbecue.png',5,'Não','Sim'),
(5,1,'Maionese Temperada – 150g','Maionese temperada - 150g',2.99,NULL,'Ativo','temperada.png',5,'Não','Sim'),
(6,1,'Maionese de alho – 150g','Maionese de alho - 150g',2.50,NULL,'Ativo','MdeAlho.png',5,'Não','Sim'),
(7,1,'Geleia de pimenta – 150g','Geleia de pimenta - 150g',5.00,NULL,'Ativo','Gpimenta.png',5,'Não','Sim'),
(8,2,'Coca Cola – 1L','Coca Cola - 1 litro',8.99,NULL,'Ativo','coca.png',2,'Sim','Sim'),
(9,2,'Suco natural de caju – 500ml','Suco natural de caju - 500ml',5.99,NULL,'Ativo','Sucocaju.png',5,'Não','Sim'),
(10,2,'Suco natural de laranja – 500ml','Suco natural de laranja - 500ml',6.99,NULL,'Ativo','Slaranja.png',5,'Não','Sim'),
(11,2,'Pepsi – 500ml','Pepsi - 500ml',8.99,NULL,'Ativo','pepsi.png',2,'Não','Sim'),
(12,2,'Fanta – 1L','Fanta - 1 litro',10.99,NULL,'Ativo','Fanta.png',2,'Não','Sim'),
(13,2,'Suco de Uva – 500ml','Suco de uva - 500ml',5.99,NULL,'Ativo','sucoUva.png',5,'Não','Sim'),
(14,2,'Guaraná – 1L','Guaraná - 1L',12.99,NULL,'Ativo','guarana.png',2,'Não','Sim'),
(15,3,'Cheddar Burguer','Pão brioche, cheddar duplo e carne dupla',28.00,NULL,'Ativo','Amburguer_card - Copia.png',20,'Não','Sim'),
(16,3,'Tropical Burguer','Carne, queijo, abacaxi grelhado e molho agridoce',30.00,NULL,'Ativo','Burguer2.png',20,'Não','Sim'),
(17,3,'X-Burguer','Carne, alface, tomate, bacon e cheddar',38.00,NULL,'Ativo','burguer3.png',20,'Não','Sim'),
(18,3,'Smash Burguer Duplo','Dois smash, queijo e molho da casa',25.00,NULL,'Ativo','burguer4.png',20,'Não','Sim'),
(19,3,'Burguer Simples','Carne, queijo, alface e tomate',20.00,NULL,'Ativo','burger5.png',20,'Não','Sim'),
(20,3,'Supremo Burguer','Carne, picles, alface, tomate e maionese',30.00,NULL,'Ativo','burguer7.png',20,'Não','Sim'),
(21,4,'2 Acompanhamentos de sua preferência','Uma porção de anéis de cebola, bolinho de mandioca com carne seca ou batata frita 500g',17.90,NULL,'Ativo','Co1.png',15,'Não','Sim'),
(22,4,'3 Supremo Burguer','3 Supremo Burguer',87.90,NULL,'Ativo','amburgueres.png',25,'Não','Sim'),
(23,4,'Cheddar Burguer e Coca-Cola','Cheddar Burguer com Coca-Cola 1L',28.90,NULL,'Ativo','Amburguer_card.png',20,'Não','Sim'),
(24,4,'Bolinho de mandioca com carne seca e 2 molhos','Bolinho de mandioca com carne seca, molho barbecue, geleia de pimenta e maionese temperada',16.00,NULL,'Ativo','bolinhoM.png',15,'Não','Sim');

INSERT INTO pedido
(
    id_pedido,
    id_usuario,
    subtotal,
    valor_entrega,
    total,
    tipo_entrega,
    cidade,
    estado,
    endereco,
    setor,
    informacao_adicional,
    forma_pagamento,
    troco,
    observacao,
    status,
    data_pedido
)
VALUES
(
    1,1,
    0.00,0.00,0.00,
    'Retirada',
    NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
    'Carrinho',
    '2026-09-23 11:05:54'
),
(
    2,2,
    0.00,0.00,0.00,
    'Retirada',
    NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',
    'finalizado',
    '2026-09-23 12:24:44'
),
(
    3,2,
    0.00,0.00,0.00,
    'Retirada',
    NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',
    'cancelado',
    '2026-09-23 12:41:29'
);

INSERT INTO itens_pedidos
(
    id_item_pedidos,
    id_produto,
    id_pedido,
    quantidade,
    preco_unitario
)
VALUES
(1,19,1,1,20.00),
(2,20,2,1,30.00),
(3,5,3,1,2.99);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Banco cerrado_burguer criado com sucesso!' AS mensagem;

SHOW TABLES;