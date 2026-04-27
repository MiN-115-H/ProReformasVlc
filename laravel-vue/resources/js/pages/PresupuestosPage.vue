<script setup>
import { computed, ref, watch } from 'vue';
import PageHero from '../components/PageHero.vue';

const IVA_RATE = 0.21;

const clienteNombre = ref('');
const clienteTelefono = ref('');
const clienteEmail = ref('');
const clienteDireccion = ref('');
const clienteCiudad = ref('');
const observaciones = ref('');

const datosPersonalesConfirmados = ref(false);

const categoriaSeleccionada = ref('');
const conceptoSeleccionadoId = ref('');
const cantidad = ref(1);

const lineas = ref([]);
const guardando = ref(false);
const guardadoId = ref(null);
const estadoGuardado = ref('');

const conceptosPorCategoria = {
  cocina: [
    { id: 1, descripcion: 'Poner suelos', precio_base: 39.5 },
    { id: 2, descripcion: 'Alicatado', precio_base: 44.0 },
  ],
  bano: [
    { id: 3, descripcion: 'Cambiar banera', precio_base: 890.0 },
    { id: 4, descripcion: 'Alicatado', precio_base: 48.0 },
    { id: 5, descripcion: 'Poner suelos', precio_base: 41.0 },
  ],
};

const ticketFecha = new Date().toLocaleDateString('es-ES');

const conceptosDisponibles = computed(() => {
  if (!categoriaSeleccionada.value) return [];
  return conceptosPorCategoria[categoriaSeleccionada.value] || [];
});

const subtotal = computed(() => lineas.value.reduce((acc, line) => acc + line.subtotal, 0));
const iva = computed(() => subtotal.value * IVA_RATE);
const total = computed(() => subtotal.value + iva.value);

const estadoConfirmacionTexto = computed(() =>
  datosPersonalesConfirmados.value ? 'Datos personales confirmados' : 'Datos pendientes de confirmar',
);

watch([clienteNombre, clienteTelefono, clienteEmail, clienteDireccion, clienteCiudad], () => {
  datosPersonalesConfirmados.value = false;
});

watch(categoriaSeleccionada, () => {
  conceptoSeleccionadoId.value = '';
});

const confirmarDatos = () => {
  if (!clienteNombre.value.trim() || !clienteTelefono.value.trim() || !clienteEmail.value.trim()) {
    alert('Para confirmar, rellena al menos nombre, telefono y correo electronico.');
    return;
  }

  datosPersonalesConfirmados.value = true;
};

const anadirConcepto = () => {
  if (!categoriaSeleccionada.value || !conceptoSeleccionadoId.value) return;

  const concepto = conceptosDisponibles.value.find((c) => String(c.id) === String(conceptoSeleccionadoId.value));
  if (!concepto || Number(cantidad.value) <= 0) return;

  lineas.value.push({
    id: Date.now(),
    descripcion: concepto.descripcion,
    cantidad: Number(cantidad.value),
    precio: concepto.precio_base,
    subtotal: Number(cantidad.value) * concepto.precio_base,
  });

  categoriaSeleccionada.value = '';
  conceptoSeleccionadoId.value = '';
  cantidad.value = 1;
};

const eliminarLinea = (id) => {
  lineas.value = lineas.value.filter((line) => line.id !== id);
};

const imprimirPresupuesto = () => {
  if (!datosPersonalesConfirmados.value) {
    alert('Debes rellenar y confirmar tus datos personales antes de imprimir el presupuesto.');
    return;
  }

  guardarPresupuesto(true);
};

const guardarPresupuesto = async (imprimirDespues = false) => {
  if (lineas.value.length === 0) {
    alert('Anade al menos un concepto antes de guardar el presupuesto.');
    return;
  }

  guardando.value = true;
  estadoGuardado.value = '';

  try {
    const payload = {
      cliente_nombre: clienteNombre.value,
      cliente_telefono: clienteTelefono.value,
      cliente_email: clienteEmail.value,
      direccion: clienteDireccion.value || null,
      ciudad: clienteCiudad.value || null,
      observaciones: observaciones.value || null,
      fecha_presupuesto: new Date().toISOString().slice(0, 10),
      lineas: lineas.value,
      subtotal: Number(subtotal.value.toFixed(2)),
      iva: Number(iva.value.toFixed(2)),
      total: Number(total.value.toFixed(2)),
    };

    const response = await fetch('/api/presupuestos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error('No se pudo guardar el presupuesto.');
    }

    const data = await response.json();
    guardadoId.value = data.id;
    estadoGuardado.value = `Presupuesto guardado (ID ${data.id}).`;

    if (imprimirDespues) {
      window.print();
    }
  } catch (error) {
    estadoGuardado.value = 'Error al guardar el presupuesto.';
    alert('No se pudo guardar el presupuesto. Revisa los datos e intentalo de nuevo.');
  } finally {
    guardando.value = false;
  }
};

const formatEUR = (value) => `${value.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} EUR`;
</script>

<template>
  <div>
    <PageHero
      title="Presupuestos"
      text="Calcula un presupuesto estimado y solicita una propuesta detallada a medida."
      image="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1800&q=80"
    />
    <main class="max-w-7xl mx-auto px-6 py-24">
      <div class="mb-8 p-5 rounded-lg border-l-4 border-amber-500 bg-amber-50 text-amber-900">
        <p class="text-sm leading-relaxed">
          Aviso importante: los precios y este presupuesto son una estimacion aproximada calculada con precios medios de materiales y mano de obra. Este calculo no refleja el precio final. Para un presupuesto detallado y definitivo, pongase en contacto con la empresa.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold uppercase tracking-widest mb-6 text-primary">1. Datos del Cliente</h2>
            <form class="space-y-4" @submit.prevent>
              <div>
                <label class="block text-sm font-semibold mb-2" for="cliente_nombre">Nombre Completo</label>
                <input id="cliente_nombre" v-model="clienteNombre" type="text" placeholder="Ej. Juan Perez" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold mb-2" for="cliente_telefono">Telefono</label>
                  <input id="cliente_telefono" v-model="clienteTelefono" type="tel" placeholder="+34 600..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required />
                </div>
                <div>
                  <label class="block text-sm font-semibold mb-2" for="cliente_email">Correo Electronico</label>
                  <input id="cliente_email" v-model="clienteEmail" type="email" placeholder="correo@ejemplo.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold mb-2" for="direccion">Direccion del Proyecto</label>
                  <input id="direccion" v-model="clienteDireccion" type="text" placeholder="Calle, numero, piso..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" />
                </div>
                <div>
                  <label class="block text-sm font-semibold mb-2" for="ciudad">Ciudad</label>
                  <input id="ciudad" v-model="clienteCiudad" type="text" placeholder="Ej. Valencia" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold mb-2" for="observaciones">Observaciones Generales</label>
                <textarea id="observaciones" v-model="observaciones" rows="3" placeholder="Detalles extra sobre la reforma..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"></textarea>
              </div>

              <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <button type="button" @click="confirmarDatos" class="bg-primary hover:bg-primary/90 text-white py-2 px-5 rounded-lg font-bold uppercase transition-all">
                  Confirmar Datos Personales
                </button>
                <span class="text-xs font-semibold" :class="datosPersonalesConfirmados ? 'text-green-600' : 'text-gray-600'">
                  {{ estadoConfirmacionTexto }}
                </span>
              </div>
            </form>
          </div>

          <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold uppercase tracking-widest mb-6 text-primary">2. Anadir Conceptos</h2>
            <form class="space-y-4" @submit.prevent="anadirConcepto">
              <div>
                <label class="block text-sm font-semibold mb-2" for="categoria">Categoria de Reforma</label>
                <select id="categoria" v-model="categoriaSeleccionada" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                  <option value="" disabled>Selecciona una categoria...</option>
                  <option value="cocina">Cocina</option>
                  <option value="bano">Bano</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-semibold mb-2" for="concepto">Trabajo Especifico</label>
                <select id="concepto" v-model="conceptoSeleccionadoId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" :disabled="!categoriaSeleccionada" required>
                  <option value="" disabled>{{ categoriaSeleccionada ? 'Selecciona un trabajo...' : 'Primero elige una categoria...' }}</option>
                  <option v-for="concepto in conceptosDisponibles" :key="concepto.id" :value="String(concepto.id)">{{ concepto.descripcion }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-semibold mb-2" for="cantidad">Cantidad</label>
                <input id="cantidad" v-model.number="cantidad" type="number" min="1" step="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required />
              </div>

              <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-lg font-bold uppercase transition-all">+ Anadir al Presupuesto</button>
            </form>
          </div>
        </div>

        <div>
          <div class="bg-white rounded-lg shadow-lg p-8 sticky top-20">
            <h3 class="text-2xl font-bold uppercase tracking-widest mb-2 text-primary">Presupuesto Estimado</h3>
            <span class="text-xs text-gray-500">{{ ticketFecha }}</span>

            <div class="space-y-3 my-6">
              <div class="flex justify-between text-sm font-semibold pb-2 border-b">
                <span>Concepto</span>
                <span>Subtotal</span>
              </div>
            </div>

            <div class="space-y-2 mb-6">
              <div v-if="lineas.length === 0" class="text-center text-gray-500 py-8">
                <p>Aun no has anadido conceptos a tu presupuesto.</p>
              </div>
              <div v-for="linea in lineas" :key="linea.id" class="grid grid-cols-[1fr_auto_auto_auto] gap-2 items-start border-b border-gray-100 pb-2">
                <div class="text-sm">
                  <p class="font-semibold text-gray-800">{{ linea.descripcion }}</p>
                  <small class="text-gray-500">Cantidad: {{ linea.cantidad }}</small>
                </div>
                <div class="text-xs text-gray-500 self-center">x{{ linea.cantidad }}</div>
                <div class="text-sm font-semibold self-center">{{ formatEUR(linea.subtotal) }}</div>
                <button type="button" class="text-red-500 hover:text-red-700 text-sm" @click="eliminarLinea(linea.id)">X</button>
              </div>
            </div>

            <div class="space-y-3 border-t border-b py-4 mb-6">
              <div class="flex justify-between"><span>Subtotal:</span><span class="font-semibold">{{ formatEUR(subtotal) }}</span></div>
              <div class="flex justify-between text-sm text-gray-600"><span>IVA (21%):</span><span class="font-semibold">{{ formatEUR(iva) }}</span></div>
            </div>

            <div class="mb-6 p-4 bg-primary text-white rounded-lg text-center">
              <p class="text-sm font-semibold mb-1">TOTAL ESTIMADO</p>
              <p class="text-3xl font-bold">{{ formatEUR(total) }}</p>
            </div>

            <p v-if="estadoGuardado" class="text-xs font-semibold text-center mb-3" :class="guardadoId ? 'text-green-600' : 'text-red-600'">
              {{ estadoGuardado }}
            </p>

            <button type="button" class="w-full mb-3 bg-secondary hover:bg-secondary/90 text-white py-3 rounded-lg font-bold uppercase transition-all disabled:opacity-60" @click="guardarPresupuesto(false)" :disabled="guardando">
              {{ guardando ? 'Guardando...' : 'Guardar Presupuesto' }}
            </button>

            <button type="button" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-lg font-bold uppercase transition-all disabled:opacity-60" @click="imprimirPresupuesto" :disabled="guardando">
              Imprimir Presupuesto
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
