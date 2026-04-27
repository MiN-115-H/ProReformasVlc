<script setup>
import PageHero from '../components/PageHero.vue';

const servicios = [
  'Albanileria',
  'Carpinteria',
  'Fontaneria',
  'Electricidad',
  'Pintura',
  'Gas y calefaccion',
  'Aire acondicionado',
  'Cristaleria',
  'Persianas',
  'Cerrajeria',
  'Acero inoxidable',
  'Electrodomesticos',
];
</script>

<template>
  <div>
    <PageHero
      title="Nuestros Servicios"
      text="Servicios especializados para cubrir todas las fases de una reforma integral en Valencia."
      image="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="max-w-7xl mx-auto px-6 py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <article v-for="servicio in servicios" :key="servicio" class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
        <h3 class="font-bold uppercase tracking-wide text-zinc-900">{{ servicio }}</h3>
      </article>
    </section>
  </div>
</template>
