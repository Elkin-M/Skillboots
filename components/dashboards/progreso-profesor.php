<script>


    // Datos de muestra - En un entorno real, estos datos vendrían de tu base de datos
    const cursos = [
        {
            id: 1,
            titulo: "Desarrollo Web Frontend",
            descripcion: "HTML, CSS y JavaScript para principiantes",
            imagen: "https://via.placeholder.com/400x225?text=Desarrollo+Web",
            duracion: 7.5,
            colorProgreso: "primary",
            ultimoAcceso: "Ayer",
            inscritos: {
                completadas: 15,
                total: 20
            },
            estado: "Activo",
            estadoClase: "success"
        },
        {
            id: 2,
            titulo: "Diseño UX/UI Profesional",
            descripcion: "Aprende a crear experiencias de usuario atractivas",
            imagen: "https://via.placeholder.com/400x225?text=Diseño+UX/UI",
            duracion: 11,
            colorProgreso: "warning",
            ultimoAcceso: "Hace 3 días",
            inscritos: {
                completadas: 9,
                total: 20
            },
            estado: "En progreso",
            estadoClase: "success"
        },
        {
            id: 3,
            titulo: "JavaScript Avanzado",
            descripcion: "Frameworks modernos y desarrollo frontend",
            imagen: "https://via.placeholder.com/400x225?text=JavaScript+Avanzado",
            duracion: 17,
            colorProgreso: "danger",
            ultimoAcceso: "Hace 1 semana",
            inscritos: {
                completadas: 3,
                total: 20
            },
            estado: "En progreso",
            estadoClase: "success"
        },
        {
            id: 4,
            titulo: "Marketing Digital",
            descripcion: "Estrategias efectivas para promoción online",
            imagen: "https://via.placeholder.com/400x225?text=Marketing+Digital",
            duracion: 0,
            colorProgreso: "success",
            ultimoAcceso: "Hace 2 semanas",
            inscritos: {
                completadas: 18,
                total: 18
            },
            estado: "Finalizado",
            estadoClase: "success"
        },
        {
            id: 5,
            titulo: "Diseño Gráfico con Adobe",
            descripcion: "Photoshop, Illustrator y herramientas de diseño",
            imagen: "https://via.placeholder.com/400x225?text=Diseño+Gráfico",
            duracion: 8,
            colorProgreso: "info",
            ultimoAcceso: "Hoy",
            inscritos: {
                completadas: 12,
                total: 20
            },
            estado: "En progreso",
            estadoClase: "success"
        },
        {
            id: 6,
            titulo: "Python para Ciencia de Datos",
            descripcion: "Análisis, visualización y machine learning",
            imagen: "https://via.placeholder.com/400x225?text=Python",
            duracion: 20,
            colorProgreso: "secondary",
            ultimoAcceso: "Inscrito: Ayer",
            inscritos: {
                completadas: 0,
                total: 25
            },
            estado: "No iniciado",
            estadoClase: "muted"
        }
    ];

    // Función para cargar los cursos
    function cargarCursos() {
        const container = document.getElementById('courses-container');
        container.innerHTML = '';
        
        cursos.forEach(curso => {
            const cursoElement = document.createElement('div');
            cursoElement.className = 'col-lg-4 col-md-6 mb-4';
            cursoElement.innerHTML = `
                <div class="card border-0 shadow-sm rounded course-card">
                    <div class="position-relative">
                        <img class="card-img-top" src="${curso.imagen}" alt="${curso.titulo}">
                        <div class="position-absolute top-0 end-0 mt-3 me-3">
                            <button class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="text-muted"><i class="far fa-clock text-primary me-1"></i>${curso.ultimoAcceso}</small>
                            <small class="text-${curso.estadoClase}">
                                <i class="fa fa-circle me-1"></i>${curso.estado}
                            </small>
                        </div>
                        <h5 class="card-title">${curso.titulo}</h5>
                        <p class="card-text text-muted">${curso.descripcion}</p>
                        
                        <div class="d-flex justify-content-between mt-2">
                            <small>${curso.inscritos.completadas}/${curso.inscritos.total} inscritos</small>
                            <small>Duración: ${curso.duracion} horas</small>
                        </div>
                        
                        <div class="mt-4">
                            <button class="btn btn-primary btn-block w-100 continue-btn" data-curso-id="${curso.id}">
                                <i class="fas fa-users me-2"></i>Ver personas inscritas
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(cursoElement);
        });
        
        // Agregar event listeners a los botones
        document.querySelectorAll('.continue-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cursoId = parseInt(this.getAttribute('data-curso-id'));
                mostrarPersonasInscritas(cursoId);
            });
        });
    }

    // Nueva función para mostrar personas inscritas
    function mostrarPersonasInscritas(cursoId) {
        const curso = cursos.find(c => c.id === cursoId);
        if (!curso) return;
        
        const modalTitle = document.getElementById('courseDetailModalLabel');
        const modalContent = document.getElementById('courseDetailContent');
        
        modalTitle.textContent = `Estudiantes inscritos en ${curso.titulo}`;
        
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-12">
                    <h5>Total de inscritos: ${curso.inscritos.completadas}/${curso.inscritos.total}</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Fecha de inscripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí irían los datos reales de los estudiantes -->
                                <tr>
                                    <td>Ejemplo Estudiante</td>
                                    <td>01/03/2024</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Ver perfil</button>
                                        <button class="btn btn-sm btn-danger">Dar de baja</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('courseDetailModal'));
        modal.show();
    }

    // Inicializar la página cargando los cursos
    document.addEventListener('DOMContentLoaded', function() {
        cargarCursos();
    });
</script>
