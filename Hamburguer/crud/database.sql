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
