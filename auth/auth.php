<?php
class Auth {
    // Definición de todos los permisos disponibles en el sistema
    private static $rolePermissions = [
        'profesor' => [
            'view_public_content',      // Ver contenido público
            'view_dashboard',           // Ver dashboard
            'manage_courses',           // Crear y editar cursos
            'create_classroom',         // Crear aulas virtuales
            'invite_users',             // Invitar usuarios a cursos
            'view_progress',            // Ver progreso de estudiantes
            'view_notes',               // Ver notas de estudiantes
            'issue_certificates',       // Emitir certificados
            'access_private_courses',   // Acceder a cursos privados
            'create_course_material'    // Crear material del curso
        ],
        'estudiante' => [
            'view_public_content',      // Ver contenido público
            'view_dashboard',           // Ver dashboard
            'view_own_progress',        // Ver progreso propio
            'take_notes',               // Tomar notas
            'download_certificate',     // Descargar certificado
            'join_private_courses',     // Unirse a cursos privados con código
            'view_enrolled_courses',    // Ver cursos inscritos
            'submit_assignments'        // Enviar tareas
        ],
        'visitante' => [
            'view_public_content',      // Ver contenido público
            'register',                 // Registrarse en la plataforma
            'browse_public_courses'     // Navegar cursos públicos
        ]
    ];
    
    // Métodos de autenticación if not empty return true else return false
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    // Obtener el rol del usuario actual en caso de no estar autenticado
    // o no definido se le asigna el rol de visitante
    public static function getUserRole() {
        if (!self::isAuthenticated()) {
            return 'visitante';
        }
        return isset($_SESSION['user_rol']) ? $_SESSION['user_rol'] : 'visitante';
    }
    
    // Verificar si el usuario actual tiene un permiso específico
    public static function hasPermission($permission) {
        $role = self::getUserRole();
        return in_array($permission, self::$rolePermissions[$role] ?? []);
    }
    
    // Verificar si el usuario tiene al menos uno de los permisos de la lista
    public static function hasAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
    
    // Verificar si el usuario tiene todos los permisos de la lista
    public static function hasAllPermissions($permissions) {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
}
?>