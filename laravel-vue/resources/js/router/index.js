import { createRouter, createWebHistory } from 'vue-router';

import HomePage from '../pages/HomePage.vue';
import EmpresaPage from '../pages/EmpresaPage.vue';
import ServiciosPage from '../pages/ServiciosPage.vue';
import ProyectosPage from '../pages/ProyectosPage.vue';
import PresupuestosPage from '../pages/PresupuestosPage.vue';
import ContactoPage from '../pages/ContactoPage.vue';
import AdminPanelPage from '../pages/AdminPanelPage.vue';
import LoginPage from '../pages/LoginPage.vue';

const routes = [
  { path: '/', name: 'inicio', component: HomePage },
  { path: '/empresa', name: 'empresa', component: EmpresaPage },
  { path: '/servicios', name: 'servicios', component: ServiciosPage },
  { path: '/proyectos', name: 'proyectos', component: ProyectosPage },
  { path: '/presupuestos', name: 'presupuestos', component: PresupuestosPage },
  { path: '/contacto', name: 'contacto', component: ContactoPage },
  { path: '/login', name: 'login', component: LoginPage, meta: { minimalLayout: true, guestOnly: true } },
  { path: '/admin/panel', name: 'admin-panel', component: AdminPanelPage, meta: { requiresAdmin: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

const getCurrentUser = async () => {
  const response = await fetch('/auth/me', {
    headers: { Accept: 'application/json' },
  });

  if (!response.ok) {
    return null;
  }

  const payload = await response.json().catch(() => null);
  return payload?.user ?? null;
};

router.beforeEach(async (to) => {
  if (to.meta.requiresAdmin) {
    const user = await getCurrentUser();
    if (!user || user.rol !== 'admin' || !user.activo) {
      return { name: 'login', query: { next: to.fullPath } };
    }
  }

  if (to.meta.guestOnly) {
    const user = await getCurrentUser();
    if (user && user.rol === 'admin' && user.activo) {
      return { name: 'admin-panel' };
    }
  }

  return true;
});

export default router;
