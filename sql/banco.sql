CREATE DATABASE quimicraft;
USE quimicraft;

CREATE TABLE turmas (
    id_turma INT AUTO_INCREMENT PRIMARY KEY,
    nome_turma VARCHAR(50) NOT NULL
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    id_turma INT,
    recorde INT DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_turma) REFERENCES turmas(id_turma)
);

CREATE TABLE amizades (
    id_amizade INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario1 INT NOT NULL,
    id_usuario2 INT NOT NULL,
    data_amizade DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario1) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_usuario2) REFERENCES usuarios(id_usuario)
);

CREATE TABLE solicitacoes_amizade (
    id_solicitacao INT AUTO_INCREMENT PRIMARY KEY,
    id_remetente INT NOT NULL,
    id_destinatario INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pendente',
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_remetente) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_destinatario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE modos_jogo (
    id_modo INT AUTO_INCREMENT PRIMARY KEY,
    nome_modo VARCHAR(30) NOT NULL
);

CREATE TABLE partidas (
    id_partida INT AUTO_INCREMENT PRIMARY KEY,
    id_modo INT NOT NULL,
    data_partida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_modo) REFERENCES modos_jogo(id_modo)
);

CREATE TABLE jogadores_partida (
    id_jogador_partida INT AUTO_INCREMENT PRIMARY KEY,
    id_partida INT NOT NULL,
    id_usuario INT NOT NULL,
    pontuacao INT DEFAULT 0,
    FOREIGN KEY (id_partida) REFERENCES partidas(id_partida),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE ranking (
    id_ranking INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    pontuacao INT NOT NULL DEFAULT 0,
    posicao INT DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL
);

CREATE TABLE perguntas (
    id_pergunta INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

CREATE TABLE respostas (
    id_resposta INT AUTO_INCREMENT PRIMARY KEY,
    id_pergunta INT NOT NULL,
    resposta TEXT NOT NULL,
    correta BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_pergunta) REFERENCES perguntas(id_pergunta)
);

CREATE TABLE respostas_usuario (
    id_resposta_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_partida INT NOT NULL,
    id_usuario INT NOT NULL,
    id_pergunta INT NOT NULL,
    id_resposta INT NOT NULL,
    correta BOOLEAN,
    data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_partida) REFERENCES partidas(id_partida),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_pergunta) REFERENCES perguntas(id_pergunta),
    FOREIGN KEY (id_resposta) REFERENCES respostas(id_resposta)
);

CREATE TABLE desafios_amigos (
    id_desafio INT AUTO_INCREMENT PRIMARY KEY,
    id_desafiante INT NOT NULL,
    id_desafiado INT NOT NULL,
    id_partida INT,
    pontuacao_desafiante INT DEFAULT NULL,
    pontuacao_desafiado INT DEFAULT NULL,
    status VARCHAR(30) DEFAULT 'pendente',
    id_vencedor INT DEFAULT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_desafiante) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_desafiado) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_partida) REFERENCES partidas(id_partida),
    FOREIGN KEY (id_vencedor) REFERENCES usuarios(id_usuario)
);

-- Dados iniciais
INSERT INTO turmas (nome_turma) VALUES ('Turma A'), ('Turma B');

INSERT INTO modos_jogo (nome_modo) VALUES ('Solo'), ('Rankeada'), ('Amigos');