<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const route = useRoute();
const isAdminAuthenticated = ref(false);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const logoClickCount = ref(0);
let logoClickTimer = null;

const links = [
  { to: '/', label: 'Inicio' },
  { to: '/empresa', label: 'Empresa' },
  { to: '/servicios', label: 'Servicios' },
  { to: '/proyectos', label: 'Proyectos' },
  { to: '/presupuestos', label: 'Presupuestos' },
  { to: '/contacto', label: 'Contacto' },
];

const refreshSessionState = async () => {
  try {
    const response = await fetch('/auth/me', { headers: { Accept: 'application/json' } });
    if (!response.ok) {
      isAdminAuthenticated.value = false;
      return;
    }

    const payload = await response.json();
    isAdminAuthenticated.value = payload?.user?.rol === 'admin' && payload?.user?.activo === true;
  } catch {
    isAdminAuthenticated.value = false;
  }
};

const logout = async () => {
  try {
    await fetch('/auth/logout', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
    });
  } finally {
    window.location.href = '/login';
  }
};

onMounted(refreshSessionState);

const handleSecretLogoClick = () => {
  logoClickCount.value += 1;

  if (logoClickTimer) {
    clearTimeout(logoClickTimer);
  }

  if (logoClickCount.value >= 5) {
    logoClickCount.value = 0;
    window.location.href = '/login';
    return;
  }

  logoClickTimer = setTimeout(() => {
    logoClickCount.value = 0;
    logoClickTimer = null;
  }, 2500);
};

onBeforeUnmount(() => {
  if (logoClickTimer) {
    clearTimeout(logoClickTimer);
  }
});
</script>

<template>
  <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 md:px-12 py-4 flex justify-between items-center">
      <div class="flex items-center">
        <RouterLink to="/" class="flex items-center" @click="handleSecretLogoClick">
          <img :src="'/img/logo.jpg'" alt="Pro Reformas Valencia" class="h-12 md:h-14 w-auto object-contain" />
        </RouterLink>
      </div>

      <ul class="hidden lg:flex items-center space-x-8 font-semibold text-sm uppercase tracking-wider">
        <li v-for="link in links" :key="link.to">
          <RouterLink
            :to="link.to"
            class="transition-colors"
            :class="route.path === link.to ? 'text-primary border-b-2 border-primary pb-1' : 'hover:text-primary'"
          >
            {{ link.label }}
          </RouterLink>
        </li>
        <template v-if="isAdminAuthenticated">
          <li>
            <RouterLink
              to="/admin/panel"
              class="transition-colors"
              :class="route.path.startsWith('/admin') ? 'text-primary border-b-2 border-primary pb-1' : 'hover:text-primary'"
            >
              Panel
            </RouterLink>
          </li>
          <li>
            <button
              type="button"
              class="transition-colors hover:text-primary cursor-pointer"
              @click="logout"
            >
              Salir
            </button>
          </li>
        </template>
      </ul>
    </nav>
  </header>
</template>
