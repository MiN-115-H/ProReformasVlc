<script setup>
import { ref, onMounted, onBeforeUnmount, watch, defineEmits } from 'vue';

const showBanner = ref(false);
const emit = defineEmits(['visibility']);

function isMobileOrTablet() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 900;
}

function closeBanner() {
  showBanner.value = false;
  sessionStorage.setItem('appBannerClosed', '1');
}

function updateBannerVisibility() {
  const isMobile = isMobileOrTablet();
  const closed = sessionStorage.getItem('appBannerClosed');
  showBanner.value = isMobile && !closed;
}

onMounted(() => {
  updateBannerVisibility();
  window.addEventListener('resize', updateBannerVisibility);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateBannerVisibility);
});

// Emitir evento cuando cambia la visibilidad
watch(showBanner, (val) => {
  emit('visibility', val);
}, { immediate: true });
</script>

<template>
  <transition name="fade">
    <div v-if="showBanner" class="app-download-banner fixed bottom-0 left-0 w-full z-[9999] bg-secondary border-t-4 border-secondary shadow-lg flex items-center py-2 sm:py-4 px-4 sm:px-6" style="min-height: 48px;">
      <div class="flex flex-col sm:flex-row items-center justify-between w-full max-w-4xl mx-auto gap-2 sm:gap-4 flex-wrap relative">
        <!-- Botón cerrar arriba a la derecha -->
        <button @click="closeBanner" class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 text-2xl sm:text-3xl text-white hover:text-secondary font-bold leading-none bg-transparent border-none cursor-pointer px-2 z-10" aria-label="Cerrar banner">&times;</button>

        <!-- Texto a la izquierda -->
        <span class="font-bold text-white text-sm sm:text-lg md:text-xl whitespace-nowrap flex-shrink-0 mb-2 sm:mb-0">Descarga nuestra App</span>

        <!-- Botones al centro -->
        <div class="flex gap-3 sm:gap-4 items-center mx-auto mb-2 sm:mb-0">
          <a href="https://play.google.com/store" target="_blank" aria-label="Google Play" class="block">
            <img src="https://play.google.com/intl/en_us/badges/static/images/badges/es_badge_web_generic.png" alt="Google Play" class="h-9 sm:h-16 w-auto object-contain" style="max-width:100px;" />
          </a>
          <a href="https://www.apple.com/es/app-store/" target="_blank" aria-label="App Store" class="block">
            <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store" class="h-9 sm:h-16 w-auto object-contain" style="max-width:100px;" />
          </a>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
/*
@media (max-width: 640px) {
  .app-download-banner {
    padding-bottom: 5.5rem !important;
  }
}
*/
.app-download-banner {
  animation: slideUp 0.3s;
}
@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.bg-primary {
  background-color: #2563eb;
}
.border-primary {
  border-color: #2563eb;
}
.bg-secondary {
  background-color: #2D3436;
}
.border-secondary {
  border-color: #2D3436;
}
.hover\:text-secondary:hover {
  color: #fbbf24;
}
</style>
