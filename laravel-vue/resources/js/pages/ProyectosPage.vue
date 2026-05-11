<script setup>
import { computed, onMounted, ref } from 'vue';
import PageHero from '../components/PageHero.vue';

const albums = ref([]);
const loading = ref(true);
const categoriaActiva = ref('Todos');
const busqueda = ref('');
const limiteVisible = ref(6);

const selectedAlbum = ref(null);
const selectedFotoIndex = ref(0);

const loadAlbums = async () => {
  loading.value = true;
  try {
    const response = await fetch('/api/albums');
    albums.value = await response.json();
  } catch (error) {
    console.error('Error loading albums:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(loadAlbums);

const categoriasDisponibles = computed(() => {
  const cats = new Set(albums.value.map(a => a.categoria).filter(c => c));
  return ['Todos', ...Array.from(cats)];
});

const albumsFiltrados = computed(() => {
  return albums.value.filter(album => {
    const matchCat = categoriaActiva.value === 'Todos' || album.categoria === categoriaActiva.value;
    const matchSearch = album.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) || 
                        (album.descripcion && album.descripcion.toLowerCase().includes(busqueda.value.toLowerCase()));
    return matchCat && matchSearch;
  });
});

const albumsVisibles = computed(() => albumsFiltrados.value.slice(0, limiteVisible.value));
const hayMasAlbums = computed(() => albumsFiltrados.value.length > limiteVisible.value);

const verMasAlbums = () => {
  limiteVisible.value += 6;
};

const openAlbum = (album) => {
  if (album.fotos && album.fotos.length > 0) {
    selectedAlbum.value = album;
    selectedFotoIndex.value = 0;
    document.body.style.overflow = 'hidden';
  } else {
    alert('Este álbum aún no tiene fotos.');
  }
};

const closeAlbum = () => {
  selectedAlbum.value = null;
  document.body.style.overflow = '';
};

const nextFoto = () => {
  if (selectedAlbum.value && selectedFotoIndex.value < selectedAlbum.value.fotos.length - 1) {
    selectedFotoIndex.value++;
  }
};

const prevFoto = () => {
  if (selectedAlbum.value && selectedFotoIndex.value > 0) {
    selectedFotoIndex.value--;
  }
};

</script>

<template>
  <div>
    <PageHero
      title="Nuestros Proyectos"
      text="Explora nuestra galería de reformas organizadas por álbumes. Haz clic en uno para ver todas las fotos."
      image="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="py-20 bg-white min-h-[500px]">
      <div class="max-w-7xl mx-auto px-6">
        
        <div v-if="loading" class="text-center text-gray-500 py-10">
          Cargando proyectos...
        </div>

        <div v-else>
          <!-- Buscador y Filtros -->
          <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-12">
            <input 
              v-model="busqueda" 
              type="text" 
              placeholder="Buscar proyectos..." 
              class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
            />

            <div class="flex flex-wrap justify-center gap-2">
              <button
                v-for="cat in categoriasDisponibles"
                :key="cat"
                class="px-4 py-2 rounded-lg uppercase text-xs font-bold transition-all"
                :class="categoriaActiva === cat ? 'bg-primary text-white' : 'border border-primary text-primary hover:bg-primary hover:text-white'"
                @click="categoriaActiva = cat"
              >
                {{ cat }}
              </button>
            </div>
          </div>

          <div v-if="albumsFiltrados.length === 0" class="text-center text-gray-500 py-10">
            No se encontraron álbumes con esos criterios.
          </div>

          <!-- Cuadrícula de Álbumes -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <article
              v-for="album in albumsVisibles"
              :key="album.id"
              class="group rounded-xl overflow-hidden bg-white border border-zinc-200 shadow-lg cursor-pointer transition-transform hover:-translate-y-2 relative"
              @click="openAlbum(album)"
            >
              <div class="h-56 overflow-hidden bg-gray-100 flex items-center justify-center">
                <img 
                  v-if="album.fotos && album.fotos.length > 0"
                  :alt="album.nombre" 
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                  :src="album.fotos[0].url" 
                />
                <div v-else class="text-gray-400">Sin fotos</div>
              </div>
              <div class="p-6 relative">
                <span v-if="album.categoria" class="absolute top-0 right-6 -translate-y-1/2 bg-primary text-white text-[10px] uppercase font-bold px-2 py-1 rounded">
                  {{ album.categoria }}
                </span>
                <h3 class="text-lg font-bold uppercase tracking-wide text-zinc-900 mb-2">{{ album.nombre }}</h3>
                <p class="text-sm leading-relaxed text-zinc-700 line-clamp-2">{{ album.descripcion }}</p>
                <div class="mt-4 text-xs font-bold text-primary uppercase">
                  Ver galería ({{ album.fotos?.length || 0 }} fotos) &rarr;
                </div>
              </div>
            </article>
          </div>

          <div v-if="hayMasAlbums" class="text-center pb-16 mt-12">
            <button
              type="button"
              class="inline-block border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors px-8 py-3 uppercase tracking-widest text-sm font-bold"
              @click="verMasAlbums"
            >
              Ver más
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal de Galería de Fotos -->
    <div v-if="selectedAlbum" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm">
      <button 
        @click="closeAlbum"
        class="absolute top-4 right-4 text-white hover:text-gray-300 text-4xl font-light z-50 focus:outline-none"
      >
        &times;
      </button>

      <div class="relative w-full max-w-5xl h-[80vh] flex flex-col">
        <!-- Contenedor Principal de la Imagen -->
        <div class="flex-1 relative flex items-center justify-center overflow-hidden">
          <img 
            :src="selectedAlbum.fotos[selectedFotoIndex].url" 
            class="max-w-full max-h-full object-contain select-none"
            :alt="selectedAlbum.fotos[selectedFotoIndex].descripcion || selectedAlbum.nombre"
          />
          
          <button 
            v-if="selectedFotoIndex > 0"
            @click="prevFoto"
            class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full bg-black/50 text-white hover:bg-primary transition-colors focus:outline-none"
          >
            &#10094;
          </button>
          
          <button 
            v-if="selectedFotoIndex < selectedAlbum.fotos.length - 1"
            @click="nextFoto"
            class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full bg-black/50 text-white hover:bg-primary transition-colors focus:outline-none"
          >
            &#10095;
          </button>
        </div>

        <!-- Info y Thumbnails -->
        <div class="mt-4 shrink-0">
          <div class="text-center text-white mb-4">
            <h4 class="text-xl font-bold uppercase">{{ selectedAlbum.nombre }}</h4>
            <p v-if="selectedAlbum.fotos[selectedFotoIndex].descripcion" class="text-sm mt-1 text-gray-300">
              {{ selectedAlbum.fotos[selectedFotoIndex].descripcion }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              Foto {{ selectedFotoIndex + 1 }} de {{ selectedAlbum.fotos.length }}
            </p>
          </div>

          <div class="flex justify-center gap-2 overflow-x-auto pb-2">
            <button 
              v-for="(foto, index) in selectedAlbum.fotos" 
              :key="foto.id"
              @click="selectedFotoIndex = index"
              class="w-16 h-16 shrink-0 rounded overflow-hidden border-2 transition-colors"
              :class="index === selectedFotoIndex ? 'border-primary opacity-100' : 'border-transparent opacity-50 hover:opacity-100'"
            >
              <img :src="foto.url" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
