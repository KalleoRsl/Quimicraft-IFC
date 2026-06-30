-- Migração para modo Amigos (execute se o banco já foi criado antes)
USE quimicraft;

CREATE TABLE IF NOT EXISTS desafios_amigos (
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
