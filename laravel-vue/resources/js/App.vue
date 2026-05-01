<script setup>
import { computed } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import SiteHeader from './components/SiteHeader.vue';
import SiteFooter from './components/SiteFooter.vue';

const route = useRoute();
const useMinimalLayout = computed(() => route.meta.minimalLayout === true);
</script>

<template>
  <div class="min-h-screen flex flex-col bg-zinc-50 text-zinc-800">
    <SiteHeader v-if="!useMinimalLayout" />
    <main class="flex-1">
      <RouterView />
    </main>
    <SiteFooter v-if="!useMinimalLayout" />
  </div>
</template>
