<?php
include 'Extrae.php'; // If this is the correct path?>
<script>





    // Función para cargar los cursos
    function cargarCursos() {
        const container = document.getElementById('courses-container');
        container.innerHTML = '';
        
        cursos.forEach(curso => {
            // Determinar el texto para mostrar en "horas restantes"
            let horasTexto = curso.progreso === 100 ? 'Curso completado' : `${curso.horasRestantes} horas restantes`;
            
            // Determinar el texto y clase para el botón de acción
            let botonTexto = curso.progreso === 100 ? 'Ver certificado' : (curso.progreso === 0 ? 'Comenzar' : 'Continuar');
            let botonClase = curso.progreso === 100 ? 'btn-success' : 'btn-primary';
            
            // Determinar si la barra de progreso debe tener animación
            let animacionBarra = curso.progreso > 0 && curso.progreso < 100 ? 'progress-bar-striped progress-bar-animated' : '';
            
            // Crear el elemento de curso
            const cursoElement = document.createElement('div');
            cursoElement.className = 'col-lg-4 col-md-6 mb-4';
            cursoElement.innerHTML = `
                <div class="card border-0 shadow-sm rounded course-card">
                    <div class="position-relative">
                        <img class="card-img-top" src="${curso.imagen}" alt="${curso.titulo}">
                        <div class="position-absolute top-0 end-0 mt-3 me-3">
                            <span class="badge bg-${curso.colorProgreso} py-2 px-3">${curso.progreso}% Completado</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="text-muted"><i class="far fa-clock text-primary me-1"></i>${curso.ultimoAcceso}</small>
                            <small class="text-${curso.estadoClase}">
                                <i class="${curso.estado === 'En progreso' ? 'fa fa-spinner fa-spin' : (curso.estado === 'Finalizado' ? 'fa fa-check-circle' : 'fa fa-hourglass-start')} me-1"></i>
                                ${curso.estado}
                            </small>
                        </div>
                        <h5 class="card-title">${curso.titulo}</h5>
                        <p class="card-text text-muted">${curso.descripcion}</p>
                        
                        <!-- Barra de progreso -->
                        <div class="progress mt-3" style="height: 10px;">
                            <div class="progress-bar ${animacionBarra} bg-${curso.colorProgreso}" role="progressbar" 
                                style="width: ${curso.progreso}%" aria-valuenow="${curso.progreso}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small>${curso.lecciones.completadas}/${curso.lecciones.total} lecciones</small>
                            <small>${horasTexto}</small>
                        </div>
                        
                        <div class="mt-4">
                            <button class="btn ${botonClase} btn-block w-100 continue-btn" data-curso-id="${curso.id}">${botonTexto}</button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(cursoElement);
        });
        
        // Agregar event listeners a los botones de continuar
        document.querySelectorAll('.continue-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cursoId = parseInt(this.getAttribute('data-curso-id'));
                // Redirigir a iniciar-curso.php con el ID del curso
                window.location.href = `iniciar-curso.php?id=${cursoId}`;
            });
        });
    }

    // Función para mostrar detalles del curso en un modal
    function mostrarDetalleCurso(cursoId) {
        const curso = cursos.find(c => c.id === cursoId);
        if (!curso) return;
        
        const modalTitle = document.getElementById('courseDetailModalLabel');
        const modalContent = document.getElementById('courseDetailContent');
        const continueBtn = document.getElementById('continueCourseBtn');
        
        modalTitle.textContent = curso.titulo;
        
        // Crear contenido del modal
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <img src="${curso.imagen}" alt="${curso.titulo}" class="img-fluid rounded">
                </div>
                <div class="col-md-6">
                    <h4>${curso.titulo}</h4>
                    <p>${curso.descripcion}</p>
                    <div class="d-flex justify-content-between my-3">
                        <span><strong>Progreso:</strong> ${curso.progreso}%</span>
                        <span><strong>Estado:</strong> ${curso.estado}</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-${curso.colorProgreso}" role="progressbar" 
                            style="width: ${curso.progreso}%" aria-valuenow="${curso.progreso}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p><strong>Lecciones completadas:</strong> ${curso.lecciones.completadas} de ${curso.lecciones.total}</p>
                    <p><strong>Tiempo estimado restante:</strong> ${curso.horasRestantes} horas</p>
                    <p><strong>Último acceso:</strong> ${curso.ultimoAcceso}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Contenido del curso</h5>
                    <div class="list-group">
                        ${generarListaLecciones(curso)}
                    </div>
                </div>
            </div>
        `;
        
        // Configurar el botón de continuar
        continueBtn.textContent = curso.progreso === 100 ? 'Ver certificado' : (curso.progreso === 0 ? 'Comenzar curso' : 'Continuar curso');
        continueBtn.className = `btn ${curso.progreso === 100 ? 'btn-success' : 'btn-primary'}`;
        continueBtn.onclick = function() {
            const cursoId = curso.id;
            window.location.href = `iniciar-curso.php?id=${cursoId}`;
        };
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('courseDetailModal'));
        modal.show();
    }

    // Función para generar una lista de lecciones de ejemplo
    function generarListaLecciones(curso) {
        let html = '';
        const leccionesCompletadas = curso.lecciones.completadas;
        const leccionesTotal = curso.lecciones.total;
        
        for (let i = 1; i <= leccionesTotal; i++) {
            const completada = i <= leccionesCompletadas;
            const actual = i === leccionesCompletadas + 1;
            
            html += `
                <a href="#" class="list-group-item list-group-item-action ${actual ? 'active' : ''} ${completada ? 'list-group-item-success' : ''}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Lección ${i}: ${getTitulo(curso.titulo, i)}</h6>
                        ${completada ? '<span><i class="fas fa-check-circle"></i> Completada</span>' : 
                          actual ? '<span><i class="fas fa-play-circle"></i> Siguiente</span>' : 
                          '<span><i class="fas fa-lock"></i> Bloqueada</span>'}
                    </div>
                    <small>${getDuracion(i)}</small>
                </a>
            `;
        }
        
        return html;
    }

    // Función auxiliar para generar títulos de lección
    function getTitulo(cursoTitulo, num) {
        if (cursoTitulo.includes("Desarrollo Web")) {
            const titulos = [
                "Introducción a HTML", "Estructura básica", "Elementos semánticos", 
                "CSS Fundamentals", "Box Model", "Flexbox", "Grid Layout", 
                "JavaScript Básico", "DOM Manipulation", "Eventos", "APIs"
            ];
            return titulos[num % titulos.length];
        } else if (cursoTitulo.includes("Diseño UX")) {
            const titulos = [
                "Principios de UX", "Investigación de usuarios", "Personas", 
                "Wireframing", "Prototipado", "Usabilidad", "UI Patterns"
            ];
            return titulos[num % titulos.length];
        } else {
            return `Tema ${num} del curso`;
        }
    }

    // Función auxiliar para generar duraciones aleatorias
    function getDuracion(num) {
        const duraciones = ["20 minutos", "35 minutos", "45 minutos", "1 hora", "1.5 horas"];
        return duraciones[num % duraciones.length];
    }

    // Inicializar la página cargando los cursos
    document.addEventListener('DOMContentLoaded', function() {
        cargarCursos();
    });
</script>
