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
    public function getCourseDetails($course_id) {
        $stmt = $this->conn->prepare("
            SELECT c.*, i.name AS instructor_nombre, i.foto_perfil AS instructor_foto
            FROM cursos c
            JOIN usuarios i ON c.instructor_id = i.id
            WHERE c.id = ?
        ");
        $stmt->execute([$course_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class ProgressTracker {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
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
        try {
            // Verificar si el usuario está inscrito en el curso
            $stmt = $this->conn->prepare("
                SELECT id, progreso, lecciones_completadas 
                FROM usuarios_cursos 
                WHERE usuario_id = ? AND curso_id = ?
            ");
            $stmt->execute([$user_id, $curso_id]);
            $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$inscripcion) {
                // Si no está inscrito, inscribirlo automáticamente
                $stmt = $this->conn->prepare("
                    INSERT INTO usuarios_cursos (usuario_id, curso_id, progreso, lecciones_completadas, ultimo_acceso) 
                    VALUES (?, ?, 0, 0, NOW())
                ");
                $stmt->execute([$user_id, $curso_id]);
                $inscripcion = [
                    'id' => $this->conn->lastInsertId(),
                    'progreso' => 0,
                    'lecciones_completadas' => 0
                ];
            }
            
            // Actualizar el último acceso
            $stmt = $this->conn->prepare("
                UPDATE usuarios_cursos 
                SET ultimo_acceso = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$inscripcion['id']]);
            
            // Verificar si este contenido ya fue marcado como completado
            $stmt = $this->conn->prepare("
                SELECT id FROM contenidos_completados 
                WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?
            ");
            $stmt->execute([$user_id, $curso_id, $contenido_id]);
            $yaCompletado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$yaCompletado) {
                // Marcar el contenido como completado
                $stmt = $this->conn->prepare("
                    INSERT INTO contenidos_completados (usuario_id, curso_id, modulo_id, contenido_id, fecha_completado) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$user_id, $curso_id, $modulo_id, $contenido_id]);
                
                // Incrementar lecciones completadas
                $lecciones_completadas = $inscripcion['lecciones_completadas'] + 1;
                
                // Calcular el nuevo progreso
                $stmt = $this->conn->prepare("
                    SELECT COUNT(*) as total FROM contenido_modular cm
                    JOIN modulos m ON cm.modulo_id = m.id
                    WHERE m.curso_id = ?
                ");
                $stmt->execute([$curso_id]);
                $totalContenidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($totalContenidos > 0) {
                    $progreso = ($lecciones_completadas / $totalContenidos) * 100;
                    $progreso = min(100, $progreso); // Asegurar que no exceda 100%
                } else {
                    $progreso = 0;
                }
                
                // Actualizar progreso y lecciones completadas
                $stmt = $this->conn->prepare("
                    UPDATE usuarios_cursos 
                    SET progreso = ?, lecciones_completadas = ? 
                    WHERE id = ?
                ");
                $stmt->execute([round($progreso), $lecciones_completadas, $inscripcion['id']]);
                
                // Si el progreso llegó a 100%, registrar notificación
                if ($progreso == 100) {
                    $stmt = $this->conn->prepare("
                        INSERT INTO notifications (user_id, role, message, link, icon, `read`, created_at)
                        VALUES (?, 'student', 'Has completado el curso exitosamente', '/cursos/certificado/{$curso_id}', 'graduation-cap', 0, NOW())
                    ");
                    $stmt->execute([$user_id]);
                }
            }
            
            return true;
        } catch (PDOException $e) {
            // Registrar el error
            error_log("Error registrando progreso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the student's progress for a specific course.
     *
     * @param int $user_id User ID
     * @param int $curso_id Course ID
     * @return array Student progress data
     */
    public function getStudentProgress($user_id, $curso_id) {
        try {
            // Consulta SQL optimizada para la estructura actual
            $stmt = $this->conn->prepare("
                SELECT 
                    c.total_lecciones AS total, 
                    COALESCE(uc.lecciones_completadas, 0) AS completados, 
                    COALESCE(uc.ultimo_acceso, 'Nunca') AS ultimo_acceso
                FROM cursos c
                LEFT JOIN usuarios_cursos uc ON uc.curso_id = c.id AND uc.usuario_id = ?
                WHERE c.id = ?
            ");
    
            // Ejecutar consulta
            $stmt->execute([$user_id, $curso_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Si no hay resultados, devolver valores predeterminados
            if (!$result) {
                return ['total' => 0, 'completados' => 0, 'ultimo_acceso' => 'Nunca'];
            }
    
            return $result;
        } catch (Exception $e) {
            // Manejo de errores
            error_log("Error en getStudentProgress: " . $e->getMessage());
            return ['total' => 0, 'completados' => 0, 'ultimo_acceso' => 'Nunca'];
        }
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
    public function loadCourseContent($course_id, $module_id = null, $content_id = null) {
        $data = [
            'modulos' => [],
            'moduloActual' => null,
            'contenidos' => [],
            'contenidoActual' => null,
            'navegacion' => [
                'anterior' => null,
                'siguiente' => null
            ],
            'contenidosPorModulo' => []
        ];
    
        // Verificar que el curso existe y está activo
        $stmt = $this->conn->prepare("
            SELECT id, nombre, descripcion FROM cursos
            WHERE id = ? AND estate = 'activo'
        ");
        $stmt->execute([$course_id]);
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$curso) {
            return ['error' => 'Curso no encontrado o inactivo'];
        }
        
        // Cargar módulos
        $stmt = $this->conn->prepare("
            SELECT id, titulo, descripcion, orden FROM modulos
            WHERE curso_id = ?
            ORDER BY orden ASC
        ");
        $stmt->execute([$course_id]);
        $data['modulos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Si no se proporciona module_id, usar el primer módulo
        if (!$module_id && !empty($data['modulos'])) {
            $module_id = $data['modulos'][0]['id'];
        }
    
        // Cargar módulo actual
        if ($module_id) {
            $stmt = $this->conn->prepare("
                SELECT id, titulo, descripcion, orden FROM modulos
                WHERE id = ? AND curso_id = ?
            ");
            $stmt->execute([$module_id, $course_id]);
            $data['moduloActual'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data['moduloActual']) {
                return ['error' => 'Módulo no encontrado para este curso'];
            }
        }
    
        // Cargar contenidos para cada módulo
        foreach ($data['modulos'] as $modulo) {
            $stmt = $this->conn->prepare("
                SELECT id, titulo, tipo, contenido, orden 
                FROM contenido_modular
                WHERE modulo_id = ?
                ORDER BY orden ASC
            ");
            $stmt->execute([$modulo['id']]);
            $data['contenidosPorModulo'][$modulo['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        // Cargar contenidos para el módulo actual
        if ($module_id) {
            $stmt = $this->conn->prepare("
                SELECT id, titulo, tipo, contenido, orden 
                FROM contenido_modular
                WHERE modulo_id = ?
                ORDER BY orden ASC
            ");
            $stmt->execute([$module_id]);
            $data['contenidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no se proporciona content_id, usar el primer contenido
            if (!$content_id && !empty($data['contenidos'])) {
                $content_id = $data['contenidos'][0]['id'];
            }
        }
    
        // Cargar contenido actual
        if ($content_id) {
            $stmt = $this->conn->prepare("
                SELECT id, titulo, tipo, contenido, orden 
                FROM contenido_modular
                WHERE id = ? AND modulo_id = ?
            ");
            $stmt->execute([$content_id, $module_id]);
            $data['contenidoActual'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data['contenidoActual']) {
                return ['error' => 'Contenido no encontrado para este módulo'];
            }
            
            // Cargar navegación
            if ($data['contenidoActual']) {
                // Contenido anterior en el mismo módulo
                $stmt = $this->conn->prepare("
                    SELECT id, modulo_id, titulo FROM contenido_modular
                    WHERE modulo_id = ? AND orden < ?
                    ORDER BY orden DESC
                    LIMIT 1
                ");
                $stmt->execute([$module_id, $data['contenidoActual']['orden']]);
                $data['navegacion']['anterior'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
                // Siguiente contenido en el mismo módulo
                $stmt = $this->conn->prepare("
                    SELECT id, modulo_id, titulo FROM contenido_modular
                    WHERE modulo_id = ? AND orden > ?
                    ORDER BY orden ASC
                    LIMIT 1
                ");
                $stmt->execute([$module_id, $data['contenidoActual']['orden']]);
                $data['navegacion']['siguiente'] = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Si no hay contenido siguiente en este módulo, buscar el primer contenido del siguiente módulo
                if (!$data['navegacion']['siguiente']) {
                    $stmt = $this->conn->prepare("
                        SELECT id, titulo FROM modulos
                        WHERE curso_id = ? AND orden > (
                            SELECT orden FROM modulos WHERE id = ?
                        )
                        ORDER BY orden ASC
                        LIMIT 1
                    ");
                    $stmt->execute([$course_id, $module_id]);
                    $nextModule = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($nextModule) {
                        $stmt = $this->conn->prepare("
                            SELECT id, modulo_id, titulo FROM contenido_modular
                            WHERE modulo_id = ?
                            ORDER BY orden ASC
                            LIMIT 1
                        ");
                        $stmt->execute([$nextModule['id']]);
                        $data['navegacion']['siguiente'] = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($data['navegacion']['siguiente']) {
                            $data['navegacion']['siguiente']['moduloTitulo'] = $nextModule['titulo'];
                        }
                    }
                }
                
                // Si no hay contenido anterior en este módulo, buscar el último contenido del módulo anterior
                if (!$data['navegacion']['anterior']) {
                    $stmt = $this->conn->prepare("
                        SELECT id, titulo FROM modulos
                        WHERE curso_id = ? AND orden < (
                            SELECT orden FROM modulos WHERE id = ?
                        )
                        ORDER BY orden DESC
                        LIMIT 1
                    ");
                    $stmt->execute([$course_id, $module_id]);
                    $prevModule = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($prevModule) {
                        $stmt = $this->conn->prepare("
                            SELECT id, modulo_id, titulo FROM contenido_modular
                            WHERE modulo_id = ?
                            ORDER BY orden DESC
                            LIMIT 1
                        ");
                        $stmt->execute([$prevModule['id']]);
                        $data['navegacion']['anterior'] = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($data['navegacion']['anterior']) {
                            $data['navegacion']['anterior']['moduloTitulo'] = $prevModule['titulo'];
                        }
                    }
                }
            }
            
            // Si es de tipo 'quiz', cargar las preguntas asociadas
            if ($data['contenidoActual']['tipo'] === 'quiz') {
                $stmt = $this->conn->prepare("
                    SELECT id, pregunta, opciones, respuesta_correcta
                    FROM quizzes
                    WHERE modulo_id = ?
                ");
                $stmt->execute([$module_id]);
                $data['contenidoActual']['preguntas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    
        // Agregar información adicional del curso
        $data['informacionCurso'] = [
            'id' => $curso['id'],
            'nombre' => $curso['nombre'],
            'descripcion' => $curso['descripcion']
        ];
    
        return $data;
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