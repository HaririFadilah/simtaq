<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg dashboard-page">
    <!-- FULLSCREEN BRANDED LOGO LOADER -->
    <teleport to="body">
      <transition name="loader-fade">
        <div v-if="loading" class="fullscreen-loader-overlay flex flex-center">
          <div class="column items-center text-center q-pa-lg">
            <!-- Animated Logo with Outer Rotating Ring & Pulse Glow -->
            <div class="loader-logo-wrapper relative-position q-mb-md">
              <div class="loader-orbit-ring"></div>
              <div class="loader-pulse-glow"></div>
              <div class="loader-logo-box shadow-5">
                <img
                  src="/assets/logo-inti.jpeg"
                  alt="SIMTAQ Logo Loader"
                  class="loader-logo-img"
                />
              </div>
            </div>

            <!-- App Branding & Description with Motion -->
            <div
              v-motion
              :initial="{ opacity: 0, y: 15 }"
              :enter="{ opacity: 1, y: 0, transition: { duration: 500, ease: 'easeOut' } }"
              class="column items-center"
            >
              <div class="text-h5 text-weight-bolder text-primary tracking-wider leading-tight">
                SIMTAQ
              </div>
              <div class="text-caption text-grey-8 text-weight-medium q-mt-xs text-sm">
                Yayasan Al Mukhlisin
              </div>

              <!-- Elegant Slim Progress Bar -->
              <div class="loader-progress-track q-mt-lg">
                <div class="loader-progress-bar"></div>
              </div>

              <span class="text-caption text-grey-6 q-mt-sm text-xs loader-text-pulse">
                Memuat data santri & tahfiz...
              </span>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- HERO / BANNER WITH LOGO INTI & FRAMER MOTION -->
    <div
      v-motion
      :initial="{ opacity: 0, y: -20 }"
      :enter="{ opacity: 1, y: 0, transition: { duration: 400, ease: 'easeOut' } }"
      class="hero-banner q-pa-md q-pa-md-lg rounded-borders text-white q-mb-md q-mb-md-lg shadow-2"
    >
      <div class="row items-center justify-between q-col-gutter-sm q-col-gutter-md-md">
        <div class="row items-center no-wrap q-gutter-sm q-gutter-md-md col-12 col-md-8">
          <div class="hero-logo-box bg-white shadow-2 flex-shrink-0">
            <img src="/assets/logo-inti.jpeg" alt="Logo Inti SIMTAQ" class="hero-logo" />
          </div>
          <div class="column">
            <div class="row items-center q-gutter-xs">
              <span class="text-subtitle1 text-md-h6 text-weight-bolder leading-tight">
                Assalamu'alaikum, {{ authStore.userName }}
              </span>
              <q-badge color="amber-8" text-color="black" class="text-weight-bold q-ml-xs text-xs">
                {{ authStore.roleLabel }}
              </q-badge>
            </div>
            <div class="text-caption text-emerald-100 q-mt-xs text-xs-mobile">
              SIMTAQ Yayasan Al Mukhlisin &bull; Manajemen Santri & Tahfiz Terpadu
            </div>
          </div>
        </div>

        <div class="col-12 col-md-4 text-left text-md-right gt-sm">
          <div class="text-caption text-emerald-200">Waktu & Periode Aktif</div>
          <div class="text-subtitle2 text-weight-bold">{{ currentDateFormatted }}</div>
        </div>
      </div>
    </div>

    <!-- MAIN DASHBOARD CONTENT -->
    <div class="column q-gutter-y-md q-gutter-y-md-lg">
      <!-- 1. TOP 4 METRIC KPI CARDS (2x2 Grid on Mobile, 4-Cols on Desktop) -->
      <div class="row q-col-gutter-sm q-col-gutter-md-md">
        <!-- Metric 1: Total Santri -->
        <div class="col-6 col-md-3">
          <div
            v-motion
            :initial="{ opacity: 0, scale: 0.95 }"
            :enter="{ opacity: 1, scale: 1, transition: { delay: 100, duration: 350 } }"
            class="metric-card bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md cursor-pointer full-height column justify-between"
            @click="$router.push('/santri')"
          >
            <div class="row items-center justify-between no-wrap">
              <div class="column">
                <span class="text-caption text-grey-6 text-weight-medium text-xs">Total Santri</span>
                <span class="text-h5 text-md-h4 text-weight-bolder text-grey-9 q-my-xs">
                  {{ dashboardData.top_metrics?.santri?.total || 124 }}
                </span>
                <div class="row items-center text-xs text-positive text-nowrap">
                  <q-icon name="check_circle" size="13px" class="q-mr-xs" />
                  <span>{{ dashboardData.top_metrics?.santri?.aktif || 118 }} Aktif</span>
                </div>
              </div>
              <div class="metric-icon-box bg-emerald-light text-primary">
                <q-icon name="groups" size="24px" />
              </div>
            </div>
            <q-linear-progress
              :value="(dashboardData.top_metrics?.santri?.aktif || 118) / (dashboardData.top_metrics?.santri?.total || 124)"
              color="primary"
              class="q-mt-sm"
              rounded
              size="4px"
            />
          </div>
        </div>

        <!-- Metric 2: Calon Santri PSB -->
        <div class="col-6 col-md-3">
          <div
            v-motion
            :initial="{ opacity: 0, scale: 0.95 }"
            :enter="{ opacity: 1, scale: 1, transition: { delay: 150, duration: 350 } }"
            class="metric-card bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md cursor-pointer full-height column justify-between"
            @click="$router.push('/psb')"
          >
            <div class="row items-center justify-between no-wrap">
              <div class="column">
                <span class="text-caption text-grey-6 text-weight-medium text-xs">Pendaftaran PSB</span>
                <span class="text-h5 text-md-h4 text-weight-bolder text-grey-9 q-my-xs">
                  {{ dashboardData.top_metrics?.psb?.total || 18 }}
                </span>
                <div class="row items-center text-xs text-info text-nowrap">
                  <q-icon name="pending" size="13px" class="q-mr-xs" />
                  <span>{{ dashboardData.top_metrics?.psb?.diproses || 8 }} Proses</span>
                </div>
              </div>
              <div class="metric-icon-box bg-blue-1 text-info">
                <q-icon name="how_to_reg" size="24px" />
              </div>
            </div>
            <q-linear-progress
              :value="(dashboardData.top_metrics?.psb?.diterima || 7) / (dashboardData.top_metrics?.psb?.total || 18)"
              color="info"
              class="q-mt-sm"
              rounded
              size="4px"
            />
          </div>
        </div>

        <!-- Metric 3: Total Hafalan Juz -->
        <div class="col-6 col-md-3">
          <div
            v-motion
            :initial="{ opacity: 0, scale: 0.95 }"
            :enter="{ opacity: 1, scale: 1, transition: { delay: 200, duration: 350 } }"
            class="metric-card bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md cursor-pointer full-height column justify-between"
            @click="$router.push('/hafalan')"
          >
            <div class="row items-center justify-between no-wrap">
              <div class="column">
                <span class="text-caption text-grey-6 text-weight-medium text-xs">Hafalan Tuntas</span>
                <span class="text-h5 text-md-h4 text-weight-bolder text-grey-9 q-my-xs">
                  {{ dashboardData.top_metrics?.hafalan?.total_juz || 842 }} <small class="text-caption text-grey-6">Juz</small>
                </span>
                <div class="row items-center text-xs text-amber-9 text-nowrap">
                  <q-icon name="star" size="13px" class="q-mr-xs" />
                  <span>{{ dashboardData.top_metrics?.hafalan?.mutqin_juz || 620 }} Mutqin</span>
                </div>
              </div>
              <div class="metric-icon-box bg-amber-1 text-amber-9">
                <q-icon name="menu_book" size="24px" />
              </div>
            </div>
            <q-linear-progress
              :value="0.74"
              color="amber-9"
              class="q-mt-sm"
              rounded
              size="4px"
            />
          </div>
        </div>

        <!-- Metric 4: Mutabaah Yaumiyah -->
        <div class="col-6 col-md-3">
          <div
            v-motion
            :initial="{ opacity: 0, scale: 0.95 }"
            :enter="{ opacity: 1, scale: 1, transition: { delay: 250, duration: 350 } }"
            class="metric-card bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md cursor-pointer full-height column justify-between"
            @click="$router.push('/mutabaah')"
          >
            <div class="row items-center justify-between no-wrap">
              <div class="column">
                <span class="text-caption text-grey-6 text-weight-medium text-xs">Mutabaah Ibadah</span>
                <span class="text-h5 text-md-h4 text-weight-bolder text-grey-9 q-my-xs">
                  {{ dashboardData.top_metrics?.mutabaah?.rata_rata_kepatuhan || 92.4 }}%
                </span>
                <div class="row items-center text-xs text-positive text-nowrap">
                  <q-icon name="trending_up" size="13px" class="q-mr-xs" />
                  <span>Sangat Baik</span>
                </div>
              </div>
              <div class="metric-icon-box bg-teal-1 text-teal-8">
                <q-icon name="checklist" size="24px" />
              </div>
            </div>
            <q-linear-progress
              :value="(dashboardData.top_metrics?.mutabaah?.rata_rata_kepatuhan || 92.4) / 100"
              color="teal-8"
              class="q-mt-sm"
              rounded
              size="4px"
            />
          </div>
        </div>
      </div>

      <!-- 2. CHARTS SECTION (Grafik Tren Hafalan & Donut Distribusi Santri) -->
      <div class="row q-col-gutter-sm q-col-gutter-md-md">
        <!-- Line Chart: Tren Hafalan -->
        <div class="col-12 col-lg-8">
          <div
            v-motion
            :initial="{ opacity: 0, y: 15 }"
            :enter="{ opacity: 1, y: 0, transition: { delay: 300, duration: 400 } }"
            class="chart-card bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md"
          >
            <div class="row items-center justify-between q-mb-xs">
              <div class="column">
                <span class="text-subtitle1 text-weight-bolder text-grey-9">Tren Capaian Hafalan Al-Qur'an</span>
                <span class="text-caption text-grey-6 text-xs">Target vs Realisasi (6 Bulan Terakhir)</span>
              </div>
              <q-badge outline color="primary" class="q-pa-xs">Bulanan</q-badge>
            </div>
            <div class="chart-wrapper">
              <apexchart
                type="area"
                height="260"
                :options="hafalanChartOptions"
                :series="hafalanChartSeries"
              />
            </div>
          </div>
        </div>

        <!-- Donut Chart: Komposisi Status Santri -->
        <div class="col-12 col-lg-4">
          <div
            v-motion
            :initial="{ opacity: 0, y: 15 }"
            :enter="{ opacity: 1, y: 0, transition: { delay: 350, duration: 400 } }"
            class="chart-card bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md column justify-between"
          >
            <div>
              <div class="text-subtitle1 text-weight-bolder text-grey-9">Status Keaktifan Santri</div>
              <div class="text-caption text-grey-6 text-xs q-mb-xs">Distribusi status seluruh santri</div>
              <div class="flex flex-center chart-donut-wrapper">
                <apexchart
                  type="donut"
                  width="100%"
                  height="220"
                  :options="santriDonutOptions"
                  :series="santriDonutSeries"
                />
              </div>
            </div>

            <div class="row justify-around text-center q-pt-sm border-top">
              <div class="column">
                <span class="text-caption text-grey-6 text-xs">Aktif</span>
                <span class="text-subtitle2 text-weight-bold text-positive">118</span>
              </div>
              <div class="column">
                <span class="text-caption text-grey-6 text-xs">Keluar</span>
                <span class="text-subtitle2 text-weight-bold text-negative">4</span>
              </div>
              <div class="column">
                <span class="text-caption text-grey-6 text-xs">Lulus</span>
                <span class="text-subtitle2 text-weight-bold text-primary">2</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. FINANCIAL SUMMARY (2x2 on Mobile, 4-Cols on Desktop) -->
      <div v-if="dashboardData.ringkasan_keuangan" class="column q-gutter-y-xs">
        <div class="text-subtitle1 text-weight-bolder text-grey-9">
          Ringkasan Keuangan & Kas Yayasan
        </div>

        <div class="row q-col-gutter-sm q-col-gutter-md-md">
          <!-- Kas Yayasan (Pengurus Only) -->
          <div v-if="dashboardData.ringkasan_keuangan.kas_yayasan !== undefined" class="col-6 col-md-3">
            <div class="finance-card bg-emerald-gradient text-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between">
              <div class="text-caption text-emerald-100 text-xs">Kas Induk Yayasan</div>
              <div class="text-subtitle1 text-md-h5 text-weight-bolder q-my-xs ellipsis">
                {{ dashboardData.ringkasan_keuangan.kas_yayasan_formatted || 'Rp 48.750.000' }}
              </div>
              <div class="text-xs text-emerald-200">Saldo kas yayasan</div>
            </div>
          </div>

          <!-- Kas Putra -->
          <div v-if="dashboardData.ringkasan_keuangan.kas_putra !== undefined" class="col-6 col-md-3">
            <div class="finance-card bg-blue-9 text-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between">
              <div class="text-caption text-blue-2 text-xs">Kas Operasional Putra</div>
              <div class="text-subtitle1 text-md-h5 text-weight-bolder q-my-xs ellipsis">
                {{ dashboardData.ringkasan_keuangan.kas_putra_formatted || 'Rp 12.350.000' }}
              </div>
              <div class="text-xs text-blue-2">Operasional asrama putra</div>
            </div>
          </div>

          <!-- Kas Putri -->
          <div v-if="dashboardData.ringkasan_keuangan.kas_putri !== undefined" class="col-6 col-md-3">
            <div class="finance-card bg-purple-9 text-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between">
              <div class="text-caption text-purple-2 text-xs">Kas Operasional Putri</div>
              <div class="text-subtitle1 text-md-h5 text-weight-bolder q-my-xs ellipsis">
                {{ dashboardData.ringkasan_keuangan.kas_putri_formatted || 'Rp 10.870.000' }}
              </div>
              <div class="text-xs text-purple-2">Operasional asrama putri</div>
            </div>
          </div>

          <!-- Total Donasi Masuk -->
          <div v-if="dashboardData.ringkasan_keuangan.total_donasi_bulan_ini !== undefined" class="col-6 col-md-3">
            <div class="finance-card bg-amber-9 text-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between">
              <div class="text-caption text-amber-1 text-xs">Donasi Bulan Ini</div>
              <div class="text-subtitle1 text-md-h5 text-weight-bolder q-my-xs ellipsis">
                {{ dashboardData.ringkasan_keuangan.total_donasi_bulan_ini_formatted || 'Rp 8.420.000' }}
              </div>
              <div class="text-xs text-amber-1">Infaq & sedekah donatur</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. JADWAL KEGIATAN & ANTREAN PSB -->
      <div class="row q-col-gutter-sm q-col-gutter-md-md">
        <!-- Jadwal Kegiatan -->
        <div class="col-12 col-lg-6">
          <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height">
            <div class="row items-center justify-between q-mb-sm">
              <span class="text-subtitle1 text-weight-bolder text-grey-9">Agenda & Jadwal Kegiatan</span>
              <q-btn flat dense no-caps color="primary" label="Kalender" icon-right="chevron_right" size="sm" />
            </div>

            <q-list separator>
              <q-item
                v-for="(jadwal, idx) in dashboardData.jadwal_kegiatan"
                :key="idx"
                class="q-px-none q-py-xs"
              >
                <q-item-section avatar>
                  <q-avatar rounded :color="getJadwalColor(jadwal.kategori)" text-color="white" icon="event" size="34px" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold text-grey-9 text-body2">{{ jadwal.nama_kegiatan }}</q-item-label>
                  <q-item-label caption class="text-grey-6 text-xs">{{ jadwal.keterangan }}</q-item-label>
                </q-item-section>
                <q-item-section side class="text-right">
                  <q-badge :color="getJadwalColor(jadwal.kategori)" outline class="text-xs">
                    {{ jadwal.waktu }}
                  </q-badge>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>

        <!-- Antrian PSB Terbaru -->
        <div class="col-12 col-lg-6">
          <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height">
            <div class="row items-center justify-between q-mb-sm">
              <span class="text-subtitle1 text-weight-bolder text-grey-9">Pendaftaran PSB Terbaru</span>
              <q-btn
                flat
                dense
                no-caps
                color="primary"
                label="Buka PSB"
                icon-right="chevron_right"
                size="sm"
                @click="$router.push('/psb')"
              />
            </div>

            <div v-if="!dashboardData.antrean_psb?.length" class="text-center text-grey-6 q-pa-md text-caption">
              Belum ada antrean pendaftaran baru.
            </div>

            <q-list separator v-else>
              <q-item
                v-for="psb in dashboardData.antrean_psb"
                :key="psb.id"
                class="q-px-none q-py-xs"
              >
                <q-item-section avatar>
                  <q-avatar size="34px" :color="psb.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white">
                    {{ psb.nama_lengkap.charAt(0) }}
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold text-grey-9 text-body2">{{ psb.nama_lengkap }}</q-item-label>
                  <q-item-label caption class="text-grey-6 text-xs">
                    {{ psb.no_pendaftaran }} &bull; {{ psb.asal_kota || '-' }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-chip
                    dense
                    size="xs"
                    :color="getPsbStatusColor(psb.status_seleksi)"
                    text-color="white"
                    class="text-weight-medium text-capitalize"
                  >
                    {{ psb.status_seleksi }}
                  </q-chip>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>
      </div>

      <!-- 5. MUTABAAH RADIAL PROGRESS & RECENT ACTIVITIES -->
      <div class="row q-col-gutter-sm q-col-gutter-md-md">
        <!-- Mutabaah Yaumiyah Gauge Checklist -->
        <div class="col-12 col-lg-6">
          <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md">
            <div class="row items-center justify-between q-mb-sm">
              <span class="text-subtitle1 text-weight-bolder text-grey-9">Mutabaah Sholat & Ibadah</span>
              <q-btn
                flat
                dense
                no-caps
                color="primary"
                label="Checklist"
                icon-right="chevron_right"
                size="sm"
                @click="$router.push('/mutabaah')"
              />
            </div>

            <!-- Responsive Mutabaah Gauge Grid -->
            <div class="row q-col-gutter-xs text-center">
              <div
                v-for="sholat in dashboardData.mutabaah_sholat"
                :key="sholat.nama"
                class="col-4 col-sm-3 col-md-3 q-py-xs"
              >
                <div class="q-pa-xs border rounded-borders bg-grey-1 column items-center">
                  <q-circular-progress
                    show-value
                    font-size="11px"
                    :value="sholat.persentase"
                    size="46px"
                    :thickness="0.18"
                    color="primary"
                    track-color="grey-3"
                    class="q-my-xs text-weight-bold text-primary"
                  >
                    {{ sholat.persentase }}%
                  </q-circular-progress>
                  <div class="text-caption text-weight-bold text-grey-9 text-xs ellipsis full-width">
                    {{ sholat.nama }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Aktivitas Terkini (Activity Logs) -->
        <div class="col-12 col-lg-6">
          <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md">
            <div class="row items-center justify-between q-mb-sm">
              <span class="text-subtitle1 text-weight-bolder text-grey-9">Log Aktivitas Sistem</span>
              <q-badge color="grey-3" text-color="grey-9" class="text-xs">Real-time</q-badge>
            </div>

            <q-timeline color="primary" dense class="q-px-xs">
              <q-timeline-entry
                v-for="act in dashboardData.aktivitas_terbaru"
                :key="act.id"
                :title="act.judul"
                :subtitle="act.waktu_lalu"
                :icon="act.icon || 'info'"
                :color="act.warna_badge || 'primary'"
              >
                <div class="text-caption text-grey-7 text-xs">{{ act.deskripsi }}</div>
              </q-timeline-entry>
            </q-timeline>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../utils/api';

const authStore = useAuthStore();
const loading = ref(true);
const dashboardData = ref({});

const currentDateFormatted = computed(() => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
});

// Chart: Tren Hafalan Al-Qur'an (Area Chart)
const hafalanChartOptions = {
  chart: {
    toolbar: { show: false },
    fontFamily: 'Inter, system-ui, sans-serif'
  },
  colors: ['#0D7C66', '#14B8A6'],
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    categories: ['Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
    labels: {
      style: { fontSize: '11px' }
    }
  },
  yaxis: {
    labels: {
      formatter: (val) => `${val} Juz`,
      style: { fontSize: '11px' }
    }
  },
  legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
  responsive: [
    {
      breakpoint: 600,
      options: {
        chart: { height: 220 },
        legend: { position: 'bottom', horizontalAlign: 'center', fontSize: '11px' },
        xaxis: { labels: { style: { fontSize: '10px' } } },
        yaxis: { labels: { style: { fontSize: '10px' } } }
      }
    }
  ]
};

const hafalanChartSeries = ref([
  { name: 'Capaian Riil (Juz)', data: [110, 135, 142, 160, 178, 195] },
  { name: 'Target Pondok (Juz)', data: [100, 120, 140, 150, 170, 190] }
]);

// Chart: Status Santri Donut Chart
const santriDonutOptions = {
  chart: {
    fontFamily: 'Inter, system-ui, sans-serif'
  },
  labels: ['Santri Aktif', 'Santri Keluar', 'Santri Lulus'],
  colors: ['#10B981', '#EF4444', '#0D7C66'],
  dataLabels: { enabled: false },
  legend: { show: false },
  responsive: [
    {
      breakpoint: 600,
      options: {
        chart: { height: 200 }
      }
    }
  ]
};

const santriDonutSeries = ref([118, 4, 2]);

const getJadwalColor = (kategori) => {
  switch (kategori) {
    case 'tahfiz': return 'primary';
    case 'evaluasi': return 'amber-9';
    case 'psb': return 'blue-8';
    default: return 'teal-7';
  }
};

const getPsbStatusColor = (status) => {
  switch (status) {
    case 'diterima': return 'positive';
    case 'diproses': return 'info';
    case 'cadangan': return 'warning';
    case 'ditolak': return 'negative';
    default: return 'grey-7';
  }
};

const fetchDashboard = async () => {
  loading.value = true;
  try {
    const [response] = await Promise.all([
      api.get('/dashboard'),
      new Promise(resolve => setTimeout(resolve, 600)) // smooth minimal duration for branded logo loader
    ]);

    if (response.data.success) {
      dashboardData.value = response.data.data;
      if (dashboardData.value.tren_hafalan) {
        hafalanChartSeries.value = [
          { name: 'Capaian Riil (Juz)', data: dashboardData.value.tren_hafalan.capaian || [110, 135, 142, 160, 178, 195] },
          { name: 'Target Pondok (Juz)', data: dashboardData.value.tren_hafalan.target || [100, 120, 140, 150, 170, 190] }
        ];
      }
    }
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>

<style scoped>
.hero-banner {
  background: linear-gradient(135deg, #0A5344 0%, #0D7C66 65%, #006A67 100%);
  border-radius: 14px;
}

.hero-logo-box {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #FFFFFF;
}

@media (max-width: 599px) {
  .hero-logo-box {
    width: 42px;
    height: 42px;
  }
  .text-xs-mobile {
    font-size: 0.68rem;
  }
}

.hero-logo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.metric-card {
  border-radius: 12px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: 1px solid #E5E7EB;
}

.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}

.metric-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 599px) {
  .metric-icon-box {
    width: 34px;
    height: 34px;
  }
}

.bg-emerald-light {
  background-color: #E0F2E9;
}

.bg-emerald-gradient {
  background: linear-gradient(135deg, #0D7C66 0%, #006A67 100%);
}

.chart-card {
  border-radius: 12px;
  border: 1px solid #E5E7EB;
}

.finance-card {
  border-radius: 12px;
}

.border-top {
  border-top: 1px solid #F1F5F9;
}

.border {
  border: 1px solid #E2E8F0;
}

.text-xs {
  font-size: 0.72rem;
}

/* FULLSCREEN BRANDED LOGO LOADER */
.fullscreen-loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: radial-gradient(circle at 50% 40%, #FFFFFF 0%, #F4F7F6 60%, #E8F5F1 100%);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.loader-logo-wrapper {
  position: relative;
  width: 124px;
  height: 124px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loader-orbit-ring {
  position: absolute;
  width: 124px;
  height: 124px;
  border-radius: 50%;
  border: 3.5px solid transparent;
  border-top-color: #0D7C66;
  border-right-color: #14B8A6;
  animation: spin-orbit 1.1s cubic-bezier(0.55, 0.2, 0.25, 0.95) infinite;
}

.loader-pulse-glow {
  position: absolute;
  width: 108px;
  height: 108px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(13, 124, 102, 0.32) 0%, rgba(20, 184, 166, 0) 70%);
  animation: pulse-scale 1.8s ease-in-out infinite;
}

.loader-logo-box {
  position: relative;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  overflow: hidden;
  border: 3.5px solid #FFFFFF;
  background-color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  animation: logo-breathe 2.2s ease-in-out infinite;
}

.loader-logo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.loader-progress-track {
  width: 200px;
  height: 4.5px;
  background-color: #E2E8F0;
  border-radius: 999px;
  overflow: hidden;
  position: relative;
}

.loader-progress-bar {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  background: linear-gradient(90deg, #0D7C66, #14B8A6, #006A67);
  border-radius: 999px;
  width: 40%;
  animation: progress-indeterminate 1.4s ease-in-out infinite;
}

.loader-text-pulse {
  animation: text-pulse 1.8s ease-in-out infinite;
}

@keyframes spin-orbit {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes pulse-scale {
  0%, 100% {
    transform: scale(0.9);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.35);
    opacity: 0.85;
  }
}

@keyframes logo-breathe {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.06);
  }
}

@keyframes progress-indeterminate {
  0% {
    left: -40%;
    width: 35%;
  }
  50% {
    left: 30%;
    width: 60%;
  }
  100% {
    left: 100%;
    width: 35%;
  }
}

@keyframes text-pulse {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}

/* Fullscreen Loader Transition */
.loader-fade-enter-active,
.loader-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}

.loader-fade-enter-from,
.loader-fade-leave-to {
  opacity: 0;
  transform: scale(1.03);
}
</style>
