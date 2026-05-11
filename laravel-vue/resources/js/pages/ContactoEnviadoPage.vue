<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const secondsLeft = ref(30);
let intervalId = null;

const message = computed(() => {
  if (secondsLeft.value <= 0) {
    return 'Redirigiendo a contacto...';
  }

  return `Serás redirigido a la página de contacto en ${secondsLeft.value} segundos.`;
});

const volverAContacto = () => {
  router.push({ name: 'contacto' });
};

onMounted(() => {
  intervalId = window.setInterval(() => {
    secondsLeft.value -= 1;

    if (secondsLeft.value <= 0) {
      if (intervalId) {
        window.clearInterval(intervalId);
      }
      volverAContacto();
    }
  }, 1000);
});

onBeforeUnmount(() => {
  if (intervalId) {
    window.clearInterval(intervalId);
  }
});
</script>

<template>
  <section class="py-24 bg-white min-h-[60vh] flex items-center">
    <div class="max-w-3xl mx-auto px-6 w-full">
      <div class="border border-green-200 bg-green-50 rounded-2xl p-10 text-center shadow-sm">
        <h1 class="text-3xl md:text-4xl font-bold uppercase tracking-wide text-green-800 mb-5">
          Mensaje enviado correctamente
        </h1>
        <p class="text-green-700 text-lg mb-2">
          Nos pondremos en contacto con usted con la mayor brevedad.
        </p>
        <p class="text-green-700 text-lg mb-8">Gracias.</p>

        <p class="text-sm text-green-700 mb-6">{{ message }}</p>

        <button
          type="button"
          class="bg-primary hover:bg-primary/90 text-white py-3 px-7 rounded-lg font-bold uppercase transition-all"
          @click="volverAContacto"
        >
          Volver a contacto
        </button>
      </div>
    </div>
  </section>
</template>
