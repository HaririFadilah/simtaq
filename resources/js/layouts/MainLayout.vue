<template>
  <q-layout view="lHh Lpr lFf" class="bg-[#F4F7F6]">
    <!-- DESKTOP & MOBILE APP HEADER -->
    <q-header elevated class="bg-white text-grey-9 q-px-sm q-px-md-md q-py-xs shadow-1 main-header">
      <q-toolbar class="q-px-none header-toolbar">
        <!-- Toggle Sidebar (Desktop / Tablet / Mobile) -->
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          color="primary"
          @click="toggleLeftDrawer"
          class="q-mr-xs q-mr-sm-sm"
        />

        <!-- Brand Logo on Header -->
        <div class="row items-center cursor-pointer q-mr-xs q-mr-sm-md" @click="$router.push('/dashboard')">
          <img
            src="/assets/logo-header.jpeg"
            alt="SIMTAQ Header Logo"
            class="header-logo q-mr-xs q-mr-sm-sm"
          />
          <div class="column justify-center">
            <span class="text-weight-bold text-primary text-subtitle1 leading-tight brand-title">SIMTAQ</span>
            <span class="text-caption text-grey-6 text-weight-medium text-xs leading-none gt-xs brand-subtitle">Yayasan Al Mukhlisin</span>
          </div>
        </div>

        <q-space />

        <!-- User Role Badge & Profile Dropdown -->
        <div class="row items-center q-gutter-xs q-gutter-sm-sm">
          <!-- Role Chip (Desktop/Tablet) -->
          <q-chip
            :color="getRoleChipColor(authStore.role)"
            text-color="white"
            icon="verified_user"
            size="sm"
            class="text-weight-bold gt-xs"
          >
            {{ authStore.roleLabel }}
          </q-chip>

          <!-- User Menu Button -->
          <q-btn-dropdown
            flat
            no-caps
            dense
            rounded
            class="q-pa-xs user-dropdown-btn"
          >
            <template #label>
              <q-avatar size="32px" color="primary" text-color="white" class="text-weight-bold">
                {{ userInitials }}
              </q-avatar>
              <div class="gt-sm column items-start text-left q-ml-xs">
                <span class="text-weight-bold text-caption text-grey-9">{{ authStore.userName }}</span>
                <span class="text-grey-6 text-xs">{{ authStore.roleLabel }}</span>
              </div>
            </template>

            <q-list style="min-width: 230px" class="q-py-sm">
              <div class="q-px-md q-py-sm bg-grey-1">
                <div class="text-weight-bold text-grey-9 text-body2">{{ authStore.userName }}</div>
                <div class="text-caption text-grey-6 text-xs ellipsis">{{ authStore.user?.email }}</div>
                <q-chip dense size="xs" :color="getRoleChipColor(authStore.role)" text-color="white" class="q-mt-xs">
                  {{ authStore.roleLabel }}
                </q-chip>
              </div>

              <q-separator class="q-my-xs" />

              <q-item clickable v-close-popup to="/profil">
                <q-item-section avatar>
                  <q-icon name="person" color="primary" />
                </q-item-section>
                <q-item-section>Profil Saya</q-item-section>
              </q-item>

              <q-item clickable v-close-popup @click="handleLogout" class="text-negative">
                <q-item-section avatar>
                  <q-icon name="logout" color="negative" />
                </q-item-section>
                <q-item-section class="text-weight-bold">Keluar Sistem</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </q-toolbar>
    </q-header>

    <!-- SIDEBAR DRAWER (Desktop persistent, Mobile sliding sheet) -->
    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      :width="270"
      :breakpoint="1024"
      class="bg-white"
    >
      <div class="column full-height">
        <!-- Sidebar Brand Banner -->
        <div class="q-pa-md bg-emerald-gradient text-white row items-center q-gutter-md">
          <q-avatar size="44px" class="bg-white shadow-2">
            <img src="/assets/logo-header.jpeg" alt="Logo Yayasan" />
          </q-avatar>
          <div class="column">
            <span class="text-weight-bolder text-subtitle1 leading-tight text-white">SIMTAQ</span>
            <span class="text-caption text-emerald-100 text-xs">Sistem Santri & Tahfiz</span>
          </div>
        </div>

        <!-- Navigation Links Grouped -->
        <q-scroll-area class="col q-py-sm">
          <q-list padding class="menu-list">
            <!-- UTAMA -->
            <q-item-label header class="text-weight-bold text-grey-6 text-uppercase text-xs q-pt-sm">
              Menu Utama
            </q-item-label>

            <q-item clickable v-ripple to="/dashboard" active-class="menu-active" @click="closeDrawerOnMobile">
              <q-item-section avatar>
                <q-icon name="dashboard" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Dashboard</q-item-section>
            </q-item>

            <q-item clickable v-ripple to="/santri" active-class="menu-active" @click="closeDrawerOnMobile">
              <q-item-section avatar>
                <q-icon name="groups" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Data Santri</q-item-section>
            </q-item>

            <!-- KESANTRIAN & AKADEMIK -->
            <q-item-label
              v-if="canViewKesantrian"
              header
              class="text-weight-bold text-grey-6 text-uppercase text-xs q-pt-md"
            >
              Kesantrian & Tahfiz
            </q-item-label>

            <q-item
              v-if="authStore.isPengurus || authStore.isUstadz"
              clickable
              v-ripple
              to="/psb"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="how_to_reg" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Penerimaan (PSB)</q-item-section>
            </q-item>

            <q-item
              v-if="authStore.isPengurus || authStore.isUstadz"
              clickable
              v-ripple
              to="/perizinan"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="assignment_turned_in" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Perizinan Santri</q-item-section>
            </q-item>

            <q-item
              v-if="authStore.isPengurus || authStore.isUstadz"
              clickable
              v-ripple
              to="/hafalan"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="menu_book" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Hafalan & Tahfiz</q-item-section>
            </q-item>

            <q-item clickable v-ripple to="/mutabaah" active-class="menu-active" @click="closeDrawerOnMobile">
              <q-item-section avatar>
                <q-icon name="checklist" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Mutabaah Yaumiyah</q-item-section>
            </q-item>

            <!-- KEUANGAN & DONASI -->
            <q-item-label
              v-if="canViewFinance"
              header
              class="text-weight-bold text-grey-6 text-uppercase text-xs q-pt-md"
            >
              Keuangan & Donasi
            </q-item-label>

            <q-item
              v-if="authStore.isPengurus"
              clickable
              v-ripple
              to="/donasi"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="volunteer_activism" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Donatur & Donasi</q-item-section>
            </q-item>

            <q-item
              v-if="authStore.isPengurus"
              clickable
              v-ripple
              to="/keuangan"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="account_balance" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Keuangan Yayasan</q-item-section>
            </q-item>

            <q-item
              v-if="authStore.isPengurus || authStore.isKetuaSantri"
              clickable
              v-ripple
              to="/kas-operasional"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="payments" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Kas Operasional</q-item-section>
            </q-item>

            <!-- PENGATURAN -->
            <q-item-label
              v-if="authStore.isPengurus"
              header
              class="text-weight-bold text-grey-6 text-uppercase text-xs q-pt-md"
            >
              Administrasi Sistem
            </q-item-label>

            <q-item
              v-if="authStore.isPengurus"
              clickable
              v-ripple
              to="/users"
              active-class="menu-active"
              @click="closeDrawerOnMobile"
            >
              <q-item-section avatar>
                <q-icon name="admin_panel_settings" />
              </q-item-section>
              <q-item-section class="text-weight-medium">Manajemen Pengguna</q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>

        <!-- Sidebar Footer -->
        <q-separator />
        <div class="q-pa-sm bg-grey-1 row items-center justify-between">
          <div class="column">
            <span class="text-xs text-grey-7 text-weight-bold">SIMTAQ v1.0 PWA</span>
            <span class="text-caption text-grey-5" style="font-size: 10px;">Al Mukhlisin &copy; 2026</span>
          </div>
          <q-btn
            flat
            round
            dense
            color="negative"
            icon="logout"
            @click="handleLogout"
          >
            <q-tooltip>Keluar</q-tooltip>
          </q-btn>
        </div>
      </div>
    </q-drawer>

    <!-- PAGE CONTAINER WITH ADAPTIVE MOBILE PADDING -->
    <q-page-container class="mobile-adaptive-container">
      <router-view v-slot="{ Component }">
        <transition name="fade-slide" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>

    <!-- ADAPTIVE MOBILE BOTTOM NAVIGATION (Khusus Tampilan Mobile / PWA Handheld) -->
    <div class="lt-md mobile-bottom-bar shadow-10 border-top">
      <div class="row no-wrap items-center justify-around full-width nav-row">
        <!-- 1. Beranda -->
        <button
          type="button"
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/dashboard' }"
          @click="$router.push('/dashboard')"
        >
          <q-icon name="dashboard" size="21px" />
          <span>Beranda</span>
        </button>

        <!-- 2. Santri -->
        <button
          type="button"
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/santri' }"
          @click="$router.push('/santri')"
        >
          <q-icon name="groups" size="21px" />
          <span>Santri</span>
        </button>

        <!-- 3. Mutabaah (Quick center thumb action) -->
        <button
          type="button"
          class="mobile-nav-item center-fab"
          :class="{ 'active': $route.path === '/mutabaah' }"
          @click="$router.push('/mutabaah')"
        >
          <div class="fab-circle bg-primary text-white shadow-4">
            <q-icon name="checklist" size="24px" />
          </div>
          <span class="fab-label">Mutabaah</span>
        </button>

        <!-- 4. Tahfiz / Kas (Adaptive by role) -->
        <button
          v-if="authStore.isKetuaSantri"
          type="button"
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/kas-operasional' }"
          @click="$router.push('/kas-operasional')"
        >
          <q-icon name="payments" size="21px" />
          <span>Kas</span>
        </button>
        <button
          v-else
          type="button"
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/hafalan' }"
          @click="$router.push('/hafalan')"
        >
          <q-icon name="menu_book" size="21px" />
          <span>Tahfiz</span>
        </button>

        <!-- 5. Menu Drawer Trigger -->
        <button
          type="button"
          class="mobile-nav-item"
          @click="toggleLeftDrawer"
        >
          <q-icon name="grid_view" size="21px" />
          <span>Menu</span>
        </button>
      </div>
    </div>
  </q-layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useAuthStore } from '../stores/auth';
import { confirmDialog } from '../utils/sweetalert';

const $q = useQuasar();
const authStore = useAuthStore();
const leftDrawerOpen = ref(false);

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value;
};

const closeDrawerOnMobile = () => {
  if ($q.screen.lt.md) {
    leftDrawerOpen.value = false;
  }
};

const userInitials = computed(() => {
  const name = authStore.userName || 'U';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
});

const canViewKesantrian = computed(() => {
  return authStore.isPengurus || authStore.isUstadz || authStore.isKetuaSantri;
});

const canViewFinance = computed(() => {
  return authStore.isPengurus || authStore.isKetuaSantri;
});

const getRoleChipColor = (role) => {
  switch (role) {
    case 'pengurus': return 'primary';
    case 'ustadz': return 'teal-7';
    case 'ketua_santri': return 'amber-9';
    default: return 'grey-7';
  }
};

const handleLogout = async () => {
  const result = await confirmDialog({
    title: 'Keluar dari SIMTAQ?',
    text: 'Sesi Anda saat ini akan diakhiri.',
    confirmButtonText: 'Ya, Keluar',
    cancelButtonText: 'Batal',
    icon: 'question'
  });

  if (result.isConfirmed) {
    authStore.logout();
  }
};
</script>

<style scoped>
.main-header {
  padding-top: max(4px, env(safe-area-inset-top));
}

.header-logo {
  height: 36px;
  width: auto;
  border-radius: 6px;
  object-fit: contain;
}

.bg-emerald-gradient {
  background: linear-gradient(135deg, #0D7C66 0%, #006A67 100%);
}

.menu-list .q-item {
  border-radius: 10px;
  margin: 2px 10px;
  color: #4B5563;
  transition: all 0.2s ease;
}

.menu-list .q-item:hover {
  background-color: #E8F5F1;
  color: #0D7C66;
}

.menu-active {
  background-color: #E0F2E9 !important;
  color: #0D7C66 !important;
  font-weight: 600;
}

.text-xs {
  font-size: 0.72rem;
}

/* Page Transition */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ADAPTIVE MOBILE BOTTOM NAVIGATION */
.mobile-bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 2000;
  height: calc(62px + env(safe-area-inset-bottom, 0px));
  padding-bottom: env(safe-area-inset-bottom, 0px);
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-top: 1px solid rgba(226, 232, 240, 0.9);
}

.nav-row {
  height: 60px;
}

.mobile-nav-item {
  background: transparent;
  border: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #64748B;
  font-size: 10.5px;
  font-weight: 500;
  padding: 4px 6px;
  cursor: pointer;
  outline: none;
  flex: 1;
  max-width: 72px;
  transition: color 0.15s ease, transform 0.15s ease;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.mobile-nav-item.active {
  color: #0D7C66;
  font-weight: 700;
}

.mobile-nav-item:active {
  transform: scale(0.92);
}

.center-fab {
  position: relative;
  top: -12px;
}

.fab-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2px;
  border: 3px solid #FFFFFF;
  box-shadow: 0 4px 10px rgba(13, 124, 102, 0.35);
  transition: transform 0.15s ease;
}

.fab-circle:active {
  transform: scale(0.92);
}

.fab-label {
  font-size: 10px;
}

@media (max-width: 1023px) {
  .mobile-adaptive-container {
    padding-bottom: calc(74px + env(safe-area-inset-bottom, 0px)) !important;
  }
}
</style>
