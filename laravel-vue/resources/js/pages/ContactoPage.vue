<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageHero from '../components/PageHero.vue';

const router = useRouter();
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const recaptchaSiteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY || '';
const recaptchaAction = 'contact_submit';

if (!window.grecaptcha) {
  window.grecaptcha = undefined;
}

const enviando = ref(false);
const error = ref('');

const form = reactive({
  nombre: '',
  email: '',
  telefono: '',
  asunto: '',
  mensaje: '',
  website: '',
});

const resetForm = () => {
  form.nombre = '';
  form.email = '';
  form.telefono = '';
  form.asunto = '';
  form.mensaje = '';
  form.website = '';
};

const loadRecaptchaScript = () => {
  if (!recaptchaSiteKey) return Promise.resolve();
  if (window.grecaptcha?.ready) return Promise.resolve();

  return new Promise((resolve, reject) => {
    const existing = document.querySelector('script[data-recaptcha="v3"]');
    if (existing) {
      existing.addEventListener('load', () => resolve(), { once: true });
      existing.addEventListener('error', () => reject(new Error('No se pudo cargar reCAPTCHA.')), { once: true });
      return;
    }

    const script = document.createElement('script');
    script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(recaptchaSiteKey)}`;
    script.async = true;
    script.defer = true;
    script.dataset.recaptcha = 'v3';
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('No se pudo cargar reCAPTCHA.'));
    document.head.appendChild(script);
  });
};

const getRecaptchaToken = async () => {
  if (!recaptchaSiteKey) return null;

  await loadRecaptchaScript();

  if (!window.grecaptcha?.ready) {
    throw new Error('No se pudo inicializar reCAPTCHA.');
  }

  return new Promise((resolve, reject) => {
    window.grecaptcha.ready(async () => {
      try {
        const token = await window.grecaptcha.execute(recaptchaSiteKey, { action: recaptchaAction });
        resolve(token || null);
      } catch (e) {
        reject(new Error('No se pudo verificar la seguridad del formulario.'));
      }
    });
  });
};

const enviarFormulario = async () => {
  if (enviando.value) return;

  error.value = '';

  if (form.mensaje.trim().length < 10) {
    error.value = 'El mensaje debe tener al menos 10 caracteres.';
    return;
  }

  enviando.value = true;

  try {
    const recaptchaToken = await getRecaptchaToken();

    const response = await fetch('/api/contactos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        nombre: form.nombre.trim(),
        email: form.email.trim(),
        telefono: form.telefono.trim() || null,
        asunto: form.asunto.trim() || null,
        mensaje: form.mensaje.trim(),
        website: form.website,
        recaptcha_token: recaptchaToken,
      }),
    });

    if (!response.ok) {
      const payload = await response.json().catch(() => ({}));
      if (response.status === 429) {
        throw new Error('Demasiados intentos seguidos. Espera un minuto y vuelve a intentarlo.');
      }
      if (payload?.errors) {
        const firstError = Object.values(payload.errors)?.[0]?.[0];
        throw new Error(firstError || 'Revisa los datos del formulario.');
      }
      throw new Error(payload?.message || 'No se pudo enviar el formulario.');
    }

    resetForm();
    router.push({ name: 'contacto-enviado' });
  } catch (e) {
    error.value = e.message || 'No se pudo enviar el formulario.';
  } finally {
    enviando.value = false;
  }
};
</script>

<template>
  <div>
    <PageHero
      title="Contacto"
      text="Cuéntanos tu proyecto y te asesoramos con una propuesta de reforma adaptada a tus necesidades."
      image="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?auto=format&fit=crop&w=1800&q=80"
    />
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <div class="space-y-8">
            <h2 class="text-2xl font-bold uppercase tracking-widest mb-8">Información de Contacto</h2>
            <div class="flex items-start gap-6 group">
              <div class="shrink-0 w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl">call</span>
              </div>
              <div>
                <h3 class="font-bold text-lg text-primary mb-1">Teléfono</h3>
                <p class="text-gray-600">+34 606 939 035</p>
                <p class="text-sm text-gray-500 mt-1">Disponible via WhatsApp</p>
              </div>
            </div>
            <div class="flex items-start gap-6 group">
              <div class="shrink-0 w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl">mail</span>
              </div>
              <div>
                <h3 class="font-bold text-lg text-primary mb-1">Correo Electrónico</h3>
                <p class="text-gray-600">julian.proreformasvlc@gmail.com</p>
              </div>
            </div>
            <div class="flex items-start gap-6 group">
              <div class="shrink-0 w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl">location_on</span>
              </div>
              <div>
                <h3 class="font-bold text-lg text-primary mb-1">Dirección</h3>
                <p class="text-gray-600">C/ Torrente n18 Valencia<br />46014 Valencia, España</p>
                <p class="text-sm text-gray-500 mt-2">Lunes - Viernes: 09:00 - 18:30</p>
              </div>
            </div>

            <div class="rounded-lg overflow-hidden border border-gray-200 shadow-sm">
              <iframe
                title="Ubicación ProReformasVLC"
                src="https://www.google.com/maps?q=C%2F%20Torrente%20n18%2C%2046014%20Valencia&output=embed"
                width="100%"
                height="300"
                style="border: 0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
            </div>
          </div>

          <div class="bg-gray-50 p-8 rounded-lg border border-zinc-100">
            <h2 class="text-2xl font-bold uppercase tracking-widest mb-8">Envíanos un mensaje</h2>
            <form class="space-y-6" @submit.prevent="enviarFormulario">
              <input
                v-model="form.website"
                type="text"
                name="website"
                tabindex="-1"
                autocomplete="off"
                class="hidden"
                aria-hidden="true"
              />
              <p v-if="error" class="text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm font-semibold">
                {{ error }}
              </p>
              <div>
                <label for="nombre" class="block text-sm font-semibold mb-2">Nombre</label>
                <input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  minlength="3"
                  maxlength="100"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                  placeholder="Tu nombre"
                />
              </div>
              <div>
                <label for="email" class="block text-sm font-semibold mb-2">Correo Electrónico</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  maxlength="150"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                  placeholder="tu@email.com"
                />
              </div>
              <div>
                <label for="telefono" class="block text-sm font-semibold mb-2">Teléfono</label>
                <input
                  id="telefono"
                  v-model="form.telefono"
                  type="tel"
                  pattern="^\+?[0-9\s-]{8,20}$"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                  placeholder="+34 600..."
                />
              </div>
              <div>
                <label for="asunto" class="block text-sm font-semibold mb-2">Asunto</label>
                <input
                  id="asunto"
                  v-model="form.asunto"
                  type="text"
                  maxlength="150"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                  placeholder="Consulta sobre reforma..."
                />
              </div>
              <div>
                <label for="mensaje" class="block text-sm font-semibold mb-2">Mensaje</label>
                <textarea
                  id="mensaje"
                  v-model="form.mensaje"
                  rows="5"
                  required
                  minlength="10"
                  maxlength="4000"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                  placeholder="Cuéntanos detalles de tu proyecto..."
                ></textarea>
              </div>
              <button
                type="submit"
                :disabled="enviando"
                class="w-full bg-primary hover:bg-primary/90 disabled:opacity-60 disabled:cursor-not-allowed text-white py-3 rounded-lg font-bold uppercase transition-all"
              >
                {{ enviando ? 'Enviando...' : 'Enviar Mensaje' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
