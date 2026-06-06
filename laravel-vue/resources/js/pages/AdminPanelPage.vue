<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { getCsrfToken } from '../utils/csrf';

const menu = [
  { id: 'tipos', label: 'Tipos de presupuesto' },
  { id: 'unidades', label: 'Unidades de medida' },
  { id: 'conceptos', label: 'Conceptos' },
  { id: 'presupuestos', label: 'Presupuestos' },
  { id: 'contactos', label: 'Contactos' },
  { id: 'servicios', label: 'Servicios' },
  { id: 'albumes', label: 'Proyectos' },
  { id: 'usuarios', label: 'Usuarios' },
];

const activeSection = ref('tipos');
const loading = ref(false);
const errorMessage = ref('');
const logoUrl = '/img/logo.jpg';
const servicioImageSize = Object.freeze({ width: 1200, height: 800 });

const tipos = ref([]);
const unidades = ref([]);
const conceptos = ref([]);
const presupuestos = ref([]);
const contactos = ref([]);
const servicios = ref([]);
const albumes = ref([]);
const usuarios = ref([]);
const albaranModalOpen = ref(false);
const albaranLoading = ref(false);
const presupuestoDetalle = ref(null);
const mostrarFormTipo = ref(false);
const mostrarFormUnidad = ref(false);
const conceptoFiltroTipo = ref('');
const mostrarFormConcepto = ref(false);
const mostrarFormServicio = ref(false);
const mostrarFormAlbum = ref(false);
const mostrarFormUsuario = ref(false);
const conceptoOrden = ref('precio_asc');
const presupuestoFiltroTipo = ref('');
const presupuestoOrden = ref('fecha_desc');
const presupuestoBusqueda = ref('');
const contactoFiltroEstado = ref('');
const contactoOrden = ref('fecha_desc');
const contactoBusqueda = ref('');
const servicioCreatePreviewUrl = ref('');
const servicioEditPreviewUrl = ref('');

// Inline editing
const editingId = reactive({ tipo: null, unidad: null, concepto: null, servicio: null, album: null, usuario: null });
const editBuffer = reactive({ nombre: '', descripcion: '' });
const conceptoEditBuffer = reactive({ descripcion: '', precio_base: '', unidad_id: '', tipo_presupuesto_id: '' });
const servicioEditBuffer = reactive({ nombre: '', descripcion: '', imagen: null, imagen_actual: '' });
const tipoEditBuffer = reactive({ nombre: '', descripcion: '' });
const unidadEditBuffer = reactive({ nombre: '', abreviatura: '' });
const usuarioEditBuffer = reactive({ name: '', email: '' });

const tipoForm = reactive({ nombre: '', descripcion: '' });
const unidadForm = reactive({ nombre: '', abreviatura: '' });
const conceptoForm = reactive({ descripcion: '', precio_base: '', unidad_id: '', tipo_presupuesto_id: '' });
const servicioForm = reactive({ nombre: '', descripcion: '', imagen: null });
const albumForm = reactive({ nombre: '', descripcion: '', categoria: '' });
const usuarioForm = reactive({ name: '', email: '' });

const sectionTitle = computed(() => menu.find((item) => item.id === activeSection.value)?.label ?? 'Panel');

const getDateValue = (value) => {
  const timestamp = value ? new Date(value).getTime() : 0;
  return Number.isNaN(timestamp) ? 0 : timestamp;
};

const conceptosFiltrados = computed(() => {
  const filtered = conceptos.value.filter((concepto) => {
    if (!conceptoFiltroTipo.value) return true;
    return String(concepto.tipo_presupuesto_id) === String(conceptoFiltroTipo.value);
  });

  return [...filtered].sort((first, second) => {
    if (conceptoOrden.value === 'fecha_desc') return getDateValue(second.created_at) - getDateValue(first.created_at);
    if (conceptoOrden.value === 'fecha_asc') return getDateValue(first.created_at) - getDateValue(second.created_at);
    if (conceptoOrden.value === 'precio_desc') return Number(second.precio_base) - Number(first.precio_base);
    return Number(first.precio_base) - Number(second.precio_base);
  });
});

const presupuestosFiltrados = computed(() => {
  const query = presupuestoBusqueda.value.trim().toLowerCase();
  const filtered = presupuestos.value.filter((presupuesto) => {
    const coincideTipo = !presupuestoFiltroTipo.value
      || String(presupuesto.tipo_presupuesto_id) === String(presupuestoFiltroTipo.value);

    if (!coincideTipo) return false;
    if (!query) return true;

    const haystack = [
      presupuesto.id,
      presupuesto.titulo,
      presupuesto.cliente,
      presupuesto.telefono,
      presupuesto.email,
      presupuesto.ciudad,
      presupuesto.tipo,
      presupuesto.estado,
    ]
      .map((value) => String(value ?? '').toLowerCase())
      .join(' ');

    return haystack.includes(query);
  });

  return [...filtered].sort((first, second) => {
    if (presupuestoOrden.value === 'fecha_asc') return getDateValue(first.created_at) - getDateValue(second.created_at);
    if (presupuestoOrden.value === 'total_asc') return Number(first.total) - Number(second.total);
    if (presupuestoOrden.value === 'total_desc') return Number(second.total) - Number(first.total);
    return getDateValue(second.created_at) - getDateValue(first.created_at);
  });
});

const contactosFiltrados = computed(() => {
  const query = contactoBusqueda.value.trim().toLowerCase();

  const filtered = contactos.value.filter((contacto) => {
    if (contactoFiltroEstado.value === 'nuevos' && (contacto.leido || contacto.respondido)) return false;
    if (contactoFiltroEstado.value === 'leidos' && !contacto.leido) return false;
    if (contactoFiltroEstado.value === 'respondidos' && !contacto.respondido) return false;
    if (contactoFiltroEstado.value === 'pendientes' && contacto.respondido) return false;

    if (!query) return true;

    const haystack = [
      contacto.id,
      contacto.nombre,
      contacto.email,
      contacto.telefono,
      contacto.asunto,
      contacto.mensaje,
    ]
      .map((value) => String(value ?? '').toLowerCase())
      .join(' ');

    return haystack.includes(query);
  });

  return [...filtered].sort((first, second) => {
    const firstDate = getDateValue(first.fecha_recepcion || first.created_at);
    const secondDate = getDateValue(second.fecha_recepcion || second.created_at);
    if (contactoOrden.value === 'fecha_asc') return firstDate - secondDate;
    return secondDate - firstDate;
  });
});

const estadoColor = (estado) => {
  if (estado === 'aceptado') return 'badge-green';
  if (estado === 'rechazado') return 'badge-red';
  return 'badge-gray';
};

const estadoContacto = (contacto) => {
  if (contacto.respondido) return 'respondido';
  if (contacto.leido) return 'leido';
  return 'nuevo';
};

const estadoContactoColor = (contacto) => {
  if (contacto.respondido) return 'badge-green';
  if (contacto.leido) return 'badge-gray';
  return 'badge-red';
};

const formatEUR = (value) => `${Number(value || 0).toFixed(2)} EUR`;
const formatDate = (value) => {
  if (!value) return 'Sin fecha';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('es-ES');
};

const request = async (url, options = {}) => {
  const method = String(options.method || 'GET').toUpperCase();
  const requiresCsrf = !['GET', 'HEAD', 'OPTIONS'].includes(method);

  const csrfHeader = requiresCsrf
    ? { 'X-CSRF-TOKEN': await getCsrfToken() }
    : {};

  const headers = {
    Accept: 'application/json',
    ...csrfHeader,
    ...(options.headers ?? {}),
  };

  if (!(options.body instanceof FormData) && !('Content-Type' in headers)) {
    headers['Content-Type'] = 'application/json';
  }

  const response = await fetch(url, {
    credentials: 'same-origin',
    headers,
    ...options,
  });

  if (response.status === 401 || response.status === 403) {
    window.location.href = '/login';
    throw new Error('Necesitas iniciar sesión como administrador.');
  }

  if (!response.ok) {
    const payload = await response.json().catch(() => null);
    throw new Error(payload?.message || 'No se pudo completar la operación.');
  }

  if (response.status === 204) return null;
  return response.json();
};


let errorTimeout = null;
const withFeedback = async (fn) => {
  errorMessage.value = '';
  if (errorTimeout) {
    clearTimeout(errorTimeout);
    errorTimeout = null;
  }
  try {
    await fn();
  } catch (e) {
    // Detectar error de restricción de borrado (puede venir del backend o del mensaje de error)
    if (
      typeof e.message === 'string' &&
      (
        e.message.toLowerCase().includes('foreign key constraint failed') || // SQLite
        e.message.toLowerCase().includes('foreign key constraint') ||
        e.message.toLowerCase().includes('restric') ||
        e.message.toLowerCase().includes('activo') ||
        e.message.toLowerCase().includes('referenciado')
      )
    ) {
      errorMessage.value = 'No puedes eliminar un elemento activo.';
      errorTimeout = setTimeout(() => { errorMessage.value = ''; }, 3000);
    } else {
      errorMessage.value = e.message;
    }
  }
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
    contactos.value = data.contactos ?? [];
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

const startEditTipo = (tipo) => {
  editingId.tipo = tipo.id;
  tipoEditBuffer.nombre = tipo.nombre;
  tipoEditBuffer.descripcion = tipo.descripcion || '';
};

const saveTipo = (id) => withFeedback(async () => {
  await request(`/api/admin/tipos-presupuesto/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({
      nombre: tipoEditBuffer.nombre,
      descripcion: tipoEditBuffer.descripcion,
    }),
  });

  editingId.tipo = null;
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

const startEditUnidad = (unidad) => {
  editingId.unidad = unidad.id;
  unidadEditBuffer.nombre = unidad.nombre;
  unidadEditBuffer.abreviatura = unidad.abreviatura;
};

const saveUnidad = (id) => withFeedback(async () => {
  await request(`/api/admin/unidades/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({
      nombre: unidadEditBuffer.nombre,
      abreviatura: unidadEditBuffer.abreviatura,
    }),
  });

  editingId.unidad = null;
  await loadPanelData();
});

const toggleConcepto = (concepto) => withFeedback(async () => {
  const payload = await request(`/api/admin/conceptos/${concepto.id}`, {
    method: 'PATCH',
    body: JSON.stringify({ activo: !concepto.activo }),
  });
  concepto.activo = payload.activo;
});

const startEditConcepto = (concepto) => {
  editingId.concepto = concepto.id;
  conceptoEditBuffer.descripcion = concepto.descripcion;
  conceptoEditBuffer.precio_base = String(concepto.precio_base ?? '');
  conceptoEditBuffer.unidad_id = String(concepto.unidad_id ?? '');
  conceptoEditBuffer.tipo_presupuesto_id = String(concepto.tipo_presupuesto_id ?? '');
};

const saveConcepto = (id) => withFeedback(async () => {
  await request(`/api/admin/conceptos/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({
      descripcion: conceptoEditBuffer.descripcion,
      precio_base: Number(conceptoEditBuffer.precio_base),
      unidad_id: Number(conceptoEditBuffer.unidad_id),
      tipo_presupuesto_id: Number(conceptoEditBuffer.tipo_presupuesto_id),
    }),
  });

  editingId.concepto = null;
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

const changeEstado = (presupuesto, estado) => withFeedback(async () => {
  await request(`/api/admin/presupuestos/${presupuesto.id}/estado`, {
    method: 'PATCH',
    body: JSON.stringify({ estado }),
  });
  presupuesto.estado = estado;
});

const verAlbaran = (id) => withFeedback(async () => {
  albaranLoading.value = true;
  try {
    presupuestoDetalle.value = await request(`/api/admin/presupuestos/${id}`);
    albaranModalOpen.value = true;
  } finally {
    albaranLoading.value = false;
  }
});

const actualizarEstadoContacto = (contacto, changes) => withFeedback(async () => {
  const payload = await request(`/api/admin/contactos/${contacto.id}/estado`, {
    method: 'PATCH',
    body: JSON.stringify(changes),
  });

  contacto.leido = payload.leido;
  contacto.respondido = payload.respondido;
});

const removeContacto = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este formulario de contacto?')) return;
  await request(`/api/admin/contactos/${id}`, { method: 'DELETE' });
  contactos.value = contactos.value.filter((contacto) => contacto.id !== id);
});

const responderContacto = (contacto) => {
  const email = String(contacto.email ?? '').trim();

  if (!email) {
    errorMessage.value = 'Este contacto no tiene un email válido para responder.';
    return;
  }

  const subject = contacto.asunto
    ? `Re: ${contacto.asunto}`
    : 'Respuesta a tu solicitud de contacto';

  const body = [
    `Hola ${contacto.nombre || ''},`,
    '',
    'Gracias por contactar con ProReformasVLC.',
    '',
  ].join('\n');

  const mailtoUrl = `mailto:${encodeURIComponent(email)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  window.location.href = mailtoUrl;
};

const cerrarAlbaran = () => {
  albaranModalOpen.value = false;
};

const buildServicioFormData = (payload, method = 'POST') => {
  const formData = new FormData();
  formData.append('nombre', payload.nombre);
  formData.append('descripcion', payload.descripcion || '');

  if (method !== 'POST') {
    formData.append('_method', method);
  }

  if (payload.imagen instanceof File) {
    formData.append('imagen', payload.imagen);
  }

  return formData;
};

const clearPreviewUrl = (previewRef) => {
  if (previewRef.value?.startsWith('blob:')) {
    URL.revokeObjectURL(previewRef.value);
  }

  previewRef.value = '';
};

const setPreviewUrl = (previewRef, file, fallback = '') => {
  clearPreviewUrl(previewRef);
  previewRef.value = file instanceof File ? URL.createObjectURL(file) : fallback;
};

const resetServicioForm = () => {
  servicioForm.nombre = '';
  servicioForm.descripcion = '';
  servicioForm.imagen = null;
  clearPreviewUrl(servicioCreatePreviewUrl);
};

const clearServicioEditState = () => {
  editingId.servicio = null;
  servicioEditBuffer.nombre = '';
  servicioEditBuffer.descripcion = '';
  servicioEditBuffer.imagen = null;
  servicioEditBuffer.imagen_actual = '';
  clearPreviewUrl(servicioEditPreviewUrl);
};

const onServicioImageChange = (event, target) => {
  const [file] = event.target.files ?? [];

  if (target === 'edit') {
    servicioEditBuffer.imagen = file ?? null;
    setPreviewUrl(servicioEditPreviewUrl, file, servicioEditBuffer.imagen_actual || '');
    return;
  }

  servicioForm.imagen = file ?? null;
  setPreviewUrl(servicioCreatePreviewUrl, file);
};

const addServicio = () => withFeedback(async () => {
  await request('/api/admin/servicios', { method: 'POST', body: buildServicioFormData(servicioForm) });
  resetServicioForm();
  await loadPanelData();
});

const startEditServicio = (servicio) => {
  editingId.servicio = servicio.id;
  servicioEditBuffer.nombre = servicio.nombre;
  servicioEditBuffer.descripcion = servicio.descripcion || '';
  servicioEditBuffer.imagen = null;
  servicioEditBuffer.imagen_actual = servicio.imagen_url || '';
  setPreviewUrl(servicioEditPreviewUrl, null, servicio.imagen_url || '');
};

const saveServicio = (id) => withFeedback(async () => {
  await request(`/api/admin/servicios/${id}`, {
    method: 'POST',
    body: buildServicioFormData(servicioEditBuffer, 'PATCH'),
  });
  clearServicioEditState();
  await loadPanelData();
});

const removeServicio = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este servicio?')) return;
  await request(`/api/admin/servicios/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

onBeforeUnmount(() => {
  clearPreviewUrl(servicioCreatePreviewUrl);
  clearPreviewUrl(servicioEditPreviewUrl);
});

const addAlbum = () => withFeedback(async () => {
  await request('/api/admin/albums', { method: 'POST', body: JSON.stringify(albumForm) });
  albumForm.nombre = '';
  albumForm.descripcion = '';
  albumForm.categoria = '';
  await loadPanelData();
});

const startEditAlbum = (a) => { editingId.album = a.id; editBuffer.nombre = a.nombre; editBuffer.descripcion = a.descripcion || ''; editBuffer.categoria = a.categoria || ''; };

const saveAlbum = (id) => withFeedback(async () => {
  await request(`/api/admin/albums/${id}`, { method: 'PATCH', body: JSON.stringify({ nombre: editBuffer.nombre, descripcion: editBuffer.descripcion, categoria: editBuffer.categoria }) });
  editingId.album = null;
  await loadPanelData();
});

const removeAlbum = (id) => withFeedback(async () => {
  if (!confirm('¿Eliminar este proyecto y sus fotos?')) return;
  await request(`/api/admin/albums/${id}`, { method: 'DELETE' });
  await loadPanelData();
});

const uploadFoto = (albumId, event) => withFeedback(async () => {
  const file = event.target.files[0];
  if (!file) return;
  
  const formData = new FormData();
  formData.append('imagen', file);
  
  await request(`/api/admin/albums/${albumId}/fotos`, { method: 'POST', body: formData });
  event.target.value = ''; // reset input
  await loadPanelData();
});

const removeFoto = (fotoId) => withFeedback(async () => {
  if (!confirm('¿Eliminar esta foto?')) return;
  await request(`/api/admin/fotos/${fotoId}`, { method: 'DELETE' });
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

const startEditUsuario = (usuario) => {
  editingId.usuario = usuario.id;
  usuarioEditBuffer.name = usuario.name;
  usuarioEditBuffer.email = usuario.email;
};

const saveUsuario = (id) => withFeedback(async () => {
  await request(`/api/admin/usuarios/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({
      name: usuarioEditBuffer.name,
      email: usuarioEditBuffer.email,
    }),
  });

  editingId.usuario = null;
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
          <p>Gestión de datos internos de ProReformasVLC</p>
        </header>

        <p v-if="loading" class="status-box">Cargando datos...</p>
        <p v-if="errorMessage" class="status-box error">{{ errorMessage }}</p>

        <!-- Tipos -->
        <div v-show="activeSection === 'tipos'" class="panel-block">
          <div class="section-header">
            <h3>Tipos de presupuesto</h3>
            <button type="button" class="btn-primary" @click="mostrarFormTipo = !mostrarFormTipo">
              {{ mostrarFormTipo ? 'Cancelar' : '+ Agregar tipo' }}
            </button>
          </div>
          <div v-show="mostrarFormTipo" class="concepto-form-card">
            <h4>Nuevo tipo</h4>
            <form class="inline-form" @submit.prevent="addTipo">
              <input v-model="tipoForm.nombre" type="text" placeholder="Nombre" required />
              <input v-model="tipoForm.descripcion" type="text" placeholder="Descripción" />
              <button type="submit" class="btn-primary">Guardar tipo</button>
            </form>
          </div>
          <ul class="item-list">
            <li v-for="tipo in tipos" :key="tipo.id">
              <template v-if="editingId.tipo === tipo.id">
                <div class="edit-row">
                  <input v-model="tipoEditBuffer.nombre" type="text" placeholder="Nombre" required />
                  <input v-model="tipoEditBuffer.descripcion" type="text" placeholder="Descripción" />
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveTipo(tipo.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="editingId.tipo = null">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span><strong>{{ tipo.nombre }}</strong> — {{ tipo.descripcion || 'Sin descripción' }}</span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditTipo(tipo)">Editar</button>
                  <button type="button" class="btn-danger" @click="removeTipo(tipo.id)">Eliminar</button>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <!-- Unidades -->
        <div v-show="activeSection === 'unidades'" class="panel-block">
          <div class="section-header">
            <h3>Unidades de medida</h3>
            <button type="button" class="btn-primary" @click="mostrarFormUnidad = !mostrarFormUnidad">
              {{ mostrarFormUnidad ? 'Cancelar' : '+ Agregar unidad' }}
            </button>
          </div>
          <div v-show="mostrarFormUnidad" class="concepto-form-card">
            <h4>Nueva unidad</h4>
            <form class="inline-form" @submit.prevent="addUnidad">
              <input v-model="unidadForm.nombre" type="text" placeholder="Nombre" required />
              <input v-model="unidadForm.abreviatura" type="text" placeholder="Abreviatura" required />
              <button type="submit" class="btn-primary">Guardar unidad</button>
            </form>
          </div>
          <ul class="item-list">
            <li v-for="unidad in unidades" :key="unidad.id">
              <template v-if="editingId.unidad === unidad.id">
                <div class="edit-row">
                  <input v-model="unidadEditBuffer.nombre" type="text" placeholder="Nombre" required />
                  <input v-model="unidadEditBuffer.abreviatura" type="text" placeholder="Abreviatura" required />
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveUnidad(unidad.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="editingId.unidad = null">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span><strong>{{ unidad.nombre }}</strong> ({{ unidad.abreviatura }})</span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditUnidad(unidad)">Editar</button>
                  <button type="button" class="btn-danger" @click="removeUnidad(unidad.id)">Eliminar</button>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <!-- Conceptos -->
        <div v-show="activeSection === 'conceptos'" class="panel-block">
          <div class="section-header">
            <h3>Conceptos</h3>
            <div class="header-filters">
              <select v-model="conceptoFiltroTipo">
                <option value="">Todos los tipos</option>
                <option v-for="tipo in tipos" :key="tipo.id" :value="String(tipo.id)">{{ tipo.nombre }}</option>
              </select>
              <select v-model="conceptoOrden">
                <option value="precio_asc">Precio: menor a mayor</option>
                <option value="precio_desc">Precio: mayor a menor</option>
                <option value="fecha_desc">Fecha: más reciente</option>
                <option value="fecha_asc">Fecha: más antigua</option>
              </select>
            </div>
            <button type="button" class="btn-primary" @click="mostrarFormConcepto = !mostrarFormConcepto">
              {{ mostrarFormConcepto ? 'Cancelar' : '+ Agregar concepto' }}
            </button>
          </div>
          <div v-show="mostrarFormConcepto" class="concepto-form-card">
            <h4>Nuevo concepto</h4>
            <form class="inline-form" @submit.prevent="addConcepto">
              <input v-model="conceptoForm.descripcion" type="text" placeholder="Descripción" required />
              <input v-model="conceptoForm.precio_base" type="number" step="0.01" min="0" placeholder="Precio base (EUR)" required />
              <select v-model="conceptoForm.unidad_id" required>
                <option value="">Unidad de medida</option>
                <option v-for="unidad in unidades" :key="unidad.id" :value="unidad.id">{{ unidad.nombre }}</option>
              </select>
              <select v-model="conceptoForm.tipo_presupuesto_id" required>
                <option value="">Tipo de presupuesto</option>
                <option v-for="tipo in tipos" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
              </select>
              <button type="submit" class="btn-primary">Guardar concepto</button>
            </form>
          </div>
          <ul class="item-list">
            <li v-for="concepto in conceptosFiltrados" :key="concepto.id">
              <template v-if="editingId.concepto === concepto.id">
                <div class="edit-row">
                  <input v-model="conceptoEditBuffer.descripcion" type="text" placeholder="Descripción" required />
                  <input v-model="conceptoEditBuffer.precio_base" type="number" step="0.01" min="0" placeholder="Precio base" required />
                  <select v-model="conceptoEditBuffer.unidad_id" required>
                    <option value="">Unidad de medida</option>
                    <option v-for="unidad in unidades" :key="unidad.id" :value="String(unidad.id)">{{ unidad.nombre }}</option>
                  </select>
                  <select v-model="conceptoEditBuffer.tipo_presupuesto_id" required>
                    <option value="">Tipo</option>
                    <option v-for="tipo in tipos" :key="tipo.id" :value="String(tipo.id)">{{ tipo.nombre }}</option>
                  </select>
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveConcepto(concepto.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="editingId.concepto = null">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span>
                  <strong>{{ concepto.descripcion }}</strong>
                  — {{ concepto.precio_base.toFixed(2) }} EUR / {{ concepto.unidad_abrev }}
                  <em class="muted">({{ concepto.tipo_nombre }})</em>
                  <span :class="['badge', concepto.activo ? 'badge-green' : 'badge-red']">
                    {{ concepto.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditConcepto(concepto)">Editar</button>
                  <button
                    type="button"
                    :class="concepto.activo ? 'btn-neutral' : 'btn-primary'"
                    @click="toggleConcepto(concepto)"
                  >
                    {{ concepto.activo ? 'Desactivar' : 'Activar' }}
                  </button>
                  <button type="button" class="btn-danger" @click="removeConcepto(concepto.id)">Eliminar</button>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <!-- Presupuestos -->
        <div v-show="activeSection === 'presupuestos'" class="panel-block">
          <div class="section-header">
            <h3>Solicitudes recibidas</h3>
            <div class="header-filters">
              <input
                v-model="presupuestoBusqueda"
                type="search"
                placeholder="Buscar por cliente, email, teléfono, título, ID..."
                aria-label="Buscar presupuestos"
              />
              <select v-model="presupuestoFiltroTipo">
                <option value="">Todos los tipos</option>
                <option v-for="tipo in tipos" :key="tipo.id" :value="String(tipo.id)">{{ tipo.nombre }}</option>
              </select>
              <select v-model="presupuestoOrden">
                <option value="fecha_desc">Alta: más reciente</option>
                <option value="fecha_asc">Alta: más antigua</option>
                <option value="total_asc">Total: menor a mayor</option>
                <option value="total_desc">Total: mayor a menor</option>
              </select>
            </div>
          </div>
          <ul class="item-list">
            <li v-for="presupuesto in presupuestosFiltrados" :key="presupuesto.id" class="presupuesto-row">
              <div class="presupuesto-info">
                <strong>#{{ presupuesto.id }} · {{ presupuesto.titulo || 'Presupuesto sin título' }}</strong>
                <span>{{ presupuesto.cliente }} · {{ presupuesto.telefono }}</span>
                <span class="muted">{{ presupuesto.email }}</span>
                <span class="muted">{{ presupuesto.ciudad || 'Sin ciudad' }} · {{ presupuesto.tipo || 'Sin tipo' }}</span>
                <span class="muted">{{ presupuesto.metros_cuadrados || 0 }} m2 · {{ presupuesto.fecha || 'Sin fecha' }}</span>
                <strong>{{ presupuesto.total.toFixed(2) }} EUR</strong>
                <span :class="['badge', estadoColor(presupuesto.estado)]">{{ presupuesto.estado }}</span>
              </div>
              <div class="action-group">
                <button type="button" class="btn-neutral" @click="verAlbaran(presupuesto.id)">Ver albarán</button>
                <button type="button" class="btn-primary" @click="changeEstado(presupuesto, 'aceptado')">Aceptar</button>
                <button type="button" class="btn-danger" @click="changeEstado(presupuesto, 'rechazado')">Denegar</button>
                <button type="button" class="btn-neutral" @click="changeEstado(presupuesto, 'pendiente')">Pendiente</button>
              </div>
            </li>
          </ul>
        </div>

        <!-- Contactos -->
        <div v-show="activeSection === 'contactos'" class="panel-block">
          <div class="section-header">
            <h3>Formularios recibidos</h3>
            <div class="header-filters">
              <input
                v-model="contactoBusqueda"
                type="search"
                placeholder="Buscar por nombre, email, teléfono, asunto o mensaje..."
                aria-label="Buscar contactos"
              />
              <select v-model="contactoFiltroEstado">
                <option value="">Todos los estados</option>
                <option value="nuevos">Nuevos</option>
                <option value="leidos">Leídos</option>
                <option value="pendientes">Pendientes de respuesta</option>
                <option value="respondidos">Respondidos</option>
              </select>
              <select v-model="contactoOrden">
                <option value="fecha_desc">Recepción: más reciente</option>
                <option value="fecha_asc">Recepción: más antigua</option>
              </select>
            </div>
          </div>

          <ul class="item-list">
            <li v-for="contacto in contactosFiltrados" :key="contacto.id" class="presupuesto-row">
              <div class="presupuesto-info">
                <strong>#{{ contacto.id }} · {{ contacto.nombre }}</strong>
                <span>{{ contacto.email }} · {{ contacto.telefono || 'Sin teléfono' }}</span>
                <span class="muted">Asunto: {{ contacto.asunto || 'Sin asunto' }}</span>
                <span class="muted">{{ formatDate(contacto.fecha_recepcion || contacto.created_at) }}</span>
                <span class="muted">{{ contacto.mensaje }}</span>
                <span :class="['badge', estadoContactoColor(contacto)]">{{ estadoContacto(contacto) }}</span>
              </div>
              <div class="action-group">
                <button
                  type="button"
                  class="btn-neutral"
                  @click="actualizarEstadoContacto(contacto, { leido: !contacto.leido })"
                >
                  {{ contacto.leido ? 'Marcar no leído' : 'Marcar leído' }}
                </button>
                <button
                  type="button"
                  :class="contacto.respondido ? 'btn-neutral' : 'btn-primary'"
                  @click="actualizarEstadoContacto(contacto, { respondido: !contacto.respondido, leido: true })"
                >
                  {{ contacto.respondido ? 'Marcar no respondido' : 'Marcar respondido' }}
                </button>
                <button
                  type="button"
                  class="btn-primary"
                  @click="responderContacto(contacto)"
                >
                  Responder
                </button>
                <button type="button" class="btn-danger" @click="removeContacto(contacto.id)">Eliminar</button>
              </div>
            </li>
          </ul>
        </div>

        <!-- Servicios -->
        <div v-show="activeSection === 'servicios'" class="panel-block">
          <div class="section-header">
            <h3>Servicios</h3>
            <button type="button" class="btn-primary" @click="mostrarFormServicio = !mostrarFormServicio">
              {{ mostrarFormServicio ? 'Cancelar' : '+ Agregar servicio' }}
            </button>
          </div>
          <div v-show="mostrarFormServicio" class="concepto-form-card">
            <h4>Nuevo servicio</h4>
            <form class="inline-form" @submit.prevent="addServicio">
              <input v-model="servicioForm.nombre" type="text" placeholder="Nombre" required />
              <input v-model="servicioForm.descripcion" type="text" placeholder="Descripción" />
              <input
                type="file"
                accept="image/png,image/jpeg,image/webp"
                required
                @change="onServicioImageChange($event, 'create')"
              />
              <span class="service-image-note">
                Imagen obligatoria de {{ servicioImageSize.width }} x {{ servicioImageSize.height }} px.
              </span>
              <div v-if="servicioCreatePreviewUrl" class="service-preview-card">
                <img :src="servicioCreatePreviewUrl" :alt="servicioForm.nombre || 'Vista previa del servicio'" class="service-preview-image" />
                <span class="service-preview-label">Vista previa de la card</span>
              </div>
              <button type="submit" class="btn-primary">Guardar servicio</button>
            </form>
          </div>
          <ul class="item-list">
            <li v-for="servicio in servicios" :key="servicio.id">
              <template v-if="editingId.servicio === servicio.id">
                <div class="edit-row">
                  <input v-model="servicioEditBuffer.nombre" type="text" placeholder="Nombre" required />
                  <input v-model="servicioEditBuffer.descripcion" type="text" placeholder="Descripción" />
                  <input
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @change="onServicioImageChange($event, 'edit')"
                  />
                  <span class="service-image-note">Si subes una nueva imagen debe medir {{ servicioImageSize.width }} x {{ servicioImageSize.height }} px.</span>
                  <div v-if="servicioEditPreviewUrl" class="service-preview-card">
                    <img :src="servicioEditPreviewUrl" :alt="servicioEditBuffer.nombre || 'Vista previa del servicio'" class="service-preview-image" />
                    <span class="service-preview-label">Vista previa actual</span>
                  </div>
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveServicio(servicio.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="clearServicioEditState()">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span class="service-meta">
                  <img v-if="servicio.imagen_url" :src="servicio.imagen_url" :alt="servicio.nombre" class="service-thumb" />
                  <strong>{{ servicio.nombre }}</strong>
                  <span class="muted">{{ servicio.descripcion || 'Sin descripción' }}</span>
                  <span class="muted">Alta: {{ formatDate(servicio.created_at || servicio.fecha_creacion) }}</span>
                </span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditServicio(servicio)">Editar</button>
                  <button type="button" class="btn-danger" @click="removeServicio(servicio.id)">Eliminar</button>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <!-- Proyectos (Álbumes en la base de datos) -->
        <div v-show="activeSection === 'albumes'" class="panel-block">
          <div class="section-header">
            <h3>Proyectos realizados</h3>
            <button type="button" class="btn-primary" @click="mostrarFormAlbum = !mostrarFormAlbum">
              {{ mostrarFormAlbum ? 'Cancelar' : '+ Agregar proyecto' }}
            </button>
          </div>
          <div v-show="mostrarFormAlbum" class="concepto-form-card">
            <h4>Nuevo proyecto</h4>
            <form class="inline-form" @submit.prevent="addAlbum">
              <input v-model="albumForm.nombre" type="text" placeholder="Nombre del proyecto" required />
              <input v-model="albumForm.descripcion" type="text" placeholder="Descripción" />
              <select v-model="albumForm.categoria">
                <option value="">Categoría</option>
                <option value="Cocinas">Cocinas</option>
                <option value="Baños">Baños</option>
                <option value="Reformas Integrales">Reformas Integrales</option>
              </select>
              <button type="submit" class="btn-primary">Guardar proyecto</button>
            </form>
          </div>
          <ul class="item-list">
            <li v-for="album in albumes" :key="album.id" style="flex-direction: column; align-items: stretch;">
              <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <template v-if="editingId.album === album.id">
                  <div class="edit-row">
                    <input v-model="editBuffer.nombre" type="text" placeholder="Nombre" required />
                    <input v-model="editBuffer.descripcion" type="text" placeholder="Descripción" />
                    <select v-model="editBuffer.categoria">
                      <option value="">Categoría</option>
                      <option value="Cocinas">Cocinas</option>
                      <option value="Baños">Baños</option>
                      <option value="Reformas Integrales">Reformas Integrales</option>
                    </select>
                  </div>
                  <div class="action-group">
                    <button type="button" class="btn-primary" @click="saveAlbum(album.id)">Guardar</button>
                    <button type="button" class="btn-neutral" @click="editingId.album = null">Cancelar</button>
                  </div>
                </template>
                <template v-else>
                  <span>
                    <strong>{{ album.nombre }}</strong>
                    <span class="muted">{{ album.descripcion || 'Sin descripción' }}</span>
                    <span class="badge badge-gray" v-if="album.categoria">{{ album.categoria }}</span>
                  </span>
                  <div class="action-group">
                    <button type="button" class="btn-primary" @click="startEditAlbum(album)">Editar</button>
                    <button type="button" class="btn-danger" @click="removeAlbum(album.id)">Eliminar</button>
                  </div>
                </template>
              </div>
              
              <div class="album-fotos-section" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #ced9e5;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <h5 style="margin: 0; color: #1d3557; font-size: 0.9rem;">Fotos del proyecto ({{ album.fotos.length }})</h5>
                  <label class="btn-neutral" style="cursor: pointer; display: inline-block; font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                    + Subir foto
                    <input type="file" style="display: none;" accept="image/png,image/jpeg,image/webp" @change="uploadFoto(album.id, $event)">
                  </label>
                </div>
                <div class="fotos-grid" style="display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem;">
                  <div v-for="foto in album.fotos" :key="foto.id" class="foto-card" style="position: relative; width: 100px; height: 100px; flex-shrink: 0;">
                    <img :src="foto.url" :alt="foto.descripcion || 'Foto'" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid #ced9e5;">
                    <button @click="removeFoto(foto.id)" type="button" style="position: absolute; top: -5px; right: -5px; background: #e63946; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">&times;</button>
                  </div>
                  <div v-if="album.fotos.length === 0" style="color: #7a92a8; font-size: 0.85rem; font-style: italic;">No hay fotos en este proyecto.</div>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Usuarios -->
        <div v-show="activeSection === 'usuarios'" class="panel-block">
          <div class="section-header">
            <h3>Usuarios</h3>
            <button type="button" class="btn-primary" @click="mostrarFormUsuario = !mostrarFormUsuario">
              {{ mostrarFormUsuario ? 'Cancelar' : '+ Agregar usuario' }}
            </button>
          </div>
          <div v-show="mostrarFormUsuario" class="concepto-form-card">
            <h4>Nuevo usuario</h4>
            <form class="inline-form" @submit.prevent="addUsuario">
              <input v-model="usuarioForm.name" type="text" placeholder="Nombre" required />
              <input v-model="usuarioForm.email" type="email" placeholder="Email" required />
              <button type="submit" class="btn-primary">Guardar usuario</button>
            </form>
          </div>
          <ul class="item-list">
            <li v-for="usuario in usuarios" :key="usuario.id">
              <template v-if="editingId.usuario === usuario.id">
                <div class="edit-row">
                  <input v-model="usuarioEditBuffer.name" type="text" placeholder="Nombre" required />
                  <input v-model="usuarioEditBuffer.email" type="email" placeholder="Email" required />
                </div>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="saveUsuario(usuario.id)">Guardar</button>
                  <button type="button" class="btn-neutral" @click="editingId.usuario = null">Cancelar</button>
                </div>
              </template>
              <template v-else>
                <span>
                  <strong>{{ usuario.name }}</strong> — {{ usuario.email }}
                  <span :class="['badge', usuario.rol === 'admin' ? 'badge-green' : 'badge-gray']">{{ usuario.rol }}</span>
                  <span :class="['badge', usuario.activo ? 'badge-green' : 'badge-red']">{{ usuario.activo ? 'activo' : 'inactivo' }}</span>
                </span>
                <div class="action-group">
                  <button type="button" class="btn-primary" @click="startEditUsuario(usuario)">Editar</button>
                  <button v-if="usuario.rol !== 'admin'" type="button" class="btn-neutral" @click="toggleUsuario(usuario)">
                    {{ usuario.activo ? 'Desactivar' : 'Activar' }}
                  </button>
                  <button v-if="usuario.rol !== 'admin'" type="button" class="btn-danger" @click="removeUsuario(usuario.id)">
                    Eliminar
                  </button>
                  <span v-if="usuario.rol === 'admin'" class="muted">Cuenta principal</span>
                </div>
              </template>
            </li>
          </ul>
        </div>
      </section>
    </div>

    <div v-if="albaranModalOpen" class="modal-overlay" @click.self="cerrarAlbaran">
      <article class="albaran-modal">
        <header class="albaran-head">
          <div>
            <h2>Albarán presupuesto #{{ presupuestoDetalle?.id }}</h2>
            <p>{{ presupuestoDetalle?.titulo || 'Presupuesto' }}</p>
          </div>
          <div class="action-group print-hidden">
            <button type="button" class="btn-primary" @click="window.print()">Imprimir</button>
            <button type="button" class="btn-neutral" @click="cerrarAlbaran">Cerrar</button>
          </div>
        </header>

        <p v-if="albaranLoading" class="status-box">Cargando albarán...</p>

        <div v-else-if="presupuestoDetalle" class="albaran-body">
          <!-- Company header (visible on screen and in print) -->
          <section class="albaran-company-header">
            <div class="company-logo-wrap">
              <img :src="logoUrl" alt="ProReformasVLC" class="company-logo" />
            </div>
            <div class="company-info">
              <strong class="company-name">ProReformasVLC</strong>
              <span>C/ Torrente n18, 46014 Valencia, España</span>
              <span>Tel: +34 606 939 035</span>
              <span>julian.proreformasvlc@gmail.com</span>
            </div>
            <div class="company-doc">
              <span class="doc-label">ALBARÁN</span>
              <span class="doc-num">#{{ presupuestoDetalle.id }}</span>
              <span class="doc-date">{{ formatDate(presupuestoDetalle.fecha) }}</span>
            </div>
          </section>

          <section class="albaran-block">
            <h4>Datos del cliente</h4>
            <p><strong>Nombre:</strong> {{ presupuestoDetalle.cliente_nombre }}</p>
            <p><strong>Teléfono:</strong> {{ presupuestoDetalle.cliente_telefono }}</p>
            <p><strong>Email:</strong> {{ presupuestoDetalle.cliente_email }}</p>
            <p><strong>Dirección:</strong> {{ presupuestoDetalle.direccion || '-' }}</p>
            <p><strong>Ciudad:</strong> {{ presupuestoDetalle.ciudad || '-' }}</p>
          </section>

          <section class="albaran-block">
            <h4>Resumen</h4>
            <p><strong>Tipo:</strong> {{ presupuestoDetalle.tipo || 'Sin tipo' }}</p>
            <p><strong>Estado:</strong> {{ presupuestoDetalle.estado }}</p>
            <p><strong>Fecha:</strong> {{ formatDate(presupuestoDetalle.fecha) }}</p>
            <p><strong>Superficie:</strong> {{ presupuestoDetalle.metros_cuadrados || 0 }} m2</p>
          </section>

          <section class="albaran-block full">
            <h4>Conceptos</h4>
            <table class="albaran-table">
              <thead>
                <tr>
                  <th>Descripción</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(linea, idx) in (presupuestoDetalle.lineas || [])" :key="idx">
                  <td>{{ linea.descripcion }}</td>
                  <td>{{ linea.cantidad }}</td>
                  <td>{{ formatEUR(linea.precio) }}</td>
                  <td>{{ formatEUR(linea.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
          </section>

          <section class="albaran-total">
            <p><span>Subtotal:</span><strong>{{ formatEUR(presupuestoDetalle.subtotal) }}</strong></p>
            <p><span>IVA:</span><strong>{{ formatEUR(presupuestoDetalle.iva) }}</strong></p>
            <p class="grand-total"><span>Total:</span><strong>{{ formatEUR(presupuestoDetalle.total) }}</strong></p>
          </section>
        </div>
      </article>
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
  margin-bottom: 0;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.section-header h3 {
  margin-bottom: 0;
  white-space: nowrap;
}

.header-filters {
  display: flex;
  gap: 0.5rem;
  flex: 1;
  flex-wrap: wrap;
}

.header-filters select {
  padding: 0.55rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #ced9e5;
  font-size: 0.88rem;
  background: #fff;
  flex: 1;
  min-width: 140px;
}

.header-filters input {
  padding: 0.55rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #ced9e5;
  font-size: 0.88rem;
  background: #fff;
  flex: 2;
  min-width: 220px;
}

.concepto-form-card {
  background: #f0f5fa;
  border: 1px solid #ced9e5;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.concepto-form-card h4 {
  color: #1d3557;
  font-size: 0.95rem;
  margin-bottom: 0.75rem;
}

.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.85rem;
}

.filter-bar select {
  padding: 0.65rem 0.8rem;
  border-radius: 8px;
  border: 1px solid #ced9e5;
  font-size: 0.93rem;
  background: #fff;
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

.service-meta {
  flex: 1;
}

.service-thumb {
  width: 84px;
  height: 56px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #d6e0ea;
}

.service-image-note {
  color: #5c728a;
  font-size: 0.82rem;
  align-self: center;
}

.service-preview-card {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 0.55rem;
  border: 1px solid #d6e0ea;
  border-radius: 10px;
  background: #f8fbff;
}

.service-preview-image {
  width: 180px;
  aspect-ratio: 3 / 2;
  object-fit: cover;
  border-radius: 8px;
}

.service-preview-label {
  color: #1d3557;
  font-size: 0.8rem;
  font-weight: 600;
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

.presupuesto-info .muted {
  white-space: normal;
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

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 999;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.albaran-modal {
  width: min(980px, 100%);
  max-height: 90vh;
  overflow: auto;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
  padding: 1rem;
}

.albaran-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  border-bottom: 1px solid #e2ebf4;
  padding-bottom: 0.75rem;
}

.albaran-head h2 {
  margin: 0;
  color: #1d3557;
}

.albaran-head p {
  margin: 0.25rem 0 0;
  color: #5c728a;
}

.albaran-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.8rem;
  margin-top: 0.8rem;
}

.albaran-block {
  border: 1px solid #e2ebf4;
  border-radius: 10px;
  padding: 0.75rem;
  background: #fafcff;
}

.albaran-block.full {
  grid-column: 1 / -1;
}

.albaran-block h4 {
  margin: 0 0 0.5rem;
  color: #1d3557;
}

.albaran-block p {
  margin: 0.2rem 0;
}

.albaran-table {
  width: 100%;
  border-collapse: collapse;
}

.albaran-table th,
.albaran-table td {
  border: 1px solid #d6e0ea;
  padding: 0.45rem;
  text-align: left;
}

.albaran-table th {
  background: #edf2f8;
}

.albaran-total {
  grid-column: 1 / -1;
  justify-self: end;
  width: min(340px, 100%);
  border-top: 1px solid #e2ebf4;
  padding-top: 0.5rem;
}

.albaran-total p {
  margin: 0.2rem 0;
  display: flex;
  justify-content: space-between;
}

.albaran-total .grand-total {
  margin-top: 0.5rem;
  padding-top: 0.4rem;
  border-top: 1px solid #d6e0ea;
}

@media (max-width: 980px) {
  .admin-wrap { grid-template-columns: 1fr; }
  .presupuesto-row { flex-wrap: wrap; }
  .albaran-body { grid-template-columns: 1fr; }
  .albaran-company-header { flex-direction: column; gap: 0.75rem; }
}

.albaran-company-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1rem 1.25rem;
  border-bottom: 2px solid #1d3557;
  margin-bottom: 0.5rem;
  grid-column: 1 / -1;
}

.company-logo-wrap {
  flex-shrink: 0;
}

.company-logo {
  height: 60px;
  width: auto;
  object-fit: contain;
}

.company-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  flex: 1;
  font-size: 0.82rem;
  color: #333;
  line-height: 1.5;
}

.company-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1d3557;
}

.company-doc {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.2rem;
  white-space: nowrap;
}

.doc-label {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1d3557;
  letter-spacing: 0.08em;
}

.doc-num {
  font-size: 0.9rem;
  color: #457b9d;
  font-weight: 600;
}

.doc-date {
  font-size: 0.8rem;
  color: #555;
}

@media print {
  @page {
    size: A4;
    margin: 12mm;
  }

  .admin-wrap,
  .panel-head,
  .status-box,
  .menu-btn,
  .admin-sidebar,
  .admin-content > :not(.modal-overlay) {
    display: none !important;
  }

  .modal-overlay {
    position: static !important;
    inset: auto !important;
    background: #fff !important;
    display: block !important;
    padding: 0 !important;
  }

  .albaran-modal {
    width: 100% !important;
    max-height: none !important;
    overflow: visible !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
  }

  .print-hidden {
    display: none !important;
  }

  .albaran-company-header {
    border-bottom-color: #1d3557 !important;
    padding: 0 0 8pt 0 !important;
    margin-bottom: 10pt !important;
  }

  .company-logo {
    height: 50px !important;
  }

  .company-name {
    font-size: 13pt !important;
  }

  .doc-label {
    font-size: 13pt !important;
  }
}
</style>

