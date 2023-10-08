-- drop database db_chat;
CREATE DATABASE IF NOT EXISTS db_chat;
USE db_chat;

CREATE TABLE IF NOT EXISTS usuarios (
	id_user INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(90) NOT NULL,
    email VARCHAR(90) NOT NULL,
    senha VARCHAR(90) NOT NULL)
 engine InnoDB;

CREATE TABLE IF NOT EXISTS mensagens (
	id_msg INT AUTO_INCREMENT PRIMARY KEY,
    mensagem_text VARCHAR(90),
    id_user INT,
    data_hora TIMESTAMP,
    CONSTRAINT fk_usuario_conversa
        FOREIGN KEY (id_user)
            REFERENCES usuarios (id_user)) 
engine InnoDB;

INSERT INTO usuarios VALUES 
(1, 'Nicole', 'nicole@gmail.com', '1234'),
(2, 'Mateus', 'mateus@gmail.com', '1234');

INSERT INTO mensagens(mensagem_text, id_user) VALUES 
('Olá Nicole', '2'),
('Olá Mateus', '1'),
('Tudo bem com você?', '1'),
('Tudo sim, e com vc?', '2');

