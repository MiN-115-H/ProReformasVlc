<script setup>
defineProps({
  title: { type: String, required: true },
  text: { type: String, required: true },
  image: { type: String, required: true },
});
</script>

<template>
  <section
    class="min-h-[52vh] py-16 md:py-20 flex items-center bg-cover bg-center bg-no-repeat"
    :style="{ backgroundImage: `linear-gradient(to right, rgba(17,24,39,0.78), rgba(17,24,39,0.45)), url('${image}')` }"
  >
    <div class="container mx-auto px-4">
      <div class="max-w-2xl bg-black/45 backdrop-blur-sm p-8 md:p-10 border-l-4 border-emerald-700 shadow-2xl">
        <h1 class="text-4xl md:text-5xl font-black text-white mb-5 uppercase tracking-tight">{{ title }}</h1>
        <p class="text-white text-base md:text-lg leading-relaxed max-w-xl">{{ text }}</p>
      </div>
    </div>
  </section>
</template>
