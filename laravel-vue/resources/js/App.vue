<script setup>
import { computed } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import SiteHeader from './components/SiteHeader.vue';
import SiteFooter from './components/SiteFooter.vue';

const route = useRoute();
const useMinimalLayout = computed(() => route.meta.minimalLayout === true);
const showWhatsappFab = computed(() => !useMinimalLayout.value && route.name !== 'presupuestos');
</script>

<template>
  <div class="min-h-screen flex flex-col bg-zinc-50 text-zinc-800">
    <SiteHeader v-if="!useMinimalLayout" />
    <main class="flex-1">
      <RouterView />
    </main>
    <SiteFooter v-if="!useMinimalLayout" />

    <!-- Botón flotante WhatsApp -->
    <a
      v-if="showWhatsappFab"
      href="https://web.whatsapp.com/"
      target="_blank"
      rel="noopener noreferrer"
      aria-label="Contactar por WhatsApp"
      class="whatsapp-fab"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
        <path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.737 5.469 2.027 7.77L0 32l8.445-2.01A15.93 15.93 0 0 0 16 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm0 29.333a13.27 13.27 0 0 1-6.77-1.856l-.484-.287-5.012 1.194 1.215-4.875-.316-.5A13.267 13.267 0 0 1 2.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.27-9.874c-.398-.199-2.355-1.162-2.72-1.294-.366-.133-.632-.199-.898.199-.266.398-1.03 1.294-1.263 1.56-.232.266-.465.299-.863.1-.398-.199-1.682-.62-3.204-1.978-1.184-1.057-1.984-2.363-2.217-2.761-.232-.398-.025-.613.174-.811.179-.178.398-.465.597-.698.199-.232.266-.398.398-.664.133-.266.067-.499-.033-.698-.1-.199-.898-2.165-1.23-2.963-.324-.778-.653-.672-.898-.684l-.765-.013c-.266 0-.698.1-1.064.499s-1.396 1.364-1.396 3.327 1.43 3.858 1.628 4.124c.199.266 2.815 4.298 6.822 6.028.954.412 1.698.658 2.278.843.957.305 1.828.262 2.516.159.767-.115 2.355-.963 2.688-1.894.332-.931.332-1.729.232-1.895-.099-.165-.365-.265-.763-.464z"/>
      </svg>
    </a>
  </div>
</template>

<style scoped>
.whatsapp-fab {
  position: fixed;
  bottom: 1.75rem;
  right: 1.75rem;
  z-index: 9999;
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 50%;
  background-color: #25d366;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(37, 211, 102, 0.45);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.whatsapp-fab:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 24px rgba(37, 211, 102, 0.6);
}

.whatsapp-fab svg {
  width: 2rem;
  height: 2rem;
}

@media print {
  .whatsapp-fab {
    display: none !important;
  }
}
</style>
