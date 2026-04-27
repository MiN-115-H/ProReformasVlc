<script setup>
import PageHero from '../components/PageHero.vue';

const enviarFormulario = () => {
  alert('Mensaje enviado correctamente. Te contactaremos pronto.');
};
</script>

<template>
  <div>
    <PageHero
      title="Contacto"
      text="Cuentanos tu proyecto y te asesoramos con una propuesta de reforma adaptada a tus necesidades."
      image="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <div class="space-y-8">
            <h2 class="text-2xl font-bold uppercase tracking-widest mb-8">Informacion de Contacto</h2>
            <div class="flex items-start gap-6 group">
              <div class="shrink-0 w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl">call</span>
              </div>
              <div>
                <h3 class="font-bold text-lg text-primary mb-1">Telefono</h3>
                <p class="text-gray-600">+34 606 939 035</p>
                <p class="text-sm text-gray-500 mt-1">Disponible via WhatsApp</p>
              </div>
            </div>
            <div class="flex items-start gap-6 group">
              <div class="shrink-0 w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl">mail</span>
              </div>
              <div>
                <h3 class="font-bold text-lg text-primary mb-1">Correo Electronico</h3>
                <p class="text-gray-600">julian.proreformasvlc@gmail.com</p>
              </div>
            </div>
            <div class="flex items-start gap-6 group">
              <div class="shrink-0 w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl">location_on</span>
              </div>
              <div>
                <h3 class="font-bold text-lg text-primary mb-1">Direccion</h3>
                <p class="text-gray-600">C/ Torrente n18 Valencia<br />46014 Valencia, Espana</p>
                <p class="text-sm text-gray-500 mt-2">Lunes - Viernes: 09:00 - 18:30</p>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 p-8 rounded-lg border border-zinc-100">
            <h2 class="text-2xl font-bold uppercase tracking-widest mb-8">Envianos un mensaje</h2>
            <form class="space-y-6" @submit.prevent="enviarFormulario">
              <div>
                <label for="nombre" class="block text-sm font-semibold mb-2">Nombre</label>
                <input id="nombre" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Tu nombre" />
              </div>
              <div>
                <label for="email" class="block text-sm font-semibold mb-2">Correo Electronico</label>
                <input id="email" type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="tu@email.com" />
              </div>
              <div>
                <label for="telefono" class="block text-sm font-semibold mb-2">Telefono</label>
                <input id="telefono" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="+34 600..." />
              </div>
              <div>
                <label for="mensaje" class="block text-sm font-semibold mb-2">Mensaje</label>
                <textarea id="mensaje" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Cuentanos detalles de tu proyecto..."></textarea>
              </div>
              <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-lg font-bold uppercase transition-all">Enviar Mensaje</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
