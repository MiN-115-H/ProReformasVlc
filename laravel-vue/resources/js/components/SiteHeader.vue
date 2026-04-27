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
  <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-zinc-200 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 md:px-12 py-4 flex justify-between items-center">
      <RouterLink to="/" class="font-extrabold text-lg tracking-wide text-zinc-900">
        ProReformasVLC
      </RouterLink>

      <ul class="hidden lg:flex items-center space-x-8 font-semibold text-sm uppercase tracking-wider">
        <li v-for="link in links" :key="link.to">
          <RouterLink
            :to="link.to"
            class="transition-colors"
            :class="route.path === link.to ? 'text-emerald-700 border-b-2 border-emerald-700 pb-1' : 'hover:text-emerald-700'"
          >
            {{ link.label }}
          </RouterLink>
        </li>
      </ul>
    </nav>
  </header>
</template>
