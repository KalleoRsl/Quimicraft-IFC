-- Migração: foto de perfil do usuário
USE quimicraft;

ALTER TABLE usuarios
    ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL AFTER recorde;
