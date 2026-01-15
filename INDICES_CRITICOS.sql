-- ============================================
-- ÍNDICES CRÍTICOS - ProdFlow
-- ============================================
-- Ejecutar en base de datos en producción
-- Tiempo estimado: 2-5 minutos
-- ============================================

-- 1. ÍNDICES EN avance_fases (PRIORIDAD CRÍTICA)
ALTER TABLE avance_fases ADD INDEX idx_programa_id (programa_id);
ALTER TABLE avance_fases ADD INDEX idx_fase_id (fase_id);
ALTER TABLE avance_fases ADD INDEX idx_responsable_id (responsable_id);
ALTER TABLE avance_fases ADD INDEX idx_estado (estado);
ALTER TABLE avance_fases ADD INDEX idx_activo (activo);

-- Índice compuesto para búsquedas frecuentes
ALTER TABLE avance_fases ADD INDEX idx_programa_fase (programa_id, fase_id);
ALTER TABLE avance_fases ADD INDEX idx_programa_estado (programa_id, estado);
ALTER TABLE avance_fases ADD INDEX idx_fase_estado (fase_id, estado);

-- Índice para ordenamientos
ALTER TABLE avance_fases ADD INDEX idx_updated_at (updated_at);
ALTER TABLE avance_fases ADD INDEX idx_fecha_fin (fecha_fin);

-- 2. ÍNDICES EN programas (PRIORIDAD ALTA)
ALTER TABLE programas ADD INDEX idx_proyecto_id (proyecto_id);
ALTER TABLE programas ADD INDEX idx_activo (activo);
ALTER TABLE programas ADD INDEX idx_perfil_programa_id (perfil_programa_id);
ALTER TABLE programas ADD INDEX idx_responsable_inicial_id (responsable_inicial_id);
ALTER TABLE programas ADD INDEX idx_creado_por (creado_por);

-- Índices compuestos
ALTER TABLE programas ADD INDEX idx_proyecto_activo (proyecto_id, activo);
ALTER TABLE programas ADD INDEX idx_perfil_activo (perfil_programa_id, activo);

-- 3. ÍNDICES EN proyectos
ALTER TABLE proyectos ADD INDEX idx_cliente_id (cliente_id);
ALTER TABLE proyectos ADD INDEX idx_activo (activo);
ALTER TABLE proyectos ADD INDEX idx_finalizado (finalizado);

-- 4. ÍNDICES EN fases
ALTER TABLE fases ADD INDEX idx_area_id (area_id);
ALTER TABLE fases ADD INDEX idx_activo (activo);
ALTER TABLE fases ADD INDEX idx_orden (orden);
ALTER TABLE fases ADD INDEX idx_estado (estado);

-- 5. ÍNDICES EN clientes
ALTER TABLE clientes ADD INDEX idx_activo (activo);

-- 6. ÍNDICES EN usuarios
ALTER TABLE users ADD INDEX idx_area_id (area_id);
ALTER TABLE users ADD INDEX idx_fase_id (fase_id);
ALTER TABLE users ADD INDEX idx_active (active);

-- 7. ÍNDICES EN RELACIONES
ALTER TABLE fase_user ADD INDEX idx_fase_id (fase_id);
ALTER TABLE fase_user ADD INDEX idx_user_id (user_id);
ALTER TABLE fase_user ADD UNIQUE INDEX idx_fase_user_unique (fase_id, user_id);

-- 8. ÍNDICES EN activity_log (IMPORTANTE)
ALTER TABLE activity_log ADD INDEX idx_subject_type_id (subject_type, subject_id);
ALTER TABLE activity_log ADD INDEX idx_created_by (created_by);
ALTER TABLE activity_log ADD INDEX idx_created_at (created_at);

-- 9. ÍNDICES EN cache (si se mantiene en BD)
ALTER TABLE cache ADD PRIMARY KEY (key);
ALTER TABLE cache ADD INDEX idx_expiration (expiration);

-- 10. ÍNDICES EN jobs/failed_jobs
ALTER TABLE jobs ADD INDEX idx_queue (queue);
ALTER TABLE jobs ADD INDEX idx_reserved_at (reserved_at);

-- ============================================
-- VERIFICAR ÍNDICES CREADOS
-- ============================================
-- Ejecutar después para validar:
-- SHOW INDEX FROM avance_fases;
-- SHOW INDEX FROM programas;

-- ============================================
-- OPTIMIZACIONES ADICIONALES
-- ============================================

-- Optimizar tablas después de agregar índices
OPTIMIZE TABLE avance_fases;
OPTIMIZE TABLE programas;
OPTIMIZE TABLE proyectos;
OPTIMIZE TABLE fases;
OPTIMIZE TABLE users;

-- Analizar tablas para mejor ejecución
ANALYZE TABLE avance_fases;
ANALYZE TABLE programas;
ANALYZE TABLE proyectos;
ANALYZE TABLE fases;

-- ============================================
-- VERIFICAR ESTADÍSTICAS DE QUERIES
-- ============================================
-- Revisar tamaño de tablas:
-- SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb 
-- FROM information_schema.tables 
-- WHERE table_schema = 'prodflow';

-- ============================================
-- MANTENIMIENO PERIÓDICO
-- ============================================
-- Ejecutar semanalmente:
-- OPTIMIZE TABLE avance_fases;
-- ANALYZE TABLE avance_fases;

-- Ejecutar mensualmente:
-- REPAIR TABLE avance_fases;
