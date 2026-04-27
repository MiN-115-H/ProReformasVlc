<template>
  <footer class="bg-black text-gray-400 py-16 px-6 md:px-12">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">
      <div class="space-y-6">
        <RouterLink class="inline-flex items-center" to="/">
          <img :src="'/img/logo.jpg'" alt="Pro Reformas Valencia" class="h-14 w-auto object-contain rounded-sm bg-white p-1" />
        </RouterLink>
        <p class="text-xs leading-relaxed">
          En <span class="font-bold text-white">Pro Reformas Valencia</span>, queremos que sus suenos se hagan realidad.
        </p>
      </div>

      <div>
        <h4 class="text-white font-bold uppercase tracking-widest text-sm mb-8 border-l-4 border-primary pl-4">Proyectos</h4>
        <ul class="space-y-3 text-xs uppercase tracking-wider">
          <li><RouterLink class="hover:text-primary transition-colors" to="/proyectos">Cocinas</RouterLink></li>
          <li><RouterLink class="hover:text-primary transition-colors" to="/proyectos">Banos</RouterLink></li>
          <li><RouterLink class="hover:text-primary transition-colors" to="/proyectos">Reforma Integral</RouterLink></li>
        </ul>
      </div>

      <div>
        <h4 class="text-white font-bold uppercase tracking-widest text-sm mb-8 border-l-4 border-primary pl-4">RGPD</h4>
        <ul class="space-y-3 text-xs uppercase tracking-wider">
          <li><a class="hover:text-primary transition-colors" href="#">Politica de Cookies</a></li>
          <li><a class="hover:text-primary transition-colors" href="#">Politica de Privacidad</a></li>
          <li><a class="hover:text-primary transition-colors" href="#">Aviso Legal</a></li>
        </ul>
      </div>

      <div>
        <h4 class="text-white font-bold uppercase tracking-widest text-sm mb-8 border-l-4 border-primary pl-4">Contacto</h4>
        <ul class="space-y-4 text-xs">
          <li class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-lg">location_on</span>
            <span>C/ Torrente n18 Valencia<br />46014 Valencia Espana</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-lg">call</span>
            <span>+34 606 939 035</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-lg">mail</span>
            <span>julian.proreformasvlc@gmail.com</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="mt-16 pt-8 border-t border-gray-800 flex justify-center items-center text-center text-[10px] uppercase tracking-[0.2em]">
      <p>© ProReformas Valencia 2024 | Todos los derechos reservados</p>
    </div>
  </footer>
</template>
