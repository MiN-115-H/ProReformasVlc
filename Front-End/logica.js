const filterButtons = document.querySelectorAll('.filter-btn');
const proyectos = document.querySelectorAll('.proyecto-card');

filterButtons.forEach(button => {
  button.addEventListener('click', () => {
    // Quitar clase active de todos
    filterButtons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    const category = button.dataset.category;

    proyectos.forEach(proyecto => {
      if (category === 'todos' || proyecto.dataset.category === category) {
        proyecto.style.display = 'flex';
      } else {
        proyecto.style.display = 'none';
      }
    });
  });
});

// Array de proyectos adicionales
const proyectosExtra = [
  { categoria: "cocina", titulo: "Cocina Clásica", descripcion: "Reforma de cocina con estilo clásico y acabados en madera." },
  { categoria: "baños", titulo: "Baño Zen", descripcion: "Diseño relajante con materiales naturales y luz suave." },
  { categoria: "integral", titulo: "Reforma Integral Moderna", descripcion: "Transformación completa con distribución abierta." },
  { categoria: "otros", titulo: "Local Comercial", descripcion: "Pequeña reforma de local para optimizar espacio y funcionalidad." },
  { categoria: "cocina", titulo: "Cocina Compacta", descripcion: "Optimización de espacios pequeños con alto diseño." },
  { categoria: "baños", titulo: "Baño Vintage", descripcion: "Estilo retro con detalles de época." },
  { categoria: "integral", titulo: "Reforma Integral Industrial", descripcion: "Espacios amplios y modernos con materiales industriales." },
  { categoria: "otros", titulo: "Oficina Pequeña", descripcion: "Reforma funcional de oficina pequeña." }
];

let proyectosMostrados = 4; // cantidad inicial mostrada

const contenedor = document.querySelector(".contenedorProyectos");
const btnCargarMas = document.getElementById("cargarMasBtn");

// Función para crear y agregar cards
function crearCard(proyecto) {
  const card = document.createElement("div");
  card.classList.add("proyecto-card");
  card.setAttribute("data-category", proyecto.categoria);

  card.innerHTML = `
    <div class="proyecto-img"></div>
    <div class="proyecto-info">
      <h3>${proyecto.titulo}</h3>
      <p>${proyecto.descripcion}</p>
    </div>
  `;

  contenedor.appendChild(card);
}

// Evento del botón
btnCargarMas.addEventListener("click", () => {
  const siguientes = proyectosExtra.splice(0, 4); // toma los siguientes 4
  siguientes.forEach(p => crearCard(p));

  // Si no quedan más proyectos, ocultamos el botón
  if (proyectosExtra.length === 0) {
    btnCargarMas.style.display = "none";
  }
});

// ==========================================
// LÓGICA CALCULADORA DE PRESUPUESTOS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  const lineaForm = document.getElementById('lineaForm');
  if (!lineaForm) return; // Si no estamos en la página de presupuesto, salir

  let presupuestoLineas = [];
  const IVA_RATE = 0.21;

  // Mock Base de Datos de Conceptos (Tabla: conf_concepto_presupuesto)
  const conceptosDB = [
    { id: 1, descripcion: "Derribo de tabiques", precio_base: 15.50, unidad_medida: "m2" },
    { id: 2, descripcion: "Pintura plástica lisa", precio_base: 8.00, unidad_medida: "m2" },
    { id: 3, descripcion: "Instalación de punto de luz", precio_base: 45.00, unidad_medida: "ud" },
    { id: 4, descripcion: "Alicatado de baño", precio_base: 35.00, unidad_medida: "m2" },
    { id: 5, descripcion: "Suelo laminado AC5", precio_base: 22.00, unidad_medida: "m2" },
    { id: 6, descripcion: "Mano de obra oficial", precio_base: 25.00, unidad_medida: "hx" },
    { id: 7, descripcion: "Partida alzada de fontanería", precio_base: 300.00, unidad_medida: "pa" }
  ];

  // DOM Elements
  const ticketFecha = document.getElementById('ticketFecha');
  const ticketClientInfo = document.getElementById('ticketClientInfo');
  
  const inputNombre = document.getElementById('cliente_nombre');
  const inputTelefono = document.getElementById('cliente_telefono');
  const inputDireccion = document.getElementById('direccion');

  const ticketLines = document.getElementById('ticketLines');
  
  const resumenSubtotal = document.getElementById('resumenSubtotal');
  const resumenIVA = document.getElementById('resumenIVA');
  const resumenTotal = document.getElementById('resumenTotal');

  // Fecha actual
  const today = new Date();
  ticketFecha.textContent = today.toLocaleDateString();

  // Actualizar info de cliente en el ticket en vivo
  function updateClientInfo() {
    const nombre = inputNombre.value || 'Cliente sin nombre';
    const tel = inputTelefono.value ? ` - ${inputTelefono.value}` : '';
    const dir = inputDireccion.value ? `<br>${inputDireccion.value}` : '';
    ticketClientInfo.innerHTML = `<strong>${nombre}</strong>${tel}${dir}`;
  }

  inputNombre.addEventListener('input', updateClientInfo);
  inputTelefono.addEventListener('input', updateClientInfo);
  inputDireccion.addEventListener('input', updateClientInfo);

  const selectConcepto = document.getElementById('descripcionConcepto');
  const selectUnidad = document.getElementById('unidadMedida');
  const inputPrecio = document.getElementById('precioUnitario');
  const inputCantidad = document.getElementById('cantidad');

  // Llenar el select de conceptos
  conceptosDB.forEach(concepto => {
    const opt = document.createElement('option');
    opt.value = concepto.id;
    opt.textContent = concepto.descripcion;
    selectConcepto.appendChild(opt);
  });

  // Al seleccionar un concepto, rellenar unidad y precio base
  selectConcepto.addEventListener('change', (e) => {
    const selectedId = parseInt(e.target.value);
    const concepto = conceptosDB.find(c => c.id === selectedId);
    
    if (concepto) {
      selectUnidad.value = concepto.unidad_medida;
      inputPrecio.value = concepto.precio_base.toFixed(2);
      inputCantidad.focus(); // mover el foco a la cantidad para agilizar
    }
  });

  // Manejar el submit del formulario de conceptos
  lineaForm.addEventListener('submit', (e) => {
    e.preventDefault();

    if(selectConcepto.value === "") return; // Asegurar selección válida del desplegable

    const desc = selectConcepto.options[selectConcepto.selectedIndex].text;
    const unid = selectUnidad.value;
    const cant = parseFloat(inputCantidad.value);
    const prec = parseFloat(inputPrecio.value);

    // Reemplaza la coma por punto en precio por si la validación lo permite
    if (desc && cant > 0 && prec >= 0) {
      const newLine = {
        id: Date.now(),
        descripcion: desc,
        unidad: unid,
        cantidad: cant,
        precio: prec,
        subtotal: cant * prec
      };

      presupuestoLineas.push(newLine);
      renderLines();
      
      // Limpiar inputs a su estado inicial
      selectConcepto.value = '';
      selectUnidad.value = 'm2'; // valor por defecto
      inputCantidad.value = '';
      inputPrecio.value = '';
      selectConcepto.focus();
    }
  });

  // Renderizar las líneas en el panel derecho
  function renderLines() {
    if (presupuestoLineas.length === 0) {
      ticketLines.innerHTML = `
        <div class="empty-state">
          <p>Aún no has añadido conceptos a tu presupuesto.</p>
        </div>`;
      resumenSubtotal.textContent = "0.00 €";
      resumenIVA.textContent = "0.00 €";
      resumenTotal.textContent = "0.00 €";
      return;
    }

    ticketLines.innerHTML = '';
    let totalSubtotal = 0;

    presupuestoLineas.forEach((line) => {
      totalSubtotal += line.subtotal;
      
      const div = document.createElement('div');
      div.className = 'ticket-line';
      div.innerHTML = `
        <div class="line-desc">
          ${line.descripcion}
          <small>${line.cantidad} ${line.unidad} x ${line.precio.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2})} €</small>
        </div>
        <div class="line-qty">${line.cantidad} ${line.unidad}</div>
        <div class="line-price">${line.subtotal.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2})} €</div>
        <div class="line-delete">
          <button class="btn-delete" onclick="eliminarLinea(${line.id})" title="Eliminar concepto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
          </button>
        </div>
      `;
      ticketLines.appendChild(div);
    });

    // Cálculos Finales
    const iva = totalSubtotal * IVA_RATE;
    const granTotal = totalSubtotal + iva;

    // Actualizar labels con formato EUR correct
    resumenSubtotal.textContent = totalSubtotal.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + " €";
    resumenIVA.textContent = iva.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + " €";
    resumenTotal.textContent = granTotal.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + " €";
  }

  // Función global para poder ser llamada desde los elementos inyectados
  window.eliminarLinea = function(id) {
    // Anima el elemento antes de eliminarlo
    const btn = document.querySelector(`button[onclick="eliminarLinea(${id})"]`);
    if(btn) {
      const lineDiv = btn.closest('.ticket-line');
      lineDiv.style.opacity = '0';
      lineDiv.style.transform = 'translateY(-10px)';
      lineDiv.style.transition = 'all 0.3s ease';
      
      setTimeout(() => {
        presupuestoLineas = presupuestoLineas.filter(l => l.id !== id);
        renderLines();
      }, 300);
    } else {
      presupuestoLineas = presupuestoLineas.filter(l => l.id !== id);
      renderLines();
    }
  };
});