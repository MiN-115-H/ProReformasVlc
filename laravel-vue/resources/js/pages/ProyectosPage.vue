<script setup>
import { computed, ref } from 'vue';
import PageHero from '../components/PageHero.vue';

const categoriaActiva = ref('todos');

const proyectos = [
  {
    categoria: 'cocina',
    titulo: 'Cocina abierta con isla',
    descripcion: 'Reforma completa de cocina en Campanar con isla central, iluminacion lineal y mobiliario a medida.',
    img: 'https://images.unsplash.com/photo-1556912172-45b7abe8b7e1?auto=format&fit=crop&w=1200&q=80',
  },
  {
    categoria: 'banos',
    titulo: 'Bano suite con microcemento',
    descripcion: 'Renovacion integral con ducha de obra, sanitarios suspendidos y revestimientos continuos.',
    img: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80',
  },
  {
    categoria: 'integral',
    titulo: 'Reforma integral de atico',
    descripcion: 'Redistribucion de estancias y actualizacion completa de instalaciones para mayor confort.',
    img: 'https://images.unsplash.com/photo-1600210492493-0946911123ea?auto=format&fit=crop&w=1200&q=80',
  },
  {
    categoria: 'cocina',
    titulo: 'Cocina minimalista en blanco',
    descripcion: 'Cocina lineal con encimera porcenalica y electrodomesticos integrados.',
    img: 'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?auto=format&fit=crop&w=1200&q=80',
  },
  {
    categoria: 'banos',
    titulo: 'Bano compacto optimizado',
    descripcion: 'Mueble suspendido, mampara fija y almacenaje vertical para aprovechar cada centimetro.',
    img: 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=1200&q=80',
  },
  {
    categoria: 'integral',
    titulo: 'Reforma de salon y comedor',
    descripcion: 'Unificacion visual con nuevos pavimentos y paleta neutra para mayor amplitud.',
    img: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
  },
];

const proyectosFiltrados = computed(() => {
  if (categoriaActiva.value === 'todos') return proyectos;
  return proyectos.filter((p) => p.categoria === categoriaActiva.value);
});
</script>

<template>
  <div>
    <PageHero
      title="Nuestros Proyectos"
      text="Explora una seleccion de proyectos de cocina, bano y reforma integral realizados en Valencia."
      image="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-center gap-3 mb-12 flex-wrap">
          <button
            class="px-6 py-2 rounded-lg uppercase text-sm font-bold transition-all"
            :class="categoriaActiva === 'todos' ? 'bg-primary text-white' : 'border-2 border-primary text-primary hover:bg-primary hover:text-white'"
            @click="categoriaActiva = 'todos'"
          >
            Todos
          </button>
          <button
            class="px-6 py-2 rounded-lg uppercase text-sm font-bold transition-all"
            :class="categoriaActiva === 'cocina' ? 'bg-primary text-white' : 'border-2 border-primary text-primary hover:bg-primary hover:text-white'"
            @click="categoriaActiva = 'cocina'"
          >
            Cocina
          </button>
          <button
            class="px-6 py-2 rounded-lg uppercase text-sm font-bold transition-all"
            :class="categoriaActiva === 'banos' ? 'bg-primary text-white' : 'border-2 border-primary text-primary hover:bg-primary hover:text-white'"
            @click="categoriaActiva = 'banos'"
          >
            Banos
          </button>
          <button
            class="px-6 py-2 rounded-lg uppercase text-sm font-bold transition-all"
            :class="categoriaActiva === 'integral' ? 'bg-primary text-white' : 'border-2 border-primary text-primary hover:bg-primary hover:text-white'"
            @click="categoriaActiva = 'integral'"
          >
            Integral
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <article
            v-for="proyecto in proyectosFiltrados"
            :key="proyecto.titulo"
            class="group rounded-xl overflow-hidden bg-white border border-zinc-200 shadow-lg transition-transform hover:-translate-y-2"
          >
            <div class="h-56 overflow-hidden">
              <img :alt="proyecto.titulo" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" :src="proyecto.img" />
            </div>
            <div class="p-6">
              <h3 class="text-lg font-bold uppercase tracking-wide text-zinc-900 mb-3">{{ proyecto.titulo }}</h3>
              <p class="text-sm leading-relaxed text-zinc-700">{{ proyecto.descripcion }}</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <div class="text-center pb-16">
      <RouterLink
        to="/contacto"
        class="inline-block border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors px-8 py-3 uppercase tracking-widest text-sm font-bold"
      >
        Ver mas
      </RouterLink>
    </div>
  </div>
</template>
