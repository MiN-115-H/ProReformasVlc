import { createRouter, createWebHistory } from 'vue-router';

import HomePage from '../pages/HomePage.vue';
import EmpresaPage from '../pages/EmpresaPage.vue';
import ServiciosPage from '../pages/ServiciosPage.vue';
import ProyectosPage from '../pages/ProyectosPage.vue';
import PresupuestosPage from '../pages/PresupuestosPage.vue';
import ContactoPage from '../pages/ContactoPage.vue';

const routes = [
  { path: '/', name: 'inicio', component: HomePage },
  { path: '/empresa', name: 'empresa', component: EmpresaPage },
  { path: '/servicios', name: 'servicios', component: ServiciosPage },
  { path: '/proyectos', name: 'proyectos', component: ProyectosPage },
  { path: '/presupuestos', name: 'presupuestos', component: PresupuestosPage },
  { path: '/contacto', name: 'contacto', component: ContactoPage },
];

export default createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});
