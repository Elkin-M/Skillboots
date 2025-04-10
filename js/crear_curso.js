
    // Variables para manejar el conteo de elementos
    let unidadCounter = 1;
    let presentacionCounter = 3; // Empezamos con 3 elementos de presentación
    let recursosCounter = {};
    let actividadesCounter = {};

    // Inicializar contadores para la primera unidad
    recursosCounter['unidad-1'] = 3;
    actividadesCounter['unidad-1'] = 3;

    // Función para mostrar una alerta
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alertDiv);
    }

    // Función para mostrar/ocultar una sección
    function toggleSection(sectionId) {
        const content = document.getElementById(sectionId + '-content');
        const icon = document.querySelector(`#${sectionId} .fa-chevron-down, #${sectionId} .fa-chevron-right`);

        if (content) {
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                if (icon) icon.className = 'fas fa-chevron-down me-2';
            } else {
                content.style.display = 'none';
                if (icon) icon.className = 'fas fa-chevron-right me-2';
            }
        }
    }

    // Función para añadir un elemento a una sección (elementos de presentación)
    function addElement(sectionId, event) {
        event.stopPropagation(); // Evitar que se cierre la sección

        const container = document.getElementById(sectionId + '-content');
        const elementCount = sectionId === 'presentacion' ? presentacionCounter++ : 0;

        const newElement = document.createElement('div');
        newElement.className = 'activity-item d-flex justify-content-between align-items-center flex-wrap';

        newElement.innerHTML = `
            <div>
                <span class="circle-indicator gray-indicator"></span>
                <input type="text" class="form-control-plaintext" name="${sectionId}[${elementCount}][titulo]" value="Nuevo elemento" style="display: inline-block; width: auto;">
                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                    <i class="fas fa-edit"></i> Contenido
                </button>
            </div>
            <div>
                <select class="form-select form-select-sm" name="${sectionId}[${elementCount}][tipo]" style="width: auto; display: inline-block;">
                    <option value="foro">Foro</option>
                    <option value="cuestionario">Cuestionario</option>
                    <option value="enlace">Enlace</option>
                </select>
                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="content-details w-100 mt-2" style="display: none;">
                <textarea class="form-control" name="${sectionId}[${elementCount}][contenido]" rows="3" placeholder="Descripción y detalles del contenido..."></textarea>
                <div class="mt-2 small text-muted">Ingresa instrucciones, contenido del foro, preguntas, etc.</div>

                <!-- Contenedor para el foro -->
                <div id="foro-container" style="display: none;">
                    <label for="foro-descripcion">Descripción del Foro:</label>
                    <textarea class="form-control" id="foro-descripcion" name="${sectionId}[${elementCount}][foro_descripcion]" rows="3"></textarea>
                </div>

                <!-- Contenedor para el quiz -->
                <div id="quiz-container" style="display: none;">
                    <label for="quiz-pregunta">Pregunta del Quiz:</label>
                    <input type="text" class="form-control" id="quiz-pregunta" name="${sectionId}[${elementCount}][quiz_pregunta]">
                    <label for="quiz-respuesta">Respuesta Correcta:</label>
                    <input type="text" class="form-control" id="quiz-respuesta" name="${sectionId}[${elementCount}][quiz_respuesta]">
                </div>

                <!-- Contenedor para el archivo -->
                <div id="archivo-container" style="display: none;">
                    <label for="archivo-upload">Subir Archivo:</label>
                    <input type="file" class="form-control" id="archivo-upload" name="${sectionId}[${elementCount}][archivo]">
                    <label for="archivo-descripcion">Descripción del Archivo:</label>
                    <textarea class="form-control" id="archivo-descripcion" name="${sectionId}[${elementCount}][archivo_descripcion]" rows="3"></textarea>
                </div>
            </div>
        `;

        container.appendChild(newElement);
    }

    // Función para añadir una nueva unidad
    function addUnidad() {
        unidadCounter++;
        const unidadId = `unidad-${unidadCounter}`;

        // Inicializar contadores para esta nueva unidad
        recursosCounter[unidadId] = 0;
        actividadesCounter[unidadId] = 0;

        const unidadHtml = `
            <div class="course-section mb-3" id="${unidadId}">
                <div class="course-section-header" onclick="toggleSection('${unidadId}')">
                    <span>
                        <i class="fas fa-chevron-down me-2"></i>
                        <input type="text" class="form-control-plaintext" name="unidades[${unidadCounter-1}][titulo]" value="Unidad ${unidadCounter}" style="display: inline-block; width: auto; font-weight: bold;">
                    </span>
                    <div>
                        <button type="button" class="btn btn-sm text-orange" onclick="addSection('${unidadId}', 'recursos', event)">
                            <i class="fas fa-plus-circle me-1"></i> Recurso
                        </button>
                        <button type="button" class="btn btn-sm text-primary" onclick="addSection('${unidadId}', 'actividades', event)">
                            <i class="fas fa-plus-circle me-1"></i> Actividad
                        </button>
                    </div>
                </div>
                <div class="course-section-content" id="${unidadId}-content">
                    <!-- Descripción de la unidad -->
                    <div class="mb-3">
                        <input type="text" class="form-control" name="unidades[${unidadCounter-1}][descripcion]" placeholder="Descripción de la unidad">
                    </div>

                    <!-- Recursos Didácticos -->
                    <div class="mb-3">
                        <h6 class="section-title">RECURSOS DIDÁCTICOS</h6>
                        <div id="${unidadId}-recursos">
                            <!-- Los recursos se añadirán dinámicamente -->
                        </div>
                    </div>

                    <!-- Actividades -->
                    <div class="mb-3">
                        <h6 class="section-title">ACTIVIDADES</h6>
                        <div id="${unidadId}-actividades">
                            <!-- Las actividades se añadirán dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('unidadesContainer').insertAdjacentHTML('beforeend', unidadHtml);
    }

    // Función para añadir un recurso o actividad a una unidad
    function addSection(unidadId, tipo, event) {
        event.stopPropagation(); // Evitar que se cierre la sección

        const container = document.getElementById(`${unidadId}-${tipo}`);
        const counterKey = unidadId;
        const unidadIndex = parseInt(unidadId.split('-')[1]) - 1;

        let elementCount = 0;
        if (tipo === 'recursos') {
            elementCount = recursosCounter[counterKey]++;
        } else if (tipo === 'actividades') {
            elementCount = actividadesCounter[counterKey]++;
        }

        const newElement = document.createElement('div');

        if (tipo === 'recursos') {
            newElement.className = 'resource-item d-flex justify-content-between align-items-center flex-wrap';
            newElement.innerHTML = `
                <div>
                    <span class="circle-indicator gray-indicator"></span>
                    <input type="text" class="form-control-plaintext" name="unidades[${unidadIndex}][recursos][${elementCount}][titulo]" value="Nuevo recurso" style="display: inline-block; width: auto;">
                    <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                        <i class="fas fa-edit"></i> Contenido
                    </button>
                </div>
                <div>
                    <select class="form-select form-select-sm" name="unidades[${unidadIndex}][recursos][${elementCount}][tipo]" style="width: auto; display: inline-block;">
                        <option value="archivo" selected>Archivo</option>
                        <option value="enlace">Enlace</option>
                        <option value="video">Video</option>
                    </select>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="content-details w-100 mt-2" style="display: none;">
                    <textarea class="form-control" name="unidades[${unidadIndex}][recursos][${elementCount}][contenido]" rows="3" placeholder="Descripción del recurso, URL del enlace o video, etc."></textarea>
                    <div class="mt-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][recursos][${elementCount}][obligatorio]" id="recurso-${unidadId}-${elementCount}-obligatorio" value="1">
                            <label class="form-check-label" for="recurso-${unidadId}-${elementCount}-obligatorio">Recurso obligatorio</label>
                        </div>
                        <label class="form-label mb-1">Adjuntos/enlaces:</label>
                        <input type="text" class="form-control mb-2" name="unidades[${unidadIndex}][recursos][${elementCount}][url]" placeholder="URL o nombre del archivo">
                    </div>
                </div>
            `;
        } else if (tipo === 'actividades') {
            newElement.className = 'activity-item d-flex justify-content-between align-items-center flex-wrap';
            newElement.innerHTML = `
                <div>
                    <span class="circle-indicator gray-indicator"></span>
                    <input type="text" class="form-control-plaintext" name="unidades[${unidadIndex}][actividades][${elementCount}][titulo]" value="Nueva actividad" style="display: inline-block; width: auto;">
                    <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                        <i class="fas fa-edit"></i> Contenido
                    </button>
                </div>
                <div>
                    <select class="form-select form-select-sm" name="unidades[${unidadIndex}][actividades][${elementCount}][tipo]" style="width: auto; display: inline-block;">
                        <option value="tarea" selected>Tarea</option>
                        <option value="quiz">Quiz</option>
                        <option value="foro">Foro</option>
                    </select>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="content-details w-100 mt-2" style="display: none;">
                    <textarea class="form-control mb-2" name="unidades[${unidadIndex}][actividades][${elementCount}][contenido]" rows="3" placeholder="Instrucciones para la actividad..."></textarea>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Puntuación:</label>
                            <input type="number" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][puntuacion]" min="0" value="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha límite:</label>
                            <input type="date" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][fecha_limite]">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tiempo (min):</label>
                            <input type="number" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][tiempo]" min="0">
                        </div>
                    </div>
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][actividades][${elementCount}][obligatorio]" id="actividad-${unidadId}-${elementCount}-obligatorio" value="1" checked>
                            <label class="form-check-label" for="actividad-${unidadId}-${elementCount}-obligatorio">Actividad obligatoria</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuizQuestion(this, ${unidadIndex}, ${elementCount})">
                            <i class="fas fa-plus"></i> Añadir pregunta
                        </button>
                    </div>
                    <div class="quiz-questions mt-3"></div>
                </div>
            `;
        }

        container.appendChild(newElement);
    }

    // Función para eliminar un elemento
    function removeElement(button) {
        const itemElement = button.closest('.resource-item, .activity-item');
        if (itemElement) {
            itemElement.remove();
        }
    }

    // Función para mostrar la pestaña de crear curso
    function showCreateCourseTab() {
        const tabElement = document.querySelector('a[href="#crearCurso"]');
        const bsTab = new bootstrap.Tab(tabElement);
        bsTab.show();
    }

    // Función para cancelar el formulario
    function cancelForm() {
        document.getElementById('courseForm').reset();
        const tabElement = document.querySelector('a[href="#misCursos"]');
        const bsTab = new bootstrap.Tab(tabElement);
        bsTab.show();
    }

    // Función para editar un curso existente
    function editCourse(courseId) {
        // Aquí deberías cargar los datos del curso con AJAX
        // Por simplicidad, redirigiremos a una página de edición
        window.location.href = `edit_course.php?id=${courseId}`;
    }

    // Inicializar las pestañas de Bootstrap cuando se carga la página
    document.addEventListener('DOMContentLoaded', function() {
        const tabElements = document.querySelectorAll('#courseManagementTabs a');
        tabElements.forEach(function(tabEl) {
            tabEl.addEventListener('click', function(e) {
                e.preventDefault();
                const bsTab = new bootstrap.Tab(tabEl);
                bsTab.show();
            });
        });
    });

    // Función para mostrar/ocultar el panel de contenido
    function toggleContent(button) {
        const itemElement = button.closest('.resource-item, .activity-item');
        const contentPanel = itemElement.querySelector('.content-details');

        if (contentPanel) {
            if (contentPanel.style.display === 'none' || contentPanel.style.display === '') {
                contentPanel.style.display = 'block';
            } else {
                contentPanel.style.display = 'none';
            }
        }

        // Mostrar u ocultar contenedores según el tipo seleccionado
        const tipoSelect = itemElement.querySelector('select[name$="[tipo]"]');
        const tipo = tipoSelect.value;

        const foroContainer = contentPanel.querySelector('#foro-container');
        const quizContainer = contentPanel.querySelector('#quiz-container');
        const archivoContainer = contentPanel.querySelector('#archivo-container');

        foroContainer.style.display = (tipo === 'foro') ? 'block' : 'none';
        quizContainer.style.display = (tipo === 'cuestionario') ? 'block' : 'none';
        archivoContainer.style.display = (tipo === 'archivo') ? 'block' : 'none';
    }

    // Función para añadir una pregunta al cuestionario
    function addQuizQuestion(button, unidadIndex, actividadIndex) {
        const questionContainer = button.closest('.content-details').querySelector('.quiz-questions');
        const questionCount = questionContainer.children.length;

        const questionElement = document.createElement('div');
        questionElement.className = 'quiz-question card mb-3';
        questionElement.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <span>Pregunta ${questionCount + 1}</span>
                <button type="button" class="btn btn-sm text-danger" onclick="removeQuizQuestion(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][texto]" rows="2" placeholder="Texto de la pregunta..."></textarea>
                </div>
                <div class="mb-2">
                    <select class="form-select mb-2" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][tipo]" onchange="changePreguntaTipo(this, ${unidadIndex}, ${actividadIndex}, ${questionCount})">
                        <option value="opcion_multiple">Opción múltiple</option>
                        <option value="verdadero_falso">Verdadero/Falso</option>
                        <option value="texto_libre">Texto libre</option>
                    </select>
                </div>
                <div class="opciones-container">
                    <div class="opcion-item d-flex mb-2">
                        <div class="form-check me-2">
                            <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][respuesta_correcta]" value="0" checked>
                        </div>
                        <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][opciones][0]" placeholder="Opción 1">
                    </div>
                    <div class="opcion-item d-flex mb-2">
                        <div class="form-check me-2">
                            <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][respuesta_correcta]" value="1">
                        </div>
                        <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][opciones][1]" placeholder="Opción 2">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addQuizOption(this, ${unidadIndex}, ${actividadIndex}, ${questionCount})">
                        <i class="fas fa-plus"></i> Añadir opción
                    </button>
                </div>
            </div>
        `;

        questionContainer.appendChild(questionElement);
    }

    // Función para eliminar una pregunta
    function removeQuizQuestion(button) {
        const questionElement = button.closest('.quiz-question');
        if (questionElement) {
            questionElement.remove();
        }
    }

    // Función para añadir una opción a una pregunta
    function addQuizOption(button, unidadIndex, actividadIndex, questionIndex) {
        const opcionesContainer = button.closest('.opciones-container');
        const opcionCount = opcionesContainer.querySelectorAll('.opcion-item').length;

        const opcionElement = document.createElement('div');
        opcionElement.className = 'opcion-item d-flex mb-2';
        opcionElement.innerHTML = `
            <div class="form-check me-2">
                <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][respuesta_correcta]" value="${opcionCount}">
            </div>
            <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][opciones][${opcionCount}]" placeholder="Opción ${opcionCount + 1}">
        `;

        // Insertar antes del botón
        button.before(opcionElement);
    }

    // Función para cambiar el tipo de pregunta
    function changePreguntaTipo(select, unidadIndex, actividadIndex, questionIndex) {
        const tipoSeleccionado = select.value;
        const opcionesContainer = select.closest('.card-body').querySelector('.opciones-container');

        // Limpiar el contenedor de opciones
        opcionesContainer.innerHTML = '';

        if (tipoSeleccionado === 'opcion_multiple') {
            opcionesContainer.innerHTML = `
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][respuesta_correcta]" value="0" checked>
                    </div>
                    <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][opciones][0]" placeholder="Opción 1">
                </div>
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][respuesta_correcta]" value="1">
                    </div>
                    <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][opciones][1]" placeholder="Opción 2">
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addQuizOption(this, ${unidadIndex}, ${actividadIndex}, ${questionIndex})">
                    <i class="fas fa-plus"></i> Añadir opción
                </button>
            `;
        } else if (tipoSeleccionado === 'verdadero_falso') {
            opcionesContainer.innerHTML = `
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][respuesta_correcta]" value="verdadero" checked>
                    </div>
                    <input type="text" class="form-control" value="Verdadero" disabled>
                </div>
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][respuesta_correcta]" value="falso">
                    </div>
                    <input type="text" class="form-control" value="Falso" disabled>
                </div>
            `;
        } else if (tipoSeleccionado === 'texto_libre') {
            opcionesContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Respuesta correcta:</label>
                    <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionIndex}][respuesta_correcta]" rows="2" placeholder="Texto de la respuesta correcta (para referencia)"></textarea>
                </div>
            `;
        }
    }

    // Manejar el envío del formulario
    document.getElementById('courseForm').addEventListener('submit', function(event) {
        const title = document.getElementById('courseTitle').value;
        const description = document.getElementById('courseDescription').value;
        const image = document.getElementById('courseImage').files[0];

        if (!title || !description) {
            event.preventDefault();
            showAlert('Por favor, completa todos los campos obligatorios.');
            return;
        }

        if (image && !image.type.startsWith('image/')) {
            event.preventDefault();
            showAlert('El archivo seleccionado no es una imagen válida.');
            return;
        }

        // Simular envío exitoso
        setTimeout(() => {
            showAlert('El curso se ha guardado correctamente.', 'success');
        }, 1000);
    });
