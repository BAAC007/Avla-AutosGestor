-- OPCIONAL: fichas técnicas de DEMOSTRACIÓN para los vehículos id 1-5 del dump original
-- (Corolla, RAV4, F-150, Serie 3, Clio). Ejecútalo solo si tus ids coinciden. Ejecutar tras migracion_v2.sql.
SET NAMES utf8mb4;

-- Datos técnicos de DEMOSTRACIÓN para los 5 vehículos existentes
UPDATE vehiculo SET combustible='híbrido',  transmision='automático', potencia_cv=140, puertas=5, plazas=5, etiqueta_dgt='ECO', garantia_meses=36,
       descripcion='Berlina compacta híbrida, 0 km, lista para entrega.' WHERE id = 1;
UPDATE vehiculo SET combustible='híbrido',  transmision='automático', potencia_cv=218, puertas=5, plazas=5, etiqueta_dgt='ECO', garantia_meses=12, propietarios_anteriores=1,
       matricula='1234KLM', descripcion='SUV híbrido de un solo propietario con revisiones al día.' WHERE id = 2;
UPDATE vehiculo SET combustible='gasolina', transmision='automático', potencia_cv=400, puertas=4, plazas=5, etiqueta_dgt='C',   garantia_meses=24,
       descripcion='Pickup de gran tamaño, 0 km.' WHERE id = 3;
UPDATE vehiculo SET combustible='diésel',   transmision='automático', potencia_cv=190, puertas=4, plazas=5, etiqueta_dgt='C',   garantia_meses=12, propietarios_anteriores=1,
       matricula='5678NPR', descripcion='Berlina premium con historial de mantenimiento completo.' WHERE id = 4;
UPDATE vehiculo SET combustible='gasolina', transmision='manual',     potencia_cv=91,  puertas=5, plazas=5, etiqueta_dgt='C',   garantia_meses=24,
       descripcion='Utilitario urbano, 0 km, bajo consumo.' WHERE id = 5;

