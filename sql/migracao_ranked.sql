-- Migração para bancos já existentes (execute se o banco já foi criado antes)
USE quimicraft;

-- Garante índice único por usuário no ranking
ALTER TABLE ranking ADD UNIQUE KEY unique_usuario (id_usuario);

-- Insere modos de jogo caso ainda não existam
INSERT IGNORE INTO modos_jogo (id_modo, nome_modo) VALUES (1, 'Solo');
INSERT IGNORE INTO modos_jogo (id_modo, nome_modo) VALUES (2, 'Rankeada');
INSERT IGNORE INTO modos_jogo (id_modo, nome_modo) VALUES (3, 'Amigos');

-- Insere turmas padrão caso ainda não existam
INSERT IGNORE INTO turmas (id_turma, nome_turma) VALUES (1, 'Turma A');
INSERT IGNORE INTO turmas (id_turma, nome_turma) VALUES (2, 'Turma B');
