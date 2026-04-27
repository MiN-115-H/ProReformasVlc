<script setup>
import PageHero from '../components/PageHero.vue';

const servicios = [
  {
    titulo: 'Albanileria',
    img: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=80',
    desc: 'Tabiques, alicatados, pavimentos, maestreado de yeso y barrecha, escayolas y molduras.',
  },
  {
    titulo: 'Carpinteria',
    img: 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=1200&q=80',
    desc: 'Puertas, armarios empotrados, suelos, tarimas, parquet y muebles a medida.',
  },
  {
    titulo: 'Fontaneria',
    img: 'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=1200&q=80',
    desc: 'Bajantes comunitarias, instalaciones multicapa, polipropileno y tuberias Wirsbo.',
  },
  {
    titulo: 'Electricidad',
    img: 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?auto=format&fit=crop&w=1200&q=80',
    desc: 'Instalaciones, boletines, cuadros automaticos, iluminacion y acometidas.',
  },
  {
    titulo: 'Pintura',
    img: 'https://images.unsplash.com/photo-1562224380-08f5d2c7f37d?auto=format&fit=crop&w=1200&q=80',
    desc: 'Esponjados, alisados, estuco veneciano, tierras florentinas y gotele.',
  },
  {
    titulo: 'Gas y Calefaccion',
    img: 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?auto=format&fit=crop&w=1200&q=80',
    desc: 'Instalaciones, boletines, calderas, calentadores, radiadores y toalleros.',
  },
  {
    titulo: 'Aire Acondicionado',
    img: 'https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?auto=format&fit=crop&w=1200&q=80',
    desc: 'Instalaciones, maquinaria y conductos para hogar, comercio e industria.',
  },
  {
    titulo: 'Cristaleria',
    img: 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80',
    desc: 'Mamparas, ventanales, correderas, abatibles, oscilobatientes y lucernarios.',
  },
  {
    titulo: 'Persianas',
    img: 'https://images.unsplash.com/photo-1523419409543-a5e549c1f7e8?auto=format&fit=crop&w=1200&q=80',
    desc: 'Enrollables de PVC o aluminio, motorizadas, de seguridad y mosquiteras.',
  },
  {
    titulo: 'Cerrajeria',
    img: 'https://images.unsplash.com/photo-1609941942332-4f0d8f7f5cde?auto=format&fit=crop&w=1200&q=80',
    desc: 'Puertas, ventanas, rejas fijas, abatibles, extensibles, barandillas y decoraciones.',
  },
  {
    titulo: 'Acero Inoxidable',
    img: 'https://images.unsplash.com/photo-1534972195531-d756b9bfa9f2?auto=format&fit=crop&w=1200&q=80',
    desc: 'Barandillas, puertas, ventanas, perfileria y trabajos a medida en inoxidable.',
  },
  {
    titulo: 'Electrodomesticos',
    img: 'https://images.unsplash.com/photo-1586208958839-06c17cacdf08?auto=format&fit=crop&w=1200&q=80',
    desc: 'Hornos, microondas, encimeras, lavadoras, secadoras, neveras, lavavajillas y campanas.',
  },
];
</script>

<template>
  <div>
    <PageHero
      title="Nuestros Servicios"
      text="Servicios especializados para cubrir todas las fases de una reforma integral en Valencia."
      image="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
        <article
          v-for="servicio in servicios"
          :key="servicio.titulo"
          class="group bg-white rounded-2xl overflow-hidden border border-gray-200 shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2"
        >
          <div class="relative h-56 overflow-hidden">
            <img :src="servicio.img" :alt="servicio.titulo" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
            <div class="absolute inset-0 bg-linear-to-t from-black/65 via-black/20 to-transparent"></div>
            <h3 class="absolute bottom-4 left-4 right-4 text-white text-lg font-extrabold uppercase tracking-wider">{{ servicio.titulo }}</h3>
          </div>
          <div class="p-6">
            <p class="text-gray-600 text-sm leading-relaxed">{{ servicio.desc }}</p>
          </div>
        </article>
      </div>
    </section>

    <div class="text-center pb-16">
      <RouterLink
        to="/contacto"
        class="inline-block border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors px-8 py-3 uppercase tracking-widest text-sm font-bold"
      >
        Solicitar Informacion
      </RouterLink>
    </div>
  </div>
</template>
