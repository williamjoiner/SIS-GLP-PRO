-- Adicionar novas colunas na tabela clients
ALTER TABLE clients
ADD COLUMN zipcode VARCHAR(10) AFTER phone,
ADD COLUMN street VARCHAR(100) AFTER zipcode,
ADD COLUMN number VARCHAR(20) AFTER street,
ADD COLUMN complement VARCHAR(100) AFTER number,
ADD COLUMN neighborhood VARCHAR(100) AFTER complement,
ADD COLUMN city VARCHAR(100) AFTER neighborhood,
ADD COLUMN state VARCHAR(2) AFTER city,
ADD COLUMN notes TEXT AFTER state;
