# SkillBoots

# Sistema de Eliminación de Cursos

## Descripción
Este sistema implementa un mecanismo de eliminación de cursos en dos fases:

1. **Fase 1 - Marcado para eliminación**: Cuando un instructor elimina un curso, este se marca como "eliminado" y se registra la fecha de eliminación.
2. **Fase 2 - Eliminación permanente**: Los cursos marcados como eliminados hace más de 30 días se eliminan permanentemente del sistema junto con todos sus datos relacionados.

## Características
- Eliminación segura en dos fases (soft delete + hard delete)
- Verificación de permisos de usuario
- Sistema transaccional para mantener la integridad de la base de datos
- Respuesta adaptada al contexto (CLI o web)
- Registro detallado de operaciones y errores

## Requisitos del sistema
- PHP 7.0 o superior
- PDO habilitado
- Base de datos MySQL/MariaDB
- Tabla `cursos` con campos `estado` y `fecha_eliminacion`

## Archivos del sistema
- `eliminar_cursos.php`: Script principal para la eliminación de cursos

## Uso

### Para instructores (eliminación manual)
Cuando un instructor quiere eliminar un curso, el sistema:
1. Marca el curso como "eliminado"
2. Registra la fecha actual en el campo `fecha_eliminacion`
3. El curso permanece en la base de datos pero no aparece en el frontend
4. Después de 30 días, el curso será eliminado permanentemente

### Para administradores (eliminación automática)
El script puede ejecutarse en modo automático para limpiar cursos antiguos:

- **En entorno local (XAMPP)**: 
  - Acceder a: `http://localhost/ruta/a/eliminar_cursos.php?modo=automatico`
  - O crear un botón en el panel de administración que apunte a esta URL

- **En servidor de producción**:
  - Configurar un CRON job para ejecutar el script regularmente:
  ```
  0 3 * * * php /ruta/completa/eliminar_cursos.php modo=automatico
  ```

## Estructura de la base de datos
La tabla `cursos` debe tener los siguientes campos:
- `id`: Identificador único del curso
- `instructor_id`: ID del instructor propietario
- `estado`: Estado del curso ('activo', 'eliminado', etc.)
- `fecha_eliminacion`: Timestamp de cuando se marcó para eliminación

## Seguridad
- Solo el instructor propietario puede marcar un curso para eliminación
- La eliminación permanente solo ocurre después de un período de gracia de 30 días
- Todas las operaciones de eliminación utilizan transacciones para evitar datos huérfanos

## Posibles personalizaciones
- Modificar el período de retención (actualmente 30 días)
- Añadir notificaciones por email al instructor cuando su curso está por ser eliminado permanentemente
- Implementar un sistema de recuperación durante el período de gracia
# Skillboots
500e96e7395b402659304ece7b9ded784ae51913
