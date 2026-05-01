<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const submit = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetch('/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(form),
    });

    if (!response.ok) {
      const payload = await response.json().catch(() => null);
      throw new Error(payload?.message || 'No fue posible iniciar sesion.');
    }

    const next = typeof route.query.next === 'string' ? route.query.next : '/admin/panel';
    await router.push(next);
  } catch (requestError) {
    error.value = requestError.message;
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <main class="login-shell">
    <section class="login-card">
      <h1>Acceso administrador</h1>
      <p>Panel interno de ProReformasVLC</p>

      <form @submit.prevent="submit">
        <label for="email">Email</label>
        <input id="email" v-model="form.email" type="email" required autocomplete="username" />

        <label for="password">Contrasena</label>
        <input id="password" v-model="form.password" type="password" required autocomplete="current-password" />

        <button type="submit" :disabled="loading">
          {{ loading ? 'Accediendo...' : 'Entrar' }}
        </button>
      </form>

      <p v-if="error" class="error-msg">{{ error }}</p>
    </section>
  </main>
</template>

<style scoped>
.login-shell {
  min-height: calc(100vh - 90px);
  display: grid;
  place-items: center;
  padding: 1rem;
  background: radial-gradient(circle at 15% 15%, #eaf2fb 0%, #dfeaf4 35%, #c8dbea 100%);
}

.login-card {
  width: 100%;
  max-width: 440px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 14px 35px rgba(31, 61, 95, 0.18);
  padding: 2rem;
}

.login-card h1 {
  margin: 0;
  color: #1d3557;
  font-size: 1.7rem;
}

.login-card p {
  margin-top: 0.45rem;
  color: #4f6a86;
}

form {
  margin-top: 1.4rem;
  display: grid;
  gap: 0.55rem;
}

label {
  font-weight: 600;
  color: #1d3557;
}

input {
  border: 1px solid #bfd0e0;
  border-radius: 10px;
  padding: 0.72rem;
  font-size: 0.95rem;
}

button {
  margin-top: 0.85rem;
  border: 0;
  border-radius: 10px;
  padding: 0.8rem;
  background: #1d3557;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

button:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

button:hover:enabled {
  background: #2a9d8f;
}

.error-msg {
  margin-top: 1rem;
  color: #8a112b;
  background: #ffe9ee;
  border-radius: 10px;
  padding: 0.7rem;
}
</style>
