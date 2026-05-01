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
  let datosPersonalesConfirmados = false;
  const IVA_RATE = 0.21;

  // Precios medios orientativos para estimación rápida (no precio final).
  const conceptosPorCategoria = {
    cocina: [
      { id: 1, descripcion: "Poner suelos", precio_base: 39.50 },
      { id: 2, descripcion: "Alicatado", precio_base: 44.00 }
    ],
    bano: [
      { id: 3, descripcion: "Cambiar bañera", precio_base: 890.00 },
      { id: 4, descripcion: "Alicatado", precio_base: 48.00 },
      { id: 5, descripcion: "Poner suelos", precio_base: 41.00 }
    ]
  };

  // DOM Elements
  const ticketFecha = document.getElementById('ticketFecha');
  
  const inputNombre = document.getElementById('cliente_nombre');
  const inputTelefono = document.getElementById('cliente_telefono');
  const inputEmail = document.getElementById('cliente_email');
  const inputDireccion = document.getElementById('direccion');
  const inputCiudad = document.getElementById('ciudad');
  const btnConfirmarDatos = document.getElementById('btnConfirmarDatos');
  const estadoConfirmacionDatos = document.getElementById('estadoConfirmacionDatos');
  const btnImprimir = document.getElementById('btnGenerarPDF');

  const ticketLines = document.getElementById('ticketLines');
  
  const resumenSubtotal = document.getElementById('resumenSubtotal');
  const resumenIVA = document.getElementById('resumenIVA');
  const resumenTotal = document.getElementById('resumenTotal');

  // Fecha actual
  const today = new Date();
  ticketFecha.textContent = today.toLocaleDateString();

  function marcarDatosSinConfirmar() {
    datosPersonalesConfirmados = false;
    estadoConfirmacionDatos.textContent = 'Datos pendientes de confirmar';
    estadoConfirmacionDatos.classList.remove('text-green-600');
    estadoConfirmacionDatos.classList.add('text-gray-600');
  }

  [inputNombre, inputTelefono, inputEmail, inputDireccion, inputCiudad].forEach((input) => {
    input.addEventListener('input', () => {
      marcarDatosSinConfirmar();
    });
  });

  btnConfirmarDatos.addEventListener('click', () => {
    if (!inputNombre.value.trim() || !inputTelefono.value.trim() || !inputEmail.value.trim()) {
      alert('Para confirmar, rellena al menos nombre, teléfono y correo electrónico.');
      datosPersonalesConfirmados = false;
      marcarDatosSinConfirmar();
      return;
    }

    datosPersonalesConfirmados = true;
    estadoConfirmacionDatos.textContent = 'Datos personales confirmados';
    estadoConfirmacionDatos.classList.remove('text-gray-600');
    estadoConfirmacionDatos.classList.add('text-green-600');
  });

  const selectCategoria = document.getElementById('categoriaConcepto');
  const selectConcepto = document.getElementById('descripcionConcepto');
  const inputCantidad = document.getElementById('cantidad');

  function cargarTrabajosPorCategoria(categoria) {
    selectConcepto.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.disabled = true;
    placeholder.selected = true;
    placeholder.textContent = categoria ? 'Selecciona un trabajo...' : 'Primero elige una categoría...';
    selectConcepto.appendChild(placeholder);

    if (!categoria || !conceptosPorCategoria[categoria]) return;

    conceptosPorCategoria[categoria].forEach((concepto) => {
      const opt = document.createElement('option');
      opt.value = concepto.id;
      opt.textContent = concepto.descripcion;
      selectConcepto.appendChild(opt);
    });
  }

  selectCategoria.addEventListener('change', (e) => {
    cargarTrabajosPorCategoria(e.target.value);
    inputCantidad.focus();
  });

  function buscarConcepto(categoria, idConcepto) {
    if (!categoria || !conceptosPorCategoria[categoria]) return null;
    return conceptosPorCategoria[categoria].find((c) => c.id === idConcepto) || null;
  }

  // Manejar el submit del formulario de conceptos
  lineaForm.addEventListener('submit', (e) => {
    e.preventDefault();

    if (selectCategoria.value === '' || selectConcepto.value === '') return;

    const categoria = selectCategoria.value;
    const conceptoSeleccionado = buscarConcepto(categoria, parseInt(selectConcepto.value, 10));
    if (!conceptoSeleccionado) return;

    const desc = selectConcepto.options[selectConcepto.selectedIndex].text;
    const cant = parseFloat(inputCantidad.value);
    const prec = conceptoSeleccionado.precio_base;

    if (desc && cant > 0 && prec >= 0) {
      const newLine = {
        id: Date.now(),
        descripcion: desc,
        cantidad: cant,
        precio: prec,
        subtotal: cant * prec
      };

      presupuestoLineas.push(newLine);
      renderLines();
      
      // Limpiar inputs a su estado inicial
      selectCategoria.value = '';
      selectConcepto.value = '';
      cargarTrabajosPorCategoria('');
      inputCantidad.value = '';
      selectCategoria.focus();
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
          <small>Cantidad: ${line.cantidad}</small>
        </div>
        <div class="line-qty">x${line.cantidad}</div>
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

  btnImprimir.addEventListener('click', () => {
    if (!datosPersonalesConfirmados) {
      alert('Debes rellenar y confirmar tus datos personales antes de imprimir el presupuesto.');
      return;
    }

    window.print();
  });

  cargarTrabajosPorCategoria('');
});