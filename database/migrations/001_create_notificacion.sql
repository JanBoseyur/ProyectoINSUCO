CREATE TABLE IF NOT EXISTS notificacion (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_ticket INT NULL,
    tipo VARCHAR(80) NOT NULL,
    destinatario VARCHAR(190) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    estado ENUM('pendiente', 'enviado', 'fallido') NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_envio TIMESTAMP NULL DEFAULT NULL,
    error_envio VARCHAR(1000) NULL,
    PRIMARY KEY (id),
    INDEX idx_notificacion_ticket (id_ticket),
    INDEX idx_notificacion_usuario (id_usuario),
    INDEX idx_notificacion_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
