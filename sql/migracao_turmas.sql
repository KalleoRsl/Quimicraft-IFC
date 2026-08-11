-- Migração: turmas INFO e Ensino Médio
USE quimicraft;

INSERT INTO turmas (id_turma, nome_turma) VALUES
(1, '1 INFO A'),
(2, '1 INFO B'),
(3, '2 INFO A'),
(4, '2 INFO B'),
(5, '3 INFO A'),
(6, '3 INFO B'),
(7, '1 A'),
(8, '1 B'),
(9, '1 C'),
(10, '1 E'),
(11, '2 A'),
(12, '2 B'),
(13, '2 C'),
(14, '2 E'),
(15, '3 A'),
(16, '3 B'),
(17, '3 C'),
(18, '3 E')
ON DUPLICATE KEY UPDATE nome_turma = VALUES(nome_turma);
