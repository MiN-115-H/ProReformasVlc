
<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';



// const route = useRoute(); // Eliminada duplicada
const isAdminAuthenticated = ref(false);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const route = useRoute();

const logoClickCount = ref(0);
const isMenuOpen = ref(false);
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

const closeMenu = () => {
  isMenuOpen.value = false;
};

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

onMounted(refreshSessionState);

watch(
  () => route.fullPath,
  () => {
    closeMenu();
  }
);

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
    <nav class="max-w-7xl mx-auto px-4 md:px-12 py-4">
      <div class="flex justify-between items-center">
        <RouterLink to="/" class="flex items-center" @click="handleSecretLogoClick">
          <img :src="'/img/logo.jpg'" alt="Pro Reformas Valencia" class="h-12 md:h-14 w-auto object-contain" />
        </RouterLink>

        <button
          type="button"
          class="lg:hidden inline-flex h-11 w-11 items-center justify-center border border-gray-200 text-secondary transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/30"
          :aria-expanded="isMenuOpen"
          aria-controls="site-mobile-menu"
          :aria-label="isMenuOpen ? 'Cerrar menu de navegacion' : 'Abrir menu de navegacion'"
          @click="toggleMenu"
        >
          <span class="sr-only">Menu</span>
          <span class="relative block h-4 w-5" aria-hidden="true">
            <span
              class="absolute left-0 top-0 block h-0.5 w-5 bg-current transition-transform duration-200"
              :class="isMenuOpen ? 'translate-y-[7px] rotate-45' : ''"
            ></span>
            <span
              class="absolute left-0 top-[7px] block h-0.5 w-5 bg-current transition-opacity duration-200"
              :class="isMenuOpen ? 'opacity-0' : 'opacity-100'"
            ></span>
            <span
              class="absolute left-0 top-[14px] block h-0.5 w-5 bg-current transition-transform duration-200"
              :class="isMenuOpen ? '-translate-y-[7px] -rotate-45' : ''"
            ></span>
          </span>
        </button>

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
      </div>

      <div
        id="site-mobile-menu"
        class="lg:hidden overflow-hidden transition-[max-height,opacity] duration-300 ease-out"
        :class="isMenuOpen ? 'max-h-[520px] opacity-100' : 'max-h-0 opacity-0'"
      >
        <ul class="pt-4 pb-2 space-y-1 font-semibold text-sm uppercase tracking-wider">
          <li v-for="link in links" :key="`mobile-${link.to}`">
            <RouterLink
              :to="link.to"
              class="block border-l-2 px-4 py-3 transition-colors"
              :class="route.path === link.to ? 'border-primary bg-primary/10 text-primary' : 'border-transparent hover:border-primary hover:bg-gray-50 hover:text-primary'"
              @click="closeMenu"
            >
              {{ link.label }}
            </RouterLink>
          </li>
          <template v-if="isAdminAuthenticated">
            <li>
              <RouterLink
                to="/admin/panel"
                class="block border-l-2 px-4 py-3 transition-colors"
                :class="route.path.startsWith('/admin') ? 'border-primary bg-primary/10 text-primary' : 'border-transparent hover:border-primary hover:bg-gray-50 hover:text-primary'"
                @click="closeMenu"
              >
                Panel
              </RouterLink>
            </li>
            <li>
              <button
                type="button"
                class="block w-full border-l-2 border-transparent px-4 py-3 text-left transition-colors hover:border-primary hover:bg-gray-50 hover:text-primary"
                @click="logout"
              >
                Salir
              </button>
            </li>
          </template>
        </ul>
      </div>
    </nav>
  </header>
</template>
