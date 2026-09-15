import { createRouter, createWebHistory } from 'vue-router';
import MainLayout from '../layouts/MainLayout.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { guestOnly: true }
  },
  {
    path: '/pendaftaran',
    name: 'public-psb',
    component: () => import('../views/PublicPsbView.vue'),
    meta: { public: true, title: 'Pendaftaran Santri Baru (PSB) Online' }
  },
  {
    path: '/',
    component: MainLayout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: { title: 'Dashboard Beranda' }
      },
      {
        path: 'santri',
        name: 'santri',
        component: () => import('../views/SantriView.vue'),
        meta: { title: 'Data Santri' }
      },
      {
        path: 'psb',
        name: 'psb',
        component: () => import('../views/PsbView.vue'),
        meta: { title: 'Penerimaan Santri Baru (PSB)', roles: ['pengurus', 'ustadz'] }
      },
      {
        path: 'perizinan',
        name: 'perizinan',
        component: () => import('../views/PerizinanView.vue'),
        meta: { title: 'Perizinan Santri', roles: ['pengurus', 'ustadz'] }
      },
      {
        path: 'hafalan',
        name: 'hafalan',
        component: () => import('../views/HafalanView.vue'),
        meta: { title: 'Hafalan & Tahfiz', roles: ['pengurus', 'ustadz'] }
      },
      {
        path: 'mutabaah',
        name: 'mutabaah',
        component: () => import('../views/MutabaahView.vue'),
        meta: { title: 'Mutabaah Yaumiyah' }
      },
      {
        path: 'donasi',
        name: 'donasi',
        component: () => import('../views/DonasiView.vue'),
        meta: { title: 'Donatur & Donasi', roles: ['pengurus'] }
      },
      {
        path: 'keuangan',
        name: 'keuangan',
        component: () => import('../views/KeuanganView.vue'),
        meta: { title: 'Keuangan Yayasan', roles: ['pengurus'] }
      },
      {
        path: 'kas-operasional',
        name: 'kas-operasional',
        component: () => import('../views/KasOperasionalView.vue'),
        meta: { title: 'Kas Operasional', roles: ['pengurus', 'ketua_santri'] }
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('../views/UsersView.vue'),
        meta: { title: 'Manajemen Pengguna', roles: ['pengurus'] }
      },
      {
        path: 'profil',
        name: 'profil',
        component: () => import('../views/ProfileView.vue'),
        meta: { title: 'Profil Saya' }
      }
    ]
  },
  {
    path: '/:catchAll(.*)*',
    redirect: '/dashboard'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('simtaq_token');
  const userStr = localStorage.getItem('simtaq_user');
  const user = userStr ? JSON.parse(userStr) : null;

  // Allow public routes without login
  if (to.meta.public) {
    return next();
  }

  if (to.meta.guestOnly && token) {
    return next({ name: 'dashboard' });
  }

  if (!to.meta.guestOnly && !token) {
    return next({ name: 'login' });
  }

  // Check role restrictions
  if (to.meta.roles && user) {
    if (!to.meta.roles.includes(user.role)) {
      return next({ name: 'dashboard' });
    }
  }

  next();
});

export default router;
