CREATE TABLE servicio (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(10,2),
    imagen_portada VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE articulo (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio)
        ON DELETE CASCADE
);

CREATE TABLE album (
    id_album INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE foto (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_album INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    descripcion TEXT,
    orden INT DEFAULT 0,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_album) REFERENCES album(id_album)
        ON DELETE CASCADE
);

CREATE TABLE contacto (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(150),
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    respondido BOOLEAN DEFAULT FALSE,
    fecha_recepcion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE presupuesto (
    id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    estado ENUM('pendiente','aceptado','rechazado') DEFAULT 'pendiente',
    total DECIMAL(10,2) DEFAULT 0,
    fecha_recepcion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE presupuesto_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_presupuesto INT NOT NULL,
    id_articulo INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_presupuesto) REFERENCES presupuesto(id_presupuesto)
        ON DELETE CASCADE,
    FOREIGN KEY (id_articulo) REFERENCES articulo(id_articulo)
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin','editor') DEFAULT 'editor',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);