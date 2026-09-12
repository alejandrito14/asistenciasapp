-- Permite que un evento del calendario sea general o específico para un maestro.
ALTER TABLE calendario_escolar
    ADD COLUMN maestro_id INT NULL AFTER descripcion,
    ADD INDEX idx_calendario_maestro (maestro_id),
    ADD CONSTRAINT fk_calendario_maestro
        FOREIGN KEY (maestro_id) REFERENCES maestros(id)
        ON DELETE SET NULL;
