<script setup>
import { onMounted, ref } from 'vue';
import PageHero from '../components/PageHero.vue';

const servicios = ref([]);
const loading = ref(true);
const errorMessage = ref('');

const loadServicios = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch('/api/servicios', {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      throw new Error('No se pudieron cargar los servicios.');
    }

    const data = await response.json();
    servicios.value = data.servicios ?? [];
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    loading.value = false;
  }
};

onMounted(loadServicios);
</script>

<template>
  <div>
    <PageHero
      title="Nuestros Servicios"
      text="Servicios especializados para cubrir todas las fases de una reforma integral en Valencia."
      image="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="py-20 bg-white">
      <div v-if="loading" class="max-w-7xl mx-auto px-6 text-center text-gray-500">
        Cargando servicios...
      </div>
      <div v-else-if="errorMessage" class="max-w-7xl mx-auto px-6 text-center text-red-600">
        {{ errorMessage }}
      </div>
      <div v-else-if="servicios.length === 0" class="max-w-7xl mx-auto px-6 text-center text-gray-500">
        Aún no hay servicios publicados.
      </div>
      <div v-else class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
        <article
          v-for="servicio in servicios"
          :key="servicio.id"
          class="group bg-white rounded-2xl overflow-hidden border border-gray-200 shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2"
        >
          <div class="relative h-56 overflow-hidden">
            <img v-if="servicio.img" :src="servicio.img" :alt="servicio.titulo" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
            <div v-else class="w-full h-full bg-linear-to-br from-slate-200 via-slate-100 to-white"></div>
            <div class="absolute inset-0 bg-linear-to-t from-black/65 via-black/20 to-transparent"></div>
            <h3 class="absolute bottom-4 left-4 right-4 text-white text-lg font-extrabold uppercase tracking-wider">{{ servicio.titulo }}</h3>
          </div>
          <div class="p-6">
            <p class="text-gray-600 text-sm leading-relaxed">{{ servicio.desc || 'Servicio disponible bajo consulta.' }}</p>
          </div>
        </article>
      </div>
    </section>

    <div class="text-center pb-16">
      <RouterLink
        to="/contacto"
        class="inline-block border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors px-8 py-3 uppercase tracking-widest text-sm font-bold"
      >
        Solicitar Información
      </RouterLink>
    </div>
  </div>
</template>
