-- Base de datos para la plataforma Recuerda+
CREATE DATABASE IF NOT EXISTS recuerda_plus;
USE recuerda_plus;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME
);

-- Tabla de eventos (agenda)
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    descripcion TEXT,
    completado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de fotos (álbum)
CREATE TABLE IF NOT EXISTS fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de adivinanzas
CREATE TABLE IF NOT EXISTS adivinanzas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    respuesta_correcta VARCHAR(100) NOT NULL,
    opcion1 VARCHAR(100) NOT NULL,
    opcion2 VARCHAR(100) NOT NULL,
    opcion3 VARCHAR(100) NOT NULL
);

-- Tabla de puntuaciones de juegos
CREATE TABLE IF NOT EXISTS puntuaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    juego VARCHAR(50) NOT NULL,
    puntuacion INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar algunas adivinanzas de ejemplo
INSERT INTO adivinanzas (pregunta, respuesta_correcta, opcion1, opcion2, opcion3) VALUES
('¿Qué tiene llaves pero no abre puertas?', 'Piano', 'Cerradura', 'Llavero', 'Piano'),
('¿Qué tiene dientes pero no puede masticar?', 'Peine', 'Peine', 'Tenedor', 'Sierra'),
('¿Qué tiene agujas pero no puede coser?', 'Reloj', 'Cactus', 'Reloj', 'Erizo'),
('¿Qué tiene ojos pero no puede ver?', 'Patata', 'Patata', 'Aguja', 'Muñeco'),
('¿Qué tiene cuello pero no tiene cabeza?', 'Botella', 'Jirafa', 'Camisa', 'Botella'),
('¿Qué tiene hojas pero no es un árbol?', 'Libro', 'Libro', 'Lechuga', 'Cuaderno'),
('¿Qué tiene corona pero no es rey?', 'Piña', 'Reina', 'Piña', 'Príncipe'),
('¿Qué tiene patas pero no puede caminar?', 'Mesa', 'Perro', 'Mesa', 'Silla');
