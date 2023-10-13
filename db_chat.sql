drop database db_chat;
CREATE DATABASE IF NOT EXISTS db_chat;
USE db_chat;

CREATE TABLE IF NOT EXISTS usuarios (
	id_user INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(90) NOT NULL,
    email VARCHAR(90) NOT NULL,
    senha VARCHAR(90) NOT NULL)
 engine InnoDB;

CREATE TABLE conversas (
    id_conversa INT PRIMARY KEY AUTO_INCREMENT,
    data_registro TIMESTAMP
) ENGINE InnoDB;

 
CREATE TABLE participante_conversa (
	id_part_conversa int PRIMARY KEY AUTO_INCREMENT,
    id_conversa INT NOT NULL,
    id_user INT NOT NULL,
    CONSTRAINT fk_conversa_participante
		FOREIGN KEY (id_conversa)
			REFERENCES conversas(id_conversa),
	CONSTRAINT fk_user_participante_c
		FOREIGN KEY (id_user)
			REFERENCES usuarios(id_user))
engine InnoDB;

CREATE TABLE IF NOT EXISTS mensagens (
	id_msg INT AUTO_INCREMENT PRIMARY KEY,
    mensagem_text VARCHAR(200) NOT NULL,
    id_user INT NOT NULL,
    id_conversa INT NOT NULL,
    data_registro TIMESTAMP NOT NULL,
    CONSTRAINT fk_usuario_conversa
        FOREIGN KEY (id_user)
            REFERENCES usuarios (id_user),
	 CONSTRAINT fk_conversa_mensagem
		FOREIGN KEY (id_conversa)
			REFERENCES conversas(id_conversa))
engine InnoDB;


INSERT INTO usuarios(nome, email, senha) VALUES 
('Nicole', 'nicole@gmail.com', '1234'),
('Mateus', 'mateus@gmail.com', '1234'),
('rodrigo', 'rodrigo@gmail.com', '1234'),
('bone', 'bone@gmail.com', '1234'),
('bruno', 'bruno@gmail.com', '1234'),
('maira', 'maira@gmail.com', '1234');

INSERT INTO conversas(data_registro) VALUES 
(CURRENT_TIMESTAMP),
(CURRENT_TIMESTAMP),
(CURRENT_TIMESTAMP),
(CURRENT_TIMESTAMP),
(CURRENT_TIMESTAMP),
(CURRENT_TIMESTAMP),
(CURRENT_TIMESTAMP);

INSERT INTO participante_conversa(id_conversa, id_user) VALUES 
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5),
(3, 6),
(4, 1),
(4, 3),
(5, 2),
(5, 4),
(6, 5),
(6, 6),
(7, 1),
(7, 5);



INSERT INTO mensagens(mensagem_text, id_user, id_conversa, data_registro) VALUES 
('Olá Nicole', 2, 1, CURRENT_TIMESTAMP),
('Olá Mateus', 1, 1, CURRENT_TIMESTAMP),
('Tudo bem com você?', 1, 1, CURRENT_TIMESTAMP),
('Tudo sim, e com vc?', 2, 1, CURRENT_TIMESTAMP),
('Olá Bone', 3, 2, CURRENT_TIMESTAMP),
('Olá Rodrigo', 4, 2, CURRENT_TIMESTAMP),
('Tudo bem com você?', 4, 2, CURRENT_TIMESTAMP),
('Tudo sim, e com vc?', 3, 2, CURRENT_TIMESTAMP),
('Olá Maira', 5, 3, CURRENT_TIMESTAMP),
('Olá Bruno', 6, 3, CURRENT_TIMESTAMP),
('Tudo bem com você?', 6, 3, CURRENT_TIMESTAMP),
('Tudo sim, e com vc?', 5, 3, CURRENT_TIMESTAMP);

select u.nome, m.mensagem_text 
	from usuarios u 
		inner join mensagens m
			where u.id_user = m.id_user
            and u.id_user = 5;
            
SELECT count(.id_conversa) 
	FROM conversas as c
		INNER JOIN participante_conversa as pc ON c.id_conversa = pc.id_conversa
		INNER JOIN usuarios as u ON u.id_user = pc.id_user
        WHERE u.id_user = 3;

