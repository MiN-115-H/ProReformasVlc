<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import PageHero from '../components/PageHero.vue';

const IVA_RATE = 0.21;

const clienteNombre = ref('');
const clienteTelefono = ref('');
const clienteEmail = ref('');
const clienteDireccion = ref('');
const clienteCiudad = ref('');
const observaciones = ref('');

const datosPersonalesConfirmados = ref(false);
const seccionDatosExpandida = ref(true);
const errorConfirmacionDatos = ref('');
const errorConceptos = ref('');
const errorVerPresupuesto = ref('');
const mostrarModalConfirmacion = ref(false);
const guardandoPresupuesto = ref(false);

const categoriaSeleccionada = ref('');
const categoriaBloqueadaId = ref('');
const conceptoSeleccionadoId = ref('');
const cantidad = ref(1);
const metrosCuadrados = ref('');
const extrasSeleccionados = ref([]);  // IDs de sugerencias marcadas
const otrosDescripcion = ref('');     // Texto libre para OTRAS

const lineas = ref([]);
const logoUrl = '/img/logo.jpg';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const tipos = ref([]);
const conceptosTodos = ref([]);
const cargando = ref(true);

onMounted(async () => {
  try {
    const res = await fetch('/api/conceptos');
    const data = await res.json();
    tipos.value = data.tipos;
    conceptosTodos.value = data.conceptos;
  } finally {
    cargando.value = false;
  }
});

const ticketFecha = new Date().toLocaleDateString('es-ES');

const datosPersonalesCompletos = computed(() =>
  [
    clienteNombre.value,
    clienteTelefono.value,
    clienteEmail.value,
    clienteDireccion.value,
    clienteCiudad.value,
  ].every((value) => value.trim().length > 0),
);

const telefonoValido = computed(() => /^\+?[0-9\s-]{8,20}$/.test(clienteTelefono.value.trim()));
const emailValido = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(clienteEmail.value.trim()));
const nombreValido = computed(() => /^[\p{L}\s\-']{3,150}$/u.test(clienteNombre.value.trim()));
const ciudadValida = computed(() => /^[\p{L}\s\-']{2,120}$/u.test(clienteCiudad.value.trim()));
const direccionValida = computed(() => clienteDireccion.value.trim().length >= 5);

const datosValidadosParaConceptos = computed(() =>
  datosPersonalesConfirmados.value
  && datosPersonalesCompletos.value
  && telefonoValido.value
  && emailValido.value
  && nombreValido.value
  && ciudadValida.value
  && direccionValida.value,
);

const nombreCategoriaPorId = (id) => {
  const tipo = tipos.value.find((item) => String(item.id) === String(id));
  if (!tipo) return 'Reforma';
  return tipo.nombre === 'Bano' ? 'Baño' : tipo.nombre;
};

const tituloPresupuesto = computed(() => {
  const categoriaId = categoriaBloqueadaId.value || categoriaSeleccionada.value;
  const nombreCategoria = categoriaId ? nombreCategoriaPorId(categoriaId) : 'Reforma';
  const m2 = Number(metrosCuadrados.value);
  if (m2 > 0) {
    return `Reforma de ${nombreCategoria} de ${m2} m2`;
  }
  return `Reforma de ${nombreCategoria}`;
});

const conceptosDisponibles = computed(() => {
  if (!categoriaSeleccionada.value) return [];
  return conceptosTodos.value.filter(
    (c) => String(c.tipo_presupuesto_id) === String(categoriaSeleccionada.value),
  );
});

const conceptoSeleccionado = computed(() =>
  conceptosTodos.value.find((c) => String(c.id) === String(conceptoSeleccionadoId.value)) ?? null,
);

const esOtras = computed(() => conceptoSeleccionado.value?.descripcion === 'OTRAS');

const sugerenciasDisponibles = computed(() => {
  const ids = conceptoSeleccionado.value?.sugerencias ?? [];
  if (!ids.length) return [];
  return conceptosTodos.value.filter((c) => ids.includes(c.id));
});

const subtotal = computed(() => lineas.value.reduce((acc, line) => acc + line.subtotal, 0));
const iva = computed(() => subtotal.value * IVA_RATE);
const total = computed(() => subtotal.value + iva.value);

const estadoConfirmacionTexto = computed(() =>
  datosPersonalesConfirmados.value ? 'Datos personales confirmados' : 'Datos pendientes de confirmar',
);

watch([clienteNombre, clienteTelefono, clienteEmail, clienteDireccion, clienteCiudad], () => {
  datosPersonalesConfirmados.value = false;
  errorConfirmacionDatos.value = '';
  if (!datosPersonalesCompletos.value) {
    errorConceptos.value = 'Completa y confirma tus datos personales para habilitar la sección de conceptos.';
  }
});

watch(categoriaSeleccionada, () => {
  conceptoSeleccionadoId.value = '';
});

watch(conceptoSeleccionadoId, () => {
  extrasSeleccionados.value = [];
  otrosDescripcion.value = '';
});

const confirmarDatos = () => {
  if (!datosPersonalesCompletos.value) {
    errorConfirmacionDatos.value = 'Debes completar nombre, teléfono, email, dirección y ciudad.';
    return;
  }

  if (!nombreValido.value) {
    errorConfirmacionDatos.value = 'Introduce un nombre válido (solo letras y espacios).';
    return;
  }

  if (!telefonoValido.value) {
    errorConfirmacionDatos.value = 'Introduce un teléfono válido (8-20 dígitos, permite +, espacios y guiones).';
    return;
  }

  if (!emailValido.value) {
    errorConfirmacionDatos.value = 'Introduce un correo electrónico válido.';
    return;
  }

  if (!direccionValida.value) {
    errorConfirmacionDatos.value = 'La dirección debe tener al menos 5 caracteres.';
    return;
  }

  if (!ciudadValida.value) {
    errorConfirmacionDatos.value = 'Introduce una ciudad válida (solo letras y espacios).';
    return;
  }

  errorConfirmacionDatos.value = '';
  errorConceptos.value = '';
  datosPersonalesConfirmados.value = true;
  seccionDatosExpandida.value = false;
};

const anadirConcepto = () => {
  errorConceptos.value = '';

  if (!datosValidadosParaConceptos.value) {
    errorConceptos.value = 'Primero completa y confirma tus datos personales.';
    return;
  }

  if (!categoriaSeleccionada.value || !conceptoSeleccionadoId.value) {
    errorConceptos.value = 'Selecciona categoría y trabajo específico.';
    return;
  }

  const concepto = conceptoSeleccionado.value;
  if (!concepto || Number(cantidad.value) <= 0) {
    errorConceptos.value = 'Indica una cantidad válida para el trabajo.';
    return;
  }

  const m2 = Number(metrosCuadrados.value);
  if (!Number.isFinite(m2) || m2 <= 0) {
    errorConceptos.value = 'Indica los metros cuadrados del habitáculo a reformar.';
    return;
  }

  if (
    categoriaBloqueadaId.value
    && String(categoriaSeleccionada.value) !== String(categoriaBloqueadaId.value)
  ) {
    errorConceptos.value = `El presupuesto actual es de ${nombreCategoriaPorId(categoriaBloqueadaId.value)}. No puedes mezclar categorías.`;
    return;
  }

  // Concepto principal (o descripcion libre para OTRAS)
  const descripcionPrincipal = esOtras.value
    ? (otrosDescripcion.value.trim() || 'OTRAS')
    : concepto.descripcion;

  const precioBase = Number(concepto.precio_base);

  lineas.value.push({
    id: Date.now(),
    descripcion: descripcionPrincipal,
    cantidad: Number(cantidad.value),
    precio: precioBase,
    subtotal: Number(cantidad.value) * precioBase,
    categoriaId: String(categoriaSeleccionada.value),
    conceptoId: concepto.id,
    metrosCuadrados: m2,
  });

  // Extras seleccionados (siempre cantidad 1)
  for (const extraId of extrasSeleccionados.value) {
    const extra = conceptosTodos.value.find((c) => c.id === extraId);
    if (!extra) continue;
    if (String(extra.tipo_presupuesto_id) !== String(categoriaSeleccionada.value)) continue;
    const extraPrecio = Number(extra.precio_base);
    lineas.value.push({
      id: Date.now() + extraId,
      descripcion: extra.descripcion,
      cantidad: 1,
      precio: extraPrecio,
      subtotal: extraPrecio,
      categoriaId: String(categoriaSeleccionada.value),
      conceptoId: extra.id,
      metrosCuadrados: m2,
    });
  }

  if (!categoriaBloqueadaId.value) {
    categoriaBloqueadaId.value = String(categoriaSeleccionada.value);
  }

  conceptoSeleccionadoId.value = '';
  cantidad.value = 1;
  extrasSeleccionados.value = [];
  otrosDescripcion.value = '';
};

const eliminarLinea = (id) => {
  lineas.value = lineas.value.filter((line) => line.id !== id);
  if (lineas.value.length === 0) {
    categoriaBloqueadaId.value = '';
  }
};

const verPresupuesto = () => {
  errorVerPresupuesto.value = '';
  if (!datosValidadosParaConceptos.value) {
    errorVerPresupuesto.value = 'Completa y confirma tus datos personales para continuar.';
    return;
  }
  if (lineas.value.length === 0) {
    errorVerPresupuesto.value = 'Añade al menos un concepto para ver el presupuesto.';
    return;
  }

  mostrarModalConfirmacion.value = true;
};

const confirmarYGuardarPresupuesto = async () => {
  if (guardandoPresupuesto.value) return;

  errorVerPresupuesto.value = '';
  guardandoPresupuesto.value = true;

  const tipoPresupuestoId = Number(categoriaBloqueadaId.value || categoriaSeleccionada.value);

  try {
    const response = await fetch('/api/presupuestos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        titulo: tituloPresupuesto.value,
        metros_cuadrados: Number(metrosCuadrados.value),
        tipo_presupuesto_id: tipoPresupuestoId,
        cliente_nombre: clienteNombre.value.trim(),
        cliente_telefono: clienteTelefono.value.trim(),
        cliente_email: clienteEmail.value.trim(),
        direccion: clienteDireccion.value.trim(),
        ciudad: clienteCiudad.value.trim(),
        observaciones: observaciones.value.trim() || null,
        fecha_presupuesto: new Date().toISOString().slice(0, 10),
        lineas: lineas.value.map((linea) => ({
          descripcion: linea.descripcion,
          cantidad: linea.cantidad,
          precio: linea.precio,
          subtotal: linea.subtotal,
          categoria_id: Number(linea.categoriaId),
        })),
        subtotal: Number(subtotal.value.toFixed(2)),
        iva: Number(iva.value.toFixed(2)),
        total: Number(total.value.toFixed(2)),
      }),
    });

    if (!response.ok) {
      const payload = await response.json().catch(() => ({}));
      throw new Error(payload?.message || 'No se pudo guardar el presupuesto.');
    }

    mostrarModalConfirmacion.value = false;
    window.print();
  } catch (error) {
    errorVerPresupuesto.value = error.message || 'No se pudo guardar el presupuesto.';
  } finally {
    guardandoPresupuesto.value = false;
  }
};

const cerrarModalConfirmacion = () => {
  if (guardandoPresupuesto.value) return;
  mostrarModalConfirmacion.value = false;
};

const formatEUR = (value) =>
  `${value.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} EUR`;
</script>

<template>
  <div>
    <div class="no-print">
      <PageHero
        title="Presupuestos"
        text="Calcula un presupuesto estimado y solicita una propuesta detallada a medida."
        image="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1800&q=80"
      />

      <main class="max-w-7xl mx-auto px-6 py-24">
        <div class="disclaimer-box mb-8">
          <p class="disclaimer-title">Información orientativa</p>
          <p class="disclaimer-text">
            Este documento tiene carácter meramente informativo y se basa en precios medios de mercado para materiales
            y mano de obra. El importe final puede variar según mediciones definitivas, calidades elegidas y
            condiciones reales de ejecución en obra.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
              <div class="flex items-center justify-between gap-3 mb-6">
                <h2 class="text-2xl font-bold uppercase tracking-widest text-primary">1. Datos del Cliente</h2>
                <button
                  type="button"
                  class="text-sm font-semibold text-primary hover:underline"
                  @click="seccionDatosExpandida = !seccionDatosExpandida"
                >
                  {{ seccionDatosExpandida ? 'Minimizar' : 'Editar datos' }}
                </button>
              </div>

              <form v-show="seccionDatosExpandida" class="space-y-4" @submit.prevent>
                <div>
                  <label class="block text-sm font-semibold mb-2" for="cliente_nombre">Nombre Completo</label>
                  <input
                    id="cliente_nombre"
                    v-model="clienteNombre"
                    type="text"
                    placeholder="Ej. Juan Perez"
                    minlength="3"
                    maxlength="150"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold mb-2" for="cliente_telefono">Teléfono</label>
                    <input
                      id="cliente_telefono"
                      v-model="clienteTelefono"
                      type="tel"
                      placeholder="+34 600..."
                      pattern="^\+?[0-9\s-]{8,20}$"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-semibold mb-2" for="cliente_email">Correo Electrónico</label>
                    <input
                      id="cliente_email"
                      v-model="clienteEmail"
                      type="email"
                      placeholder="correo@ejemplo.com"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                      required
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold mb-2" for="direccion">Dirección del Proyecto</label>
                    <input
                      id="direccion"
                      v-model="clienteDireccion"
                      type="text"
                      placeholder="Calle, numero, piso..."
                      minlength="5"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-semibold mb-2" for="ciudad">Ciudad</label>
                    <input
                      id="ciudad"
                      v-model="clienteCiudad"
                      type="text"
                      placeholder="Ej. Valencia"
                      minlength="2"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2" for="observaciones">Observaciones Generales</label>
                  <textarea
                    id="observaciones"
                    v-model="observaciones"
                    rows="3"
                    placeholder="Detalles extra sobre la reforma..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                  ></textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                  <button
                    type="button"
                    @click="confirmarDatos"
                    class="bg-primary hover:bg-primary/90 text-white py-2 px-5 rounded-lg font-bold uppercase transition-all"
                  >
                    Confirmar Datos Personales
                  </button>
                  <span
                    class="text-xs font-semibold"
                    :class="datosPersonalesConfirmados ? 'text-green-600' : 'text-gray-600'"
                  >
                    {{ estadoConfirmacionTexto }}
                  </span>
                </div>

                <p v-if="errorConfirmacionDatos" class="alerta-validacion">{{ errorConfirmacionDatos }}</p>
              </form>

              <div v-if="!seccionDatosExpandida" class="resumen-datos text-sm text-gray-700">
                <p><strong>Nombre:</strong> {{ clienteNombre || '-' }}</p>
                <p><strong>Teléfono:</strong> {{ clienteTelefono || '-' }}</p>
                <p><strong>Email:</strong> {{ clienteEmail || '-' }}</p>
                <p><strong>Dirección:</strong> {{ clienteDireccion || '-' }}</p>
                <p><strong>Ciudad:</strong> {{ clienteCiudad || '-' }}</p>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8" :class="{ 'opacity-60': !datosValidadosParaConceptos }">
              <h2 class="text-2xl font-bold uppercase tracking-widest mb-6 text-primary">2. Añadir Conceptos</h2>
              <p v-if="!datosValidadosParaConceptos" class="alerta-bloqueo mb-4">
                Esta sección está bloqueada. Completa y confirma primero tus datos personales.
              </p>
              <form class="space-y-4" @submit.prevent="anadirConcepto">
                <div>
                  <label class="block text-sm font-semibold mb-2" for="categoria">Categoría de Reforma</label>
                  <select
                    id="categoria"
                    v-model="categoriaSeleccionada"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                    :disabled="!datosValidadosParaConceptos"
                    required
                  >
                    <option value="" disabled>
                      {{ cargando ? 'Cargando categorías...' : 'Selecciona una categoría...' }}
                    </option>
                    <option v-for="tipo in tipos" :key="tipo.id" :value="String(tipo.id)">
                      {{ tipo.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2" for="concepto">Trabajo Específico</label>
                  <select
                    id="concepto"
                    v-model="conceptoSeleccionadoId"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                    :disabled="!datosValidadosParaConceptos || !categoriaSeleccionada"
                    required
                  >
                    <option value="" disabled>
                      {{ categoriaSeleccionada ? 'Selecciona un trabajo...' : 'Primero elige una categoría...' }}
                    </option>
                    <option
                      v-for="concepto in conceptosDisponibles"
                      :key="concepto.id"
                      :value="String(concepto.id)"
                    >
                      {{ concepto.descripcion }}
                    </option>
                  </select>
                </div>

                <!-- Campo libre para OTRAS -->
                <div v-if="esOtras">
                  <label class="block text-sm font-semibold mb-2" for="otros_desc">Describe el servicio que necesitas</label>
                  <input
                    id="otros_desc"
                    v-model="otrosDescripcion"
                    type="text"
                    placeholder="Ej. Cambio de radiadores, reforma integral..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <!-- Sugerencias / Extras -->
                <div v-if="sugerenciasDisponibles.length" class="border border-amber-200 bg-amber-50 rounded-lg p-4">
                  <p class="text-sm font-bold text-amber-800 mb-3 uppercase tracking-wide">
                    ¿También necesitas alguno de estos trabajos relacionados?
                  </p>
                  <div class="space-y-2">
                    <label
                      v-for="extra in sugerenciasDisponibles"
                      :key="extra.id"
                      class="flex items-center gap-3 cursor-pointer group"
                    >
                      <input
                        type="checkbox"
                        :value="extra.id"
                        v-model="extrasSeleccionados"
                        class="w-4 h-4 accent-primary"
                      />
                      <span class="text-sm text-gray-700 group-hover:text-primary transition-colors">
                        {{ extra.descripcion }}
                      </span>
                    </label>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold mb-2" for="cantidad">Cantidad</label>
                    <input
                      id="cantidad"
                      v-model.number="cantidad"
                      type="number"
                      min="1"
                      step="1"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-semibold mb-2" for="metros_cuadrados">Tamaño del habitáculo (m2)</label>
                    <input
                      id="metros_cuadrados"
                      v-model.number="metrosCuadrados"
                      type="number"
                      min="1"
                      step="0.1"
                      placeholder="Ej. 150"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                      :disabled="!datosValidadosParaConceptos"
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  :disabled="!datosValidadosParaConceptos"
                  class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-lg font-bold uppercase transition-all"
                >
                  + Añadir al Presupuesto
                </button>

                <p v-if="errorConceptos" class="alerta-validacion">{{ errorConceptos }}</p>
              </form>
            </div>
          </div>

          <div>
            <div class="bg-white rounded-lg shadow-lg p-8 sticky top-20">
              <h3 class="text-2xl font-bold uppercase tracking-widest mb-2 text-primary">Presupuesto Estimado</h3>
              <p class="text-sm font-semibold text-gray-700 mb-1">{{ tituloPresupuesto }}</p>
              <span class="text-xs text-gray-500">{{ ticketFecha }}</span>

              <div class="space-y-3 my-6">
                <div class="flex justify-between text-sm font-semibold pb-2 border-b">
                  <span>Concepto</span>
                  <span>Subtotal</span>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <div v-if="lineas.length === 0" class="text-center text-gray-500 py-8">
                  <p>Aún no has añadido conceptos a tu presupuesto.</p>
                </div>
                <div
                  v-for="linea in lineas"
                  :key="linea.id"
                  class="grid grid-cols-[1fr_auto_auto_auto] gap-2 items-start border-b border-gray-100 pb-2"
                >
                  <div class="text-sm">
                    <p class="font-semibold text-gray-800">{{ linea.descripcion }}</p>
                    <small class="text-gray-500">Cantidad: {{ linea.cantidad }}</small>
                  </div>
                  <div class="text-xs text-gray-500 self-center">x{{ linea.cantidad }}</div>
                  <div class="text-sm font-semibold self-center">{{ formatEUR(linea.subtotal) }}</div>
                  <button
                    type="button"
                    class="text-red-500 hover:text-red-700 text-sm"
                    @click="eliminarLinea(linea.id)"
                  >
                    X
                  </button>
                </div>
              </div>

              <div class="space-y-3 border-t border-b py-4 mb-6">
                <div class="flex justify-between">
                  <span>Subtotal:</span>
                  <span class="font-semibold">{{ formatEUR(subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                  <span>IVA (21%):</span>
                  <span class="font-semibold">{{ formatEUR(iva) }}</span>
                </div>
              </div>

              <div class="mb-6 p-4 bg-primary text-white rounded-lg text-center">
                <p class="text-sm font-semibold mb-1">TOTAL ESTIMADO</p>
                <p class="text-3xl font-bold">{{ formatEUR(total) }}</p>
              </div>

              <button
                type="button"
                class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-lg font-bold uppercase transition-all"
                @click="verPresupuesto"
              >
                Ver presupuesto
              </button>

              <p v-if="errorVerPresupuesto" class="alerta-validacion mt-3">{{ errorVerPresupuesto }}</p>
            </div>
          </div>
        </div>
      </main>
    </div>

    <div v-if="mostrarModalConfirmacion" class="modal-overlay no-print" @click.self="cerrarModalConfirmacion">
      <div class="modal-card">
        <h4>Confirmar envío de presupuesto</h4>
        <p>
          Se guardará el presupuesto de <strong>{{ tituloPresupuesto }}</strong> para
          <strong>{{ clienteNombre }}</strong> en la base de datos.
        </p>
        <div class="modal-actions">
          <button type="button" class="btn-modal-secondary" @click="cerrarModalConfirmacion">Cancelar</button>
          <button type="button" class="btn-modal-primary" :disabled="guardandoPresupuesto" @click="confirmarYGuardarPresupuesto">
            {{ guardandoPresupuesto ? 'Guardando...' : 'Confirmar y ver presupuesto' }}
          </button>
        </div>
      </div>
    </div>

    <section class="print-only print-sheet">
      <header class="print-header">
        <div class="brand-row">
          <img class="print-logo" :src="logoUrl" alt="Logo ProReformasVLC" />
          <div>
            <p class="company">ProReformasVLC</p>
            <p class="doc-type">Presupuesto orientativo</p>
          </div>
        </div>
        <div class="print-meta">
          <p>Fecha: {{ ticketFecha }}</p>
        </div>
      </header>

      <section class="print-block">
        <h4>{{ tituloPresupuesto }}</h4>
      </section>

      <section class="print-block">
        <h4>Datos del cliente</h4>
        <div class="print-grid">
          <p><strong>Nombre:</strong> {{ clienteNombre || '-' }}</p>
          <p><strong>Telefono:</strong> {{ clienteTelefono || '-' }}</p>
          <p><strong>Email:</strong> {{ clienteEmail || '-' }}</p>
          <p><strong>Ciudad:</strong> {{ clienteCiudad || '-' }}</p>
          <p class="full"><strong>Direccion:</strong> {{ clienteDireccion || '-' }}</p>
          <p class="full"><strong>Observaciones:</strong> {{ observaciones || '-' }}</p>
        </div>
      </section>

      <section class="print-block">
        <h4>Conceptos incluidos</h4>
        <table class="print-table">
          <thead>
            <tr>
              <th>Concepto</th>
              <th>Cantidad</th>
              <th>Precio unitario</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="linea in lineas" :key="linea.id">
              <td>{{ linea.descripcion }}</td>
              <td>{{ linea.cantidad }}</td>
              <td>{{ formatEUR(linea.precio) }}</td>
              <td>{{ formatEUR(linea.subtotal) }}</td>
            </tr>
          </tbody>
        </table>
      </section>

      <section class="print-total">
        <p><span>Subtotal:</span><strong>{{ formatEUR(subtotal) }}</strong></p>
        <p><span>IVA (21%):</span><strong>{{ formatEUR(iva) }}</strong></p>
        <p class="grand-total"><span>Total estimado:</span><strong>{{ formatEUR(total) }}</strong></p>
      </section>

      <div class="print-company-footer">
        <div class="company-footer-brand">
          <img class="company-footer-logo" :src="logoUrl" alt="Logo ProReformasVLC" />
          <div>
            <p class="company-footer-name">ProReformasVLC</p>
            <p class="company-footer-tag">Reformas y rehabilitacion de vivienda</p>
          </div>
        </div>

        <div class="company-footer-data">
          <p><strong>Direccion:</strong> C/ Torrente n18, 46014 Valencia, Espana</p>
          <p><strong>Telefono:</strong> +34 606 939 035</p>
          <p><strong>Email:</strong> julian.proreformasvlc@gmail.com</p>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.disclaimer-box {
  border: 1px solid #f0d5a8;
  border-left: 6px solid #b7791f;
  background: linear-gradient(135deg, #fffbf2, #fff6e5);
  border-radius: 14px;
  padding: 1rem 1.25rem;
  box-shadow: 0 10px 20px rgba(153, 102, 33, 0.08);
}

.disclaimer-title {
  margin: 0 0 0.35rem;
  font-size: 0.9rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #7c5318;
}

.disclaimer-text {
  margin: 0;
  color: #5f461f;
  line-height: 1.55;
}

.alerta-validacion {
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 10px;
  padding: 0.65rem 0.8rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.alerta-bloqueo {
  border: 1px solid #fde68a;
  background: #fffbeb;
  color: #92400e;
  border-radius: 10px;
  padding: 0.65rem 0.8rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.resumen-datos {
  display: grid;
  gap: 0.45rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 60;
  background: rgba(17, 24, 39, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-card {
  width: min(560px, 100%);
  background: #fff;
  border-radius: 14px;
  padding: 1.2rem;
  box-shadow: 0 18px 35px rgba(15, 23, 42, 0.25);
}

.modal-card h4 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
  font-weight: 800;
  color: #1f2937;
}

.modal-card p {
  margin: 0;
  color: #374151;
  line-height: 1.5;
}

.modal-actions {
  margin-top: 1rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.55rem;
}

.btn-modal-secondary,
.btn-modal-primary {
  border: 0;
  border-radius: 8px;
  padding: 0.6rem 0.85rem;
  font-weight: 700;
  cursor: pointer;
}

.btn-modal-secondary {
  background: #e5e7eb;
  color: #1f2937;
}

.btn-modal-primary {
  background: #0f766e;
  color: #fff;
}

.btn-modal-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.print-only {
  display: none;
}

@media print {
  @page {
    size: A4;
    margin: 12mm;
  }

  .no-print {
    display: none !important;
  }

  .print-only {
    display: block !important;
  }

  .print-sheet {
    color: #1f2937;
    font-size: 12px;
  }

  .print-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #d1d5db;
    padding-bottom: 10px;
    margin-bottom: 14px;
  }

  .brand-row {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .print-logo {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #d1d5db;
  }

  .company {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 0.04em;
  }

  .doc-type {
    margin: 2px 0 0;
    font-size: 13px;
    color: #4b5563;
  }

  .print-meta p {
    margin: 0;
    font-size: 12px;
  }

  .print-block {
    margin-bottom: 14px;
  }

  .print-block h4 {
    margin: 0 0 6px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #374151;
  }

  .print-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 14px;
  }

  .print-grid p {
    margin: 0;
  }

  .print-grid .full {
    grid-column: 1 / -1;
  }

  .print-table {
    width: 100%;
    border-collapse: collapse;
  }

  .print-table th,
  .print-table td {
    border: 1px solid #d1d5db;
    padding: 6px;
    text-align: left;
    vertical-align: top;
  }

  .print-table th {
    background: #f3f4f6;
    font-weight: 700;
  }

  .print-total {
    margin-top: 10px;
    border-top: 2px solid #e5e7eb;
    padding-top: 8px;
    width: 50%;
    margin-left: auto;
  }

  .print-total p {
    display: flex;
    justify-content: space-between;
    margin: 0 0 4px;
  }

  .print-total .grand-total {
    font-size: 14px;
    margin-top: 6px;
    border-top: 1px solid #d1d5db;
    padding-top: 6px;
  }

  .print-company-footer {
    margin-top: 14px;
    padding-top: 10px;
    border-top: 2px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }

  .company-footer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .company-footer-logo {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #d1d5db;
  }

  .company-footer-name {
    margin: 0;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.03em;
  }

  .company-footer-tag {
    margin: 1px 0 0;
    font-size: 10px;
    color: #4b5563;
  }

  .company-footer-data {
    text-align: right;
    font-size: 10px;
    line-height: 1.4;
    color: #374151;
  }

  .company-footer-data p {
    margin: 0;
  }

  :global(footer) {
    display: none !important;
  }

  :global(body) {
    background: #fff !important;
  }
}
</style>
