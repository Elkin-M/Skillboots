<?php
/**
 * Security Utility Class
 *
 * Contains security-related functions like HTML purification,
 * XSS prevention, and input validation
 */

class CourseAccess {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Check if a user has access to a specific course
     *
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param string $userRole User role (estudiante, profesor, admin)
     * @return bool True if user has access, false otherwise
     */
    public function checkAccess($user_id, $course_id, $userRole) {
        // Admins have access to all courses
        if ($userRole === 'admin') {
            return true;
        }

        // Professors have access to courses they created
        if ($userRole === 'profesor') {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM cursos
                WHERE id = ? AND instructor_id = ?
            ");
            $stmt->execute([$course_id, $user_id]);
            return $stmt->fetchColumn() > 0;
        }

        // Students have access to courses they're enrolled in
        if ($userRole === 'estudiante') {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM usuarios_cursos
                WHERE usuario_id = ? AND curso_id = ?
            ");
            $stmt->execute([$user_id, $course_id]);
            return $stmt->fetchColumn() > 0;
        }

        return false;
    }

    /**
     * Get course details by course ID
     *
     * @param int $course_id Course ID
     * @return array|false Course details or false if not found
     */
    public function getCourseDetails($curso_id) {
        $sql = "SELECT c.*, u.name as instructor_nombre, u.foto_perfil as instructor_foto, u.id as instructor_id
               FROM cursos c
               LEFT JOIN usuarios u ON c.instructor_id = u.id
               WHERE c.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class ProgressTracker {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }


        /**
     * Calculate the overall progress percentage for a course.
     *
     * @param int $curso_id Course ID
     * @param int $completados Number of completed items
     * @return float Overall progress percentage
     */
    public function calculateOverallProgress($curso_id, $completados) {
        // Consulta SQL para obtener el número total de contenidos en el curso
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM contenido_modular cm
            JOIN modulos m ON cm.modulo_id = m.id
            WHERE m.curso_id = ?
        ");
        $stmt->execute([$curso_id]);
        $total = $stmt->fetchColumn();
        
        return $total > 0 ? ($completados / $total) * 100 : 0;
    }

    /**
     * Registra el progreso del usuario en un curso
     * 
     * @param int $user_id ID del usuario
     * @param int $curso_id ID del curso
     * @param int $modulo_id ID del módulo
     * @param int $contenido_id ID del contenido
     * @return bool Retorna true si se registró correctamente, false en caso contrario
     */
    

     public function recordProgress($user_id, $curso_id, $modulo_id, $contenido_id) {
        // Verificar si ya existe un registro para este usuario y contenido
        $sql = "SELECT id FROM progreso_contenido
               WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id, $contenido_id]);
        $exists = $stmt->fetch(PDO::FETCH_COLUMN);
        
        // Si no existe, crear un registro
        if (!$exists) {
            $sql = "INSERT INTO progreso_contenido 
                   (usuario_id, curso_id, modulo_id, contenido_id, fecha_acceso)
                   VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id, $curso_id, $modulo_id, $contenido_id]);
        } else {
            // Actualizar la fecha de acceso
            $sql = "UPDATE progreso_contenido 
                   SET fecha_acceso = NOW() 
                   WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id, $curso_id, $contenido_id]);
        }
        
        // Actualizar último acceso en usuarios_cursos
        $sql = "INSERT INTO usuarios_cursos (usuario_id, curso_id, ultimo_acceso)
               VALUES (?, ?, NOW())
               ON DUPLICATE KEY UPDATE ultimo_acceso = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id]);
    }
    /**
     * Get the student's progress for a specific course.
     *
     * @param int $user_id User ID
     * @param int $curso_id Course ID
     * @return array Student progress data
     */
    public function getStudentProgress($user_id, $curso_id) {
        // Obtener todos los contenidos del curso
        $sql = "SELECT cm.id, cm.modulo_id
               FROM contenido_modular cm
               JOIN modulos m ON cm.modulo_id = m.id
               WHERE m.curso_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id]);
        $contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener contenidos completados
        $sql = "SELECT contenido_id, modulo_id
               FROM progreso_contenido
               WHERE usuario_id = ? AND curso_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id]);
        $completados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir a un formato más fácil de usar
        $idsCompletados = array_column($completados, 'contenido_id');
        $modulosProgreso = [];
        
        // Calcular progreso por módulo
        foreach ($contenidos as $contenido) {
            $modulo_id = $contenido['modulo_id'];
            
            if (!isset($modulosProgreso[$modulo_id])) {
                $modulosProgreso[$modulo_id] = [
                    'total' => 0,
                    'completados' => 0,
                    'porcentaje' => 0
                ];
            }
            
            $modulosProgreso[$modulo_id]['total']++;
            
            if (in_array($contenido['id'], $idsCompletados)) {
                $modulosProgreso[$modulo_id]['completados']++;
            }
        }
        
        // Calcular porcentajes
        foreach ($modulosProgreso as $modulo_id => &$progreso) {
            if ($progreso['total'] > 0) {
                $progreso['porcentaje'] = ($progreso['completados'] / $progreso['total']) * 100;
            }
        }
        
        // Obtener último acceso
        $sql = "SELECT ultimo_acceso FROM usuarios_cursos
               WHERE usuario_id = ? AND curso_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id]);
        $ultimo_acceso = $stmt->fetch(PDO::FETCH_COLUMN) ?: date('Y-m-d H:i:s');
        
        return [
            'completados' => count($idsCompletados),
            'total' => count($contenidos),
            'modulos' => $modulosProgreso,
            'completados_ids' => $idsCompletados, // ✅ renombrado
            'ultimo_acceso' => $ultimo_acceso
        ];
    }
}




    class CommentsManager {
        private $conn;
        
        public function __construct($conn) {
            $this->conn = $conn;
        }
        
        public function getContentComments($curso_id, $contenido_id) {
            $sql = "SELECT c.*, u.name as usuario_nombre, u.foto_perfil 
                   FROM comentarios c
                   JOIN usuarios u ON c.usuario_id = u.id
                   WHERE c.curso_id = ? AND c.contenido_id = ? AND c.comentario_padre_id IS NULL
                   ORDER BY c.fecha_creacion DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$curso_id, $contenido_id]);
            $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener respuestas para cada comentario
            foreach ($comentarios as &$comentario) {
                $sql = "SELECT r.*, u.name as usuario_nombre, u.foto_perfil 
                       FROM comentarios r
                       JOIN usuarios u ON r.usuario_id = u.id
                       WHERE r.comentario_padre_id = ?
                       ORDER BY r.fecha_creacion ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$comentario['id']]);
                $comentario['respuestas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $comentarios;
        }
    }
  
    
    class TimeUtils {
        public static function timeAgo($datetime) {
            $timestamp = strtotime($datetime);
            $difference = time() - $timestamp;
            
            if ($difference < 60) {
                return 'hace un momento';
            } elseif ($difference < 3600) {
                $minutes = floor($difference / 60);
                return $minutes . ' minuto' . ($minutes != 1 ? 's' : '') . ' atrás';
            } elseif ($difference < 86400) {
                $hours = floor($difference / 3600);
                return $hours . ' hora' . ($hours != 1 ? 's' : '') . ' atrás';
            } elseif ($difference < 604800) {
                $days = floor($difference / 86400);
                return $days . ' día' . ($days != 1 ? 's' : '') . ' atrás';
            } else {
                return date('d M Y', $timestamp);
            }
        }
    }

class CourseContent {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Load course content including modules and contents
     *
     * @param int $course_id Course ID
     * @param int $module_id Module ID
     * @param int $content_id Content ID
     * @return array Course content data
     */
    
     public function loadCourseContent($curso_id, $modulo_id = 0, $contenido_id = 0) {
        // Obtener todos los módulos del curso
        $sql = "SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id]);
        $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Depuración: Verificar si se obtuvieron módulos
        if (empty($modulos)) {
            error_log("No se encontraron módulos para el curso con ID: $curso_id");
        } else {
            error_log("Módulos encontrados: " . print_r($modulos, true));
        }
    
        // Si no hay módulo seleccionado, usar el primero
        if ($modulo_id == 0 && !empty($modulos)) {
            $modulo_id = $modulos[0]['id'];
        }
    
        // Obtener el módulo actual
        $moduloActual = null;
        foreach ($modulos as $modulo) {
            if ($modulo['id'] == $modulo_id) {
                $moduloActual = $modulo;
                break;
            }
        }
    
        // Depuración: Verificar si se encontró el módulo actual
        if (!$moduloActual) {
            error_log("No se encontró el módulo con ID: $modulo_id");
        } else {
            error_log("Módulo actual encontrado: " . print_r($moduloActual, true));
        }
    
        // Obtener contenidos del módulo actual
        $contenidos = [];
        $contenidosPorModulo = [];
    
        if ($moduloActual) {
            // Obtener contenidos del módulo actual desde la tabla contenido_modular
            $sql = "SELECT * FROM contenido_modular WHERE modulo_id = ? ORDER BY orden ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$modulo_id]);
            $contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Depuración: Verificar si se obtuvieron contenidos
            if (empty($contenidos)) {
                error_log("No se encontraron contenidos para el módulo con ID: $modulo_id");
            } else {
                error_log("Contenidos encontrados: " . print_r($contenidos, true));
            }
    
            // Si no hay contenido seleccionado, usar el primero
            if ($contenido_id == 0 && !empty($contenidos)) {
                $contenido_id = $contenidos[0]['id'];
            }
        }
    
        // Obtener el contenido actual
        $contenidoActual = null;
        foreach ($contenidos as $contenido) {
            if ($contenido['id'] == $contenido_id) {
                $contenidoActual = $contenido;
                break;
            }
        }
    
        // Depuración: Verificar si se encontró el contenido actual
        if (!$contenidoActual) {
            error_log("No se encontró el contenido con ID: $contenido_id");
        } else {
            error_log("Contenido actual encontrado: " . print_r($contenidoActual, true));
        }
    
        // Obtener todos los contenidos por módulo para la navegación
        foreach ($modulos as $modulo) {
            $sql = "SELECT * FROM contenido_modular WHERE modulo_id = ? ORDER BY orden ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$modulo['id']]);
            $contenidosPorModulo[$modulo['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        // Construir navegación anterior/siguiente
        $navegacion = ['anterior' => null, 'siguiente' => null];
    
        // Encontrar índice del contenido actual
        $indiceActual = -1;
        $indiceModuloActual = -1;
    
        // Primero, encontrar el índice del módulo actual
        foreach ($modulos as $i => $modulo) {
            if ($modulo['id'] == $modulo_id) {
                $indiceModuloActual = $i;
                break;
            }
        }
    
        // Luego, si hay un módulo actual, encontrar el índice del contenido actual
        if ($indiceModuloActual >= 0 && !empty($contenidos)) {
            foreach ($contenidos as $i => $contenido) {
                if ($contenido['id'] == $contenido_id) {
                    $indiceActual = $i;
                    break;
                }
            }
        }
    
        // Configurar navegación previa
        if ($indiceActual > 0) {
            // Contenido anterior en el mismo módulo
            $navegacion['anterior'] = [
                'id' => $contenidos[$indiceActual - 1]['id'],
                'modulo_id' => $modulo_id
            ];
        } elseif ($indiceModuloActual > 0) {
            // Último contenido del módulo anterior
            $moduloAnterior = $modulos[$indiceModuloActual - 1];
            $contenidosModuloAnterior = $contenidosPorModulo[$moduloAnterior['id']];
    
            if (!empty($contenidosModuloAnterior)) {
                $ultimoContenido = end($contenidosModuloAnterior);
                $navegacion['anterior'] = [
                    'id' => $ultimoContenido['id'],
                    'modulo_id' => $moduloAnterior['id']
                ];
            }
        }
    
        // Configurar navegación siguiente
        if ($indiceActual >= 0 && $indiceActual < count($contenidos) - 1) {
            // Contenido siguiente en el mismo módulo
            $navegacion['siguiente'] = [
                'id' => $contenidos[$indiceActual + 1]['id'],
                'modulo_id' => $modulo_id
            ];
        } elseif ($indiceModuloActual >= 0 && $indiceModuloActual < count($modulos) - 1) {
            // Primer contenido del módulo siguiente
            $moduloSiguiente = $modulos[$indiceModuloActual + 1];
            $contenidosModuloSiguiente = $contenidosPorModulo[$moduloSiguiente['id']];
    
            if (!empty($contenidosModuloSiguiente)) {
                $primerContenido = $contenidosModuloSiguiente[0];
                $navegacion['siguiente'] = [
                    'id' => $primerContenido['id'],
                    'modulo_id' => $moduloSiguiente['id']
                ];
            }
        }
    
        return [
            'modulos' => $modulos,
            'moduloActual' => $moduloActual,
            'contenidos' => $contenidos,
            'contenidoActual' => $contenidoActual,
            'contenidosPorModulo' => $contenidosPorModulo,
            'navegacion' => $navegacion
        ];
    }
    
}

class Security {
    /**
     * Purifies HTML content to prevent XSS attacks
     *
     * @param string $html The HTML content to purify
     * @return string The purified HTML content
     */
    public static function purifyHTML($html) {
        // Basic implementation - we'll sanitize the HTML
        // For a production environment, consider using HTMLPurifier library

        // Remove potentially dangerous attributes
        $html = preg_replace(
            '/<(.*?)[\s]+(on[a-z]+)[\s]*=[\s]*["\']+(.*?)["\']/i',
            '<$1',
            $html
        );

        // Remove javascript: protocol
        $html = preg_replace(
            '/<(.*?)[\s]+([a-z]+)[\s]*=[\s]*["\']javascript:(.*?)["\']/i',
            '<$1',
            $html
        );

        // Remove potentially dangerous tags
        $dangerousTags = ['script', 'iframe', 'object', 'embed', 'applet', 'form'];
        foreach ($dangerousTags as $tag) {
            $html = preg_replace('/<' . $tag . '(.*?)>(.*?)<\/' . $tag . '>/is', '', $html);
            $html = preg_replace('/<' . $tag . '(.*?)>/is', '', $html);
        }

        return $html;
    }

    /**
     * Sanitize user input to prevent XSS
     *
     * @param string $input The user input to sanitize
     * @return string The sanitized input
     */
    public static function sanitizeInput($input) {
        if (is_string($input)) {
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Generate a CSRF token for forms
     *
     * @return string The generated CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify if a CSRF token is valid
     *
     * @param string $token The token to verify
     * @return bool True if the token is valid, false otherwise
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate an email address
     *
     * @param string $email The email address to validate
     * @return bool True if the email is valid, false otherwise
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if a password is strong enough
     *
     * @param string $password The password to check
     * @return bool True if the password is strong enough, false otherwise
     */
    public static function isStrongPassword($password) {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Generate a secure hash for a password
     *
     * @param string $password The password to hash
     * @return string The hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if the password matches the hash, false otherwise
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}