<script setup>
import PageHero from '../components/PageHero.vue';
</script>

<template>
  <div>
    <PageHero
      title="Nuestra Empresa"
      text="En Pro Reformas Valencia, transformamos espacios y cumplimos suenos. Somos referentes en reformas integrales en toda la Comunidad Valenciana."
      image="https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1800&q=80"
    />

    <section class="py-20 bg-background-light" id="historia">
      <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-12 items-center">
          <div class="lg:w-1/2">
            <h2 class="text-3xl font-black uppercase tracking-widest text-zinc-800 mb-2">Nuestra Historia</h2>
            <div class="w-24 h-1 bg-primary mb-8"></div>
            <div class="space-y-6 text-zinc-600 leading-relaxed">
              <p>Nacimos de una pasion compartida por la arquitectura y el diseno de interiores.</p>
              <p>Nuestra trayectoria se basa en la transparencia y la honestidad en cada reforma.</p>
              <p>Trabajamos con proveedores de confianza para garantizar calidad y plazos de entrega.</p>
            </div>
          </div>
          <div class="lg:w-1/2 grid grid-cols-2 gap-4">
            <img alt="Work Process" class="rounded shadow-lg w-full h-64 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBv5NN23xrl_aH87ikna2WU1nEHZ4zLuiopn9SVEzpwDx9utClhirz-EGICiDYXd_-h3uXW7WjKbiRoWVi7zpTxOEe5h8kqsxl7rMwGvG944k7TOwJ7QLwrML46IYpLPMKGHJ1fCfWJj3zGnBVEMawfBn8HAe7XLVXUE5jtyLqP2lpzFtaS46PliJDa8HXVOX91gqbTplQAYxKIp8YXZ6L5OY-gs1PRG6AO_vESC5u1R3EJLf3uCSGcbZ6VBO4htQfq1OdzRndbOUpr" />
            <img alt="Office Work" class="rounded shadow-lg w-full h-64 object-cover mt-8" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSbOmiZ6hT44K0p5I1kfDLA1m35r7WKpK0riHRAkYigJhBSytPQ43C91OBGzxUu8Rlre9uQnU5aRUh_JXqwSl0iNHJxWIVBuUy0UNAGklSkrQMGyCHdxujJwZl9GN8KEdv8MFrCSHv3cSMxR5heFpY_wl4Wq-drdTsWcbsutsqrrihvpbM0xZLOKlZbBXs8WZIMazes7BmRcwdyR3UsOBVGsJQQtw_Hmwz2Vz6FJtgLoSFJRmfZTzN2Q0HWMs0NNd_zIRT6GhvDkLR" />
            <img alt="Construction detail" class="rounded shadow-lg w-full h-64 object-cover -mt-8" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCm-ScdbKumsj7LJakC4VITJV6nLx3DtHn-6fvJe-u_CwFiwOpxqKkBnVVKW86R6pLKf0hCTGWh8QQlQ87xm_FxWJTxYcHQ7atHCHjnuUrtNzjIaiA8qhpgeA29pln9IOCoabXvJ0sGTXJq1lTAeC6BydlSpCDPO4XJYmNpv9yJcSDjsRKyZqXn6QC9i-P1wiY3GRIMWZG00IKAodw6XUNWDmT7ciAE80in91dFwA5ttedFiU5S0ugou02tYIVgh0ZznIBdqsVxMYjt" />
            <img alt="Finished house" class="rounded shadow-lg w-full h-64 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAi5-Q8obtCQ_BUceWKSU4MPXm4Zj54-Y3eSdQVIwUWd6JVR34SlbSpy3ZDWqA8zbApHYOerQtH5mj1w_9mXjVTvvDtlHctWbZrNF-eWh_2iWZ3EU1F9YbyxIk6yEgwJ-NXPv_leSSvXbj9wcft3a-9zUv-iyUyMKLUklX-Q2ZZnfl1Fp9BY2oQgpzpPJBlgmpXbkLAsSaolBcanlNfPxqmBTvxkcaPEzncHSuFTn7esEZzJChBC0D5ShVQajX0t9W3puP6aP2HX6ii" />
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-zinc-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-black uppercase tracking-widest text-zinc-800">Nuestro Compromiso</h2>
          <div class="w-24 h-1 bg-primary mx-auto mt-4"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100 text-center">
            <div class="w-16 h-16 bg-zinc-50 border-2 border-primary flex items-center justify-center mx-auto mb-6"><i class="material-icons text-primary text-3xl">check</i></div>
            <h3 class="text-xl font-bold uppercase mb-4 text-zinc-800">Calidad</h3>
            <p class="text-zinc-600 text-sm leading-relaxed">Ofrecemos calidad, profesionalidad y seriedad para ganar la confianza de cada cliente.</p>
          </div>
          <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100 text-center">
            <div class="w-16 h-16 bg-zinc-50 border-2 border-primary flex items-center justify-center mx-auto mb-6"><i class="material-icons text-primary text-3xl">construction</i></div>
            <h3 class="text-xl font-bold uppercase mb-4 text-zinc-800">Servicio</h3>
            <p class="text-zinc-600 text-sm leading-relaxed">Servicios personalizados y adaptados a las necesidades concretas de cada proyecto.</p>
          </div>
          <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100 text-center">
            <div class="w-16 h-16 bg-zinc-50 border-2 border-primary flex items-center justify-center mx-auto mb-6"><i class="material-icons text-primary text-3xl">euro</i></div>
            <h3 class="text-xl font-bold uppercase mb-4 text-zinc-800">Precio</h3>
            <p class="text-zinc-600 text-sm leading-relaxed">Materiales de calidad a precios asequibles y presupuestos ajustados.</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
