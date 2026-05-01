<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const menu = [
  { id: 'tipos', label: 'Tipos de presupuesto' },
  { id: 'unidades', label: 'Unidades de medida' },
  { id: 'conceptos', label: 'Conceptos' },
  { id: 'presupuestos', label: 'Presupuestos' },
  { id: 'servicios', label: 'Servicios' },
  { id: 'albumes', label: 'Albumes' },
  { id: 'usuarios', label: 'Usuarios' },
];

const activeSection = ref('tipos');
const loading = ref(false);
const errorMessage = ref('');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const tipos = ref([]);
const unidades = ref([]);
const conceptos = ref([]);
const presupuestos = ref([]);
const servicios = ref([]);
const albumes = ref([]);
const usuarios = ref([]);

// Inline editing
const editingId = reactive({ servicio: null, album: null });
const editBuffer = reactive({ nombre: '', descripcion: '' });

const tipoForm = reactive({ nombre: '', descripcion: '' });
const unidadForm = reactive({ nombre: '', abreviatura: '' });
const conceptoForm = reactive({ descripcion: '', precio_base: '', unidad_id: '', tipo_presupuesto_id: '' });
const presupuestoForm = reactive({ cliente: '', ciudad: '', tipo_presupuesto_id: '' });
const servicioForm = reactive({ nombre: '', descripcion: '' });
const albumForm = reactive({ nombre: '', descripcion: '' });
const usuarioForm = reactive({ name: '', email: '' });

const sectionTitle = computed(() => menu.find((item) => item.id === activeSection.value)?.label ?? 'Panel');

const estadoColor = (estado) => {
  if (estado === 'aceptado') return 'badge-green';
  if (estado === 'rechazado') return 'badge-red';
  return 'badge-gray';
};

const request = async (url, options = {}) => {
  const response = await fetch(url, {
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      ...(options.headers ?? {}),
    },
    ...options,
  });

  if (response.status === 401 || response.status === 403) {
    window.location.href = '/login';
    throw new Error('Necesitas iniciar sesion como administrador.');
  }

  if (!response.ok) {
    const payload = await response.json().catch(() => null);
    throw new Error(payload?.message || 'No se pudo completar la operacion.');
  }

  if (response.status === 204) return null;
  return response.json();
};

const withFeedback = async (fn) => {
  errorMessage.value = '';
  try { await fn(); } catch (e) { errorMessage.value = e.message; }
};

const loadPanelData = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const data = await request('/api/admin/panel-data');
    tipos.value = data.tipos ?? [];
    unidades.value = data.unidades ?? [];
    conceptos.value = data.conceptos ?? [];
    presupuestos.value = data.presupuestos ?? [];
    servicios.value = data.servicios ?? [];
    albumes.value = data.albumes ?? [];
    usuarios.value = data.usuarios ?? [];
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    loading.value = false;
  }
};

const addTipo = () => withFeedback(async () => {
  await request('/api/admin/tipos-presupuesto', { method: 'POST', body: JSON.stringify(tipoForm) });
  tipoForm.nombre = '';
  tipoForm.descripcion = '';
  await loadPanelData();
});

const removeTipo = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este tipo?')) return;
  await request(`/api/admin/tipos-presupuesto/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

const addUnidad = () => withFeedback(async () => {
  await request('/api/admin/unidades', { method: 'POST', body: JSON.stringify(unidadForm) });
  unidadForm.nombre = '';
  unidadForm.abreviatura = '';
  await loadPanelData();
});

const removeUnidad = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar esta unidad?')) return;
  await request(`/api/admin/unidades/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

const addConcepto = () => withFeedback(async () => {
  await request('/api/admin/conceptos', {
    method: 'POST',
    body: JSON.stringify({
      descripcion: conceptoForm.descripcion,
      precio_base: Number(conceptoForm.precio_base),
      unidad_id: Number(conceptoForm.unidad_id),
      tipo_presupuesto_id: Number(conceptoForm.tipo_presupuesto_id),
    }),
  });
  conceptoForm.descripcion = '';
  conceptoForm.precio_base = '';
  conceptoForm.unidad_id = '';
  conceptoForm.tipo_presupuesto_id = '';
  await loadPanelData();
});

const removeConcepto = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este concepto?')) return;
  await request(`/api/admin/conceptos/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

const addPresupuesto = () => withFeedback(async () => {
  await request('/api/admin/presupuestos', {
    method: 'POST',
    body: JSON.stringify({
      cliente: presupuestoForm.cliente,
      ciudad: presupuestoForm.ciudad,
      tipo_presupuesto_id: presupuestoForm.tipo_presupuesto_id ? Number(presupuestoForm.tipo_presupuesto_id) : null,
    }),
  });
  presupuestoForm.cliente = '';
  presupuestoForm.ciudad = '';
  presupuestoForm.tipo_presupuesto_id = '';
  await loadPanelData();
});

const changeEstado = (presupuesto, estado) => withFeedback(async () => {
  await request(`/api/admin/presupuestos/${presupuesto.id}/estado`, {
    method: 'PATCH',
    body: JSON.stringify({ estado }),
  });
  presupuesto.estado = estado;
});

const addServicio = () => withFeedback(async () => {
  await request('/api/admin/servicios', { method: 'POST', body: JSON.stringify(servicioForm) });
  servicioForm.nombre = '';
  servicioForm.descripcion = '';
  await loadPanelData();
});

const startEditServicio = (s) => { editingId.servicio = s.id; editBuffer.nombre = s.nombre; editBuffer.descripcion = s.descripcion || ''; };

const saveServicio = (id) => withFeedback(async () => {
  await request(`/api/admin/servicios/${id}`, { method: 'PATCH', body: JSON.stringify({ nombre: editBuffer.nombre, descripcion: editBuffer.descripcion }) });
  editingId.servicio = null;
  await loadPanelData();
});

const removeServicio = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este servicio?')) return;
  await request(`/api/admin/servicios/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

const addAlbum = () => withFeedback(async () => {
  await request('/api/admin/albums', { method: 'POST', body: JSON.stringify(albumForm) });
  albumForm.nombre = '';
  albumForm.descripcion = '';
  await loadPanelData();
});

const startEditAlbum = (a) => { editingId.album = a.id; editBuffer.nombre = a.nombre; editBuffer.descripcion = a.descripcion || ''; };

const saveAlbum = (id) => withFeedback(async () => {
  await request(`/api/admin/albums/${id}`, { method: 'PATCH', body: JSON.stringify({ nombre: editBuffer.nombre, descripcion: editBuffer.descripcion }) });
  editingId.album = null;
  await loadPanelData();
});

const removeAlbum = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este album y sus fotos?')) return;
  await request(`/api/admin/albums/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

const addUsuario = () => withFeedback(async () => {
  await request('/api/admin/usuarios', { method: 'POST', body: JSON.stringify(usuarioForm) });
  usuarioForm.name = '';
  usuarioForm.email = '';
  await loadPanelData();
});

const toggleUsuario = (usuario) => withFeedback(async () => {
  const payload = await request(`/api/admin/usuarios/${usuario.id}/toggle`, { method: 'PATCH' });
  usuario.activo = payload.activo;
});

const removeUsuario = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este usuario?')) return;
  await request(`/api/admin/usuarios/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

onMounted(loadPanelData);
</script>

<template>
  <main class="admin-shell">
    <div class="admin-wrap">
      <aside class="admin-sidebar">
        <h2>Panel de control</h2>
        <button
          v-for="item in menu"
          :key="item.id"
          type="button"
          class="menu-btn"
          :class="{ active: activeSection === item.id }"
          @click="activeSection = item.id"
        >
          {{ item.label }}
        </button>
      </aside>

      <section class="admin-content">
        <header class="panel-head">
          <h1>{{ sectionTitle }}</h1>
          <p>Gestion de datos internos de ProReformasVLC</p>
        </header>

        <p v-if="loading" class="status-box">Cargando datos...</p>
        <p v-if="errorMessage" class="status-box error">{{ errorMessage }}</p>

        <!-- Tipos -->
        <div v-show="activeSection === 'tipos'" class="panel-block">
          <h3>Nuevo tipo</h3>
          <form class="inline-form" @submit.prevent="addTipo">
            <input v-model="tipoForm.nombre" type="text" placeholder="Nombre" required />
            <input v-model="tipoForm.descripcion" type="text" placeholder="Descripcion" />
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="tipo in tipos" :key="tipo.id">
              <span><strong>{{ tipo.nombre }}</strong> — {{ tipo.descripcion || 'Sin descripcion' }}</span>
              <button type="button" class="btn-danger" @click="removeTipo(tipo.id)">Eliminar</button>
            </li>
          </ul>
        </div>

        <!-- Unidades -->
        <div v-show="activeSection === 'unidades'" class="panel-block">
          <h3>Nueva unidad</h3>
          <form class="inline-form" @submit.prevent="addUnidad">
            <input v-model="unidadForm.nombre" type="text" placeholder="Nombre" required />
            <input v-model="unidadForm.abreviatura" type="text" placeholder="Abreviatura" required />
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="unidad in unidades" :key="unidad.id">
              <span><strong>{{ unidad.nombre }}</strong> ({{ unidad.abreviatura }})</span>
              <button type="button" class="btn-danger" @click="removeUnidad(unidad.id)">Eliminar</button>
            </li>
          </ul>
        </div>

        <!-- Conceptos -->
        <div v-show="activeSection === 'conceptos'" class="panel-block">
          <h3>Nuevo concepto</h3>
          <form class="inline-form" @submit.prevent="addConcepto">
            <input v-model="conceptoForm.descripcion" type="text" placeholder="Descripcion" required />
            <input v-model="conceptoForm.precio_base" type="number" step="0.01" min="0" placeholder="Precio base" required />
            <select v-model="conceptoForm.unidad_id" required>
              <option value="">Unidad</option>
              <option v-for="unidad in unidades" :key="unidad.id" :value="unidad.id">{{ unidad.nombre }}</option>
            </select>
            <select v-model="conceptoForm.tipo_presupuesto_id" required>
              <option value="">Tipo</option>
              <option v-for="tipo in tipos" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
            </select>
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="concepto in conceptos" :key="concepto.id">
              <span>
                <strong>{{ concepto.descripcion }}</strong>
                — {{ concepto.precio_base.toFixed(2) }} EUR / {{ concepto.unidad_abrev }}
                <em class="muted">({{ concepto.tipo_nombre }})</em>
              </span>
              <button type="button" class="btn-danger" @click="removeConcepto(concepto.id)">Eliminar</button>
            </li>
          </ul>
        </div>

        <!-- Presupuestos -->
        <div v-show="activeSection === 'presupuestos'" class="panel-block">
          <h3>Nuevo presupuesto rapido</h3>
          <form class="inline-form" @submit.prevent="addPresupuesto">
            <input v-model="presupuestoForm.cliente" type="text" placeholder="Cliente" required />
            <input v-model="presupuestoForm.ciudad" type="text" placeholder="Ciudad" />
            <select v-model="presupuestoForm.tipo_presupuesto_id">
              <option value="">Tipo opcional</option>
              <option v-for="tipo in tipos" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
            </select>
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="presupuesto in presupuestos" :key="presupuesto.id" class="presupuesto-row">
              <div class="presupuesto-info">
                <strong>#{{ presupuesto.id }}</strong>
                {{ presupuesto.cliente }} — {{ presupuesto.ciudad || 'Sin ciudad' }}
                <span class="muted">{{ presupuesto.tipo || 'Sin tipo' }}</span>
                <strong>{{ presupuesto.total.toFixed(2) }} EUR</strong>
                <span :class="['badge', estadoColor(presupuesto.estado)]">{{ presupuesto.estado }}</span>
              </div>
              <select
                class="estado-select"
                :value="presupuesto.estado"
                @change="changeEstado(presupuesto, $event.target.value)"
              >
                <option value="pendiente">Pendiente</option>
                <option value="aceptado">Aceptado</option>
                <option value="rechazado">Rechazado</option>
              </select>
            </li>
          </ul>
        </div>

        <!-- Servicios -->
        <div v-show="activeSection === 'servicios'" class="panel-block">
          <h3>Nuevo servicio</h3>
          <form class="inline-form" @submit.prevent="addServicio">
            <input v-model="servicioForm.nombre" type="text" placeholder="Nombre" required />
            <input v-model="servicioForm.descripcion" type="text" placeholder="Descripcion" />
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="servicio in servicios" :key="servicio.id">
              <template v-if="editingId.servicio === servicio.id">
                <div class="edit-row">
                  <input v-model="editBuffer.nombre" type="text" placeholder="Nombre" required />
                  <input v-model="editBuffer.descripcion" type="text" placeholder="Descripcion" />
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveServicio(servicio.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="editingId.servicio = null">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span>
                  <strong>{{ servicio.nombre }}</strong>
                  <span class="muted">{{ servicio.descripcion || 'Sin descripcion' }}</span>
                </span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditServicio(servicio)">Editar</button>
                  <button type="button" class="btn-danger" @click="removeServicio(servicio.id)">Eliminar</button>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <!-- Albums -->
        <div v-show="activeSection === 'albumes'" class="panel-block">
          <h3>Nuevo album</h3>
          <form class="inline-form" @submit.prevent="addAlbum">
            <input v-model="albumForm.nombre" type="text" placeholder="Nombre" required />
            <input v-model="albumForm.descripcion" type="text" placeholder="Descripcion" />
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="album in albumes" :key="album.id">
              <template v-if="editingId.album === album.id">
                <div class="edit-row">
                  <input v-model="editBuffer.nombre" type="text" placeholder="Nombre" required />
                  <input v-model="editBuffer.descripcion" type="text" placeholder="Descripcion" />
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveAlbum(album.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="editingId.album = null">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span>
                  <strong>{{ album.nombre }}</strong>
                  <span class="muted">{{ album.descripcion || 'Sin descripcion' }}</span>
                </span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditAlbum(album)">Editar</button>
                  <button type="button" class="btn-danger" @click="removeAlbum(album.id)">Eliminar</button>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <!-- Usuarios -->
        <div v-show="activeSection === 'usuarios'" class="panel-block">
          <h3>Nuevo usuario</h3>
          <form class="inline-form" @submit.prevent="addUsuario">
            <input v-model="usuarioForm.name" type="text" placeholder="Nombre" required />
            <input v-model="usuarioForm.email" type="email" placeholder="Email" required />
            <button type="submit">Agregar</button>
          </form>
          <ul class="item-list">
            <li v-for="usuario in usuarios" :key="usuario.id">
              <span>
                <strong>{{ usuario.name }}</strong> — {{ usuario.email }}
                <span :class="['badge', usuario.rol === 'admin' ? 'badge-green' : 'badge-gray']">{{ usuario.rol }}</span>
                <span :class="['badge', usuario.activo ? 'badge-green' : 'badge-red']">{{ usuario.activo ? 'activo' : 'inactivo' }}</span>
              </span>
              <div class="action-group">
                <button v-if="usuario.rol !== 'admin'" type="button" class="btn-neutral" @click="toggleUsuario(usuario)">
                  {{ usuario.activo ? 'Desactivar' : 'Activar' }}
                </button>
                <button v-if="usuario.rol !== 'admin'" type="button" class="btn-danger" @click="removeUsuario(usuario.id)">
                  Eliminar
                </button>
                <span v-if="usuario.rol === 'admin'" class="muted">Cuenta principal</span>
              </div>
            </li>
          </ul>
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.admin-shell {
  background: linear-gradient(165deg, #f5f7fb 0%, #dce8f4 100%);
  min-height: calc(100vh - 90px);
  padding: 2rem 1rem;
}

.admin-wrap {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.25rem;
}

.admin-sidebar,
.admin-content {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(24, 54, 88, 0.12);
  padding: 1.5rem;
}

.admin-sidebar h2 {
  margin: 0 0 1rem;
  color: #1d3557;
  font-size: 1.2rem;
}

.menu-btn {
  width: 100%;
  border: 0;
  border-radius: 10px;
  padding: 0.7rem 0.9rem;
  text-align: left;
  margin-bottom: 0.5rem;
  color: #1d3557;
  background: #edf2f8;
  font-weight: 600;
  cursor: pointer;
}

.menu-btn.active,
.menu-btn:hover {
  background: #2a9d8f;
  color: #fff;
}

.panel-head h1 {
  margin: 0;
  color: #1d3557;
  font-size: 1.7rem;
}

.panel-head p {
  margin-top: 0.35rem;
  color: #5c728a;
}

.panel-block {
  margin-top: 1.25rem;
}

.panel-block h3 {
  color: #1d3557;
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.inline-form {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.inline-form input,
.inline-form select {
  padding: 0.65rem 0.8rem;
  border-radius: 8px;
  border: 1px solid #ced9e5;
  font-size: 0.93rem;
}

.inline-form button {
  padding: 0.65rem 1rem;
  border: 0;
  border-radius: 8px;
  background: #1d3557;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

.inline-form button:hover {
  background: #2a9d8f;
}

.item-list {
  margin: 0.9rem 0 0;
  padding: 0;
  list-style: none;
}

.item-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  border: 1px solid #e2ebf4;
  border-radius: 10px;
  padding: 0.75rem 0.9rem;
  margin-bottom: 0.5rem;
  background: #fafcff;
  flex-wrap: wrap;
}

.item-list span {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.muted {
  color: #7a92a8;
  font-size: 0.88rem;
}

.action-group {
  display: flex;
  gap: 0.4rem;
  flex-shrink: 0;
  align-items: center;
}

.edit-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  flex: 1;
}

.edit-row input {
  padding: 0.5rem 0.7rem;
  border: 1px solid #ced9e5;
  border-radius: 8px;
  font-size: 0.9rem;
  flex: 1;
  min-width: 140px;
}

.presupuesto-row {
  flex-wrap: nowrap;
}

.presupuesto-info {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  align-items: center;
  flex: 1;
}

.estado-select {
  padding: 0.5rem 0.6rem;
  border: 1px solid #ced9e5;
  border-radius: 8px;
  font-size: 0.88rem;
  flex-shrink: 0;
}

.btn-primary {
  border: 0;
  border-radius: 8px;
  background: #1d3557;
  color: #fff;
  padding: 0.45rem 0.75rem;
  font-size: 0.88rem;
  cursor: pointer;
}
.btn-primary:hover { background: #2a9d8f; }

.btn-neutral {
  border: 0;
  border-radius: 8px;
  background: #e2ebf4;
  color: #1d3557;
  padding: 0.45rem 0.75rem;
  font-size: 0.88rem;
  cursor: pointer;
}
.btn-neutral:hover { background: #c8d8e8; }

.btn-danger {
  border: 0;
  border-radius: 8px;
  background: #e63946;
  color: #fff;
  padding: 0.45rem 0.75rem;
  font-size: 0.88rem;
  cursor: pointer;
}
.btn-danger:hover { background: #c42e39; }

.badge {
  display: inline-flex;
  padding: 0.2rem 0.55rem;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 700;
}
.badge-green { background: #d1fae5; color: #065f46; }
.badge-red { background: #fee2e2; color: #991b1b; }
.badge-gray { background: #e5e7eb; color: #374151; }

.status-box {
  margin-top: 0.9rem;
  padding: 0.75rem;
  border-radius: 10px;
  background: #edf2f8;
  color: #1d3557;
}

.status-box.error {
  background: #fde8ea;
  color: #7d1128;
}

@media (max-width: 980px) {
  .admin-wrap { grid-template-columns: 1fr; }
  .presupuesto-row { flex-wrap: wrap; }
}
</style>

