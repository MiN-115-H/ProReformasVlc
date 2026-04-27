<script setup>
import { RouterLink, useRoute } from 'vue-router';

const route = useRoute();

const links = [
  { to: '/', label: 'Inicio' },
  { to: '/empresa', label: 'Empresa' },
  { to: '/servicios', label: 'Servicios' },
  { to: '/proyectos', label: 'Proyectos' },
  { to: '/presupuestos', label: 'Presupuestos' },
  { to: '/contacto', label: 'Contacto' },
];
</script>

<template>
  <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 md:px-12 py-4 flex justify-between items-center">
      <RouterLink to="/" class="flex items-center">
        <img :src="'/img/logo.jpg'" alt="Pro Reformas Valencia" class="h-12 md:h-14 w-auto object-contain" />
      </RouterLink>

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
      </ul>
    </nav>
  </header>
</template>
