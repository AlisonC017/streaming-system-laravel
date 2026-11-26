-- SP para las vistas de suscripcion y metodos de pago del cliente (Dila)
-- Correr DESPUES de Seguridad_roles.sql (al final da permisos a rol_app)

USE streamingDB;
GO

/** SUSCRIPCION DEL CLIENTE */
-- Devuelve la suscripcion vigente del cliente (o la ultima cancelada) con los datos del plan
-- y los ultimos 4 digitos de la tarjeta con la que se pago por ultima vez
CREATE OR ALTER PROCEDURE sp_obtener_suscripcion_cliente
    @id_usuario INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM cliente WHERE id_usuario = @id_usuario)
            THROW 50350, 'El cliente no existe.', 1;

        SELECT TOP 1
               s.id_suscripcion, s.id_usuario, s.id_plan, s.fecha_inicio, s.fecha_proximo_cobro,
               s.estado, s.fecha_cancelacion,
               p.nombre_plan, p.precio, p.calidad,
               (SELECT TOP 1 m.ultimos4
                FROM pago pg
                JOIN metodo_pago m ON m.id_metodo = pg.id_metodo
                WHERE pg.id_suscripcion = s.id_suscripcion
                ORDER BY pg.fecha_pago DESC) AS ultimos4
        FROM suscripcion s
        JOIN planes p ON p.id_plan = s.id_plan
        WHERE s.id_usuario = @id_usuario
        ORDER BY CASE WHEN s.estado <> 'Cancelada' THEN 0 ELSE 1 END, s.id_suscripcion DESC;
    END TRY
    BEGIN CATCH
        THROW;
    END CATCH
END;
GO

/** METODOS DE PAGO DEL CLIENTE */
-- Lista las tarjetas del cliente sin el numero encriptado (solo ultimos 4)
CREATE OR ALTER PROCEDURE sp_listar_metodos_cliente
    @id_usuario INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        SELECT id_metodo, id_usuario, ultimos4, fecha_vencimiento, estado
        FROM metodo_pago
        WHERE id_usuario = @id_usuario
        ORDER BY CASE WHEN estado = 'Activo' THEN 0 ELSE 1 END, id_metodo DESC;
    END TRY
    BEGIN CATCH
        THROW;
    END CATCH
END;
GO

-- PERMISOS: la app de Laravel (rol_app) solo puede ejecutarlos, no leer metodo_pago directo
GRANT EXECUTE ON sp_obtener_suscripcion_cliente TO rol_app;
GRANT EXECUTE ON sp_listar_metodos_cliente      TO rol_app;
GO

-- PRUEBA
-- EXEC sp_obtener_suscripcion_cliente @id_usuario = 3;
-- EXEC sp_listar_metodos_cliente @id_usuario = 3;
