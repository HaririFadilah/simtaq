<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Mutabaah Yaumiyah (Ibadah Harian)</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Pencatatan checklist sholat berjamaah & amalan harian santri oleh Ketua Santri.</p>
      </div>

      <div class="row q-gutter-xs q-mt-xs q-mt-md-none">
        <q-btn
          outline
          color="primary"
          icon="done_all"
          label="Tandai Semua Hadir"
          no-caps
          dense
          class="q-px-sm text-xs text-weight-bold"
          @click="markAllPresent"
        />

        <q-btn
          v-if="authStore.isPengurus || authStore.isKetuaSantri"
          color="primary"
          icon="save"
          label="Simpan Lembar"
          unelevated
          no-caps
          dense
          class="rounded-borders text-weight-bold q-px-sm text-xs"
          :loading="saving"
          @click="saveSheet"
        />
      </div>
    </div>

    <!-- Date & Scope Controls Card -->
    <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md q-mb-sm q-mb-md-md">
      <div class="row q-col-gutter-xs q-col-gutter-sm-sm items-center">
        <div class="col-12 col-sm-4">
          <label class="text-caption text-weight-bold text-grey-8 text-xs">Tanggal Pemantauan</label>
          <q-input v-model="selectedDate" outlined dense type="date" @update:model-value="loadSheet" />
        </div>

        <div class="col-6 col-sm-4" v-if="authStore.isPengurus">
          <label class="text-caption text-weight-bold text-grey-8 text-xs">Scope Santri</label>
          <q-select
            v-model="genderFilter"
            outlined
            dense
            :options="[{label:'Semua Santri', value:''}, {label:'Santri Putra (Ikhwan)', value:'L'}, {label:'Santri Putri (Akhwat)', value:'P'}]"
            emit-value
            map-options
            @update:model-value="loadSheet"
          />
        </div>

        <div class="col-6 col-sm-4 text-right self-end">
          <q-btn flat no-caps color="grey-7" icon="refresh" label="Muat Ulang" class="text-xs" @click="loadSheet" />
        </div>
      </div>
    </div>

    <!-- Checklist Matrix Table with Sticky Left Column for Mobile -->
    <div class="bg-white shadow-1 rounded-borders overflow-hidden">
      <div v-if="loading" class="q-pa-lg text-center">
        <q-spinner-dots color="primary" size="40px" />
        <div class="text-caption text-grey-6 q-mt-sm text-xs">Memuat lembar mutabaah...</div>
      </div>

      <div v-else class="q-table__container">
        <div class="table-responsive">
          <table class="q-table custom-checklist-table full-width">
            <thead>
              <tr class="bg-grey-1">
                <th class="text-left sticky-col-header" style="min-width: 160px; max-width: 200px;">
                  Nama Santri
                </th>
                <th
                  v-for="kgt in kegiatans"
                  :key="kgt.id"
                  class="text-center"
                  style="min-width: 80px;"
                >
                  <div class="column items-center">
                    <span class="text-weight-bold text-grey-9 text-xs">{{ kgt.nama_kegiatan }}</span>
                    <span class="text-caption text-grey-5" style="font-size: 10px;">{{ kgt.waktu }}</span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in sheetRows" :key="item.santri_id">
                <td class="text-left sticky-col-cell">
                  <div class="column">
                    <span class="text-weight-bold text-grey-9 text-body2 leading-tight ellipsis">{{ item.nama }}</span>
                    <span class="text-caption text-grey-6 text-xs ellipsis">{{ item.asrama }}</span>
                  </div>
                </td>

                <td
                  v-for="kgt in kegiatans"
                  :key="kgt.id"
                  class="text-center"
                >
                  <button
                    type="button"
                    class="status-toggle-btn"
                    :class="getStatusBtnClass(item.checklist[kgt.id])"
                    @click="cycleStatus(item, kgt.id)"
                  >
                    {{ getStatusBtnLabel(item.checklist[kgt.id]) }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Legend Card -->
    <div class="q-mt-sm bg-white q-pa-sm rounded-borders shadow-1 row items-center justify-around text-xs flex-wrap q-gutter-xs">
      <div class="row items-center q-gutter-xs">
        <span class="status-indicator bg-positive"></span>
        <span class="text-weight-medium">H: Hadir</span>
      </div>
      <div class="row items-center q-gutter-xs">
        <span class="status-indicator bg-negative"></span>
        <span class="text-weight-medium">A: Alpa</span>
      </div>
      <div class="row items-center q-gutter-xs">
        <span class="status-indicator bg-info"></span>
        <span class="text-weight-medium">I: Izin</span>
      </div>
      <div class="row items-center q-gutter-xs">
        <span class="status-indicator bg-warning"></span>
        <span class="text-weight-medium">S: Sakit</span>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../utils/api';
import { toastSuccess, toastError } from '../utils/sweetalert';

const authStore = useAuthStore();

const loading = ref(false);
const saving = ref(false);
const kegiatans = ref([]);
const sheetRows = ref([]);
const selectedDate = ref(new Date().toISOString().substring(0, 10));
const genderFilter = ref('');

const loadSheet = async () => {
  loading.value = true;
  try {
    const res = await api.get('/mutabaah/sheet', {
      params: {
        tanggal: selectedDate.value,
        gender: genderFilter.value
      }
    });

    if (res.data.success) {
      kegiatans.value = res.data.data.kegiatan;
      sheetRows.value = res.data.data.sheet;
    }
  } catch (err) {
    console.error('Failed to load mutabaah sheet:', err);
  } finally {
    loading.value = false;
  }
};

const getStatusBtnClass = (status) => {
  switch (status) {
    case 'hadir': return 'btn-hadir';
    case 'alpa': return 'btn-alpa';
    case 'izin': return 'btn-izin';
    case 'sakit': return 'btn-sakit';
    default: return 'btn-hadir';
  }
};

const getStatusBtnLabel = (status) => {
  switch (status) {
    case 'hadir': return 'H';
    case 'alpa': return 'A';
    case 'izin': return 'I';
    case 'sakit': return 'S';
    default: return 'H';
  }
};

const cycleStatus = (row, kgtId) => {
  const current = row.checklist[kgtId] || 'hadir';
  const cycle = ['hadir', 'alpa', 'izin', 'sakit'];
  const nextIdx = (cycle.indexOf(current) + 1) % cycle.length;
  row.checklist[kgtId] = cycle[nextIdx];
};

const markAllPresent = () => {
  sheetRows.value.forEach(row => {
    kegiatans.value.forEach(kgt => {
      row.checklist[kgt.id] = 'hadir';
    });
  });
  toastSuccess('Semua santri ditandai Hadir.');
};

const saveSheet = async () => {
  saving.value = true;
  try {
    const records = [];
    sheetRows.value.forEach(row => {
      Object.keys(row.checklist).forEach(kgtId => {
        records.push({
          santri_id: row.santri_id,
          kegiatan_mutabaah_id: parseInt(kgtId),
          status: row.checklist[kgtId]
        });
      });
    });

    const res = await api.post('/mutabaah/sheet', {
      tanggal: selectedDate.value,
      records: records
    });

    if (res.data.success) {
      toastSuccess('Lembar Mutabaah berhasil disimpan!');
    }
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal menyimpan mutabaah.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSheet();
});
</script>

<style scoped>
.text-xs {
  font-size: 0.75rem;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.custom-checklist-table th,
.custom-checklist-table td {
  padding: 8px 10px;
  border-bottom: 1px solid #F1F5F9;
}

/* Sticky first column on mobile & desktop */
.sticky-col-header {
  position: sticky;
  left: 0;
  background-color: #F8FAFC !important;
  z-index: 10;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}

.sticky-col-cell {
  position: sticky;
  left: 0;
  background-color: #FFFFFF !important;
  z-index: 9;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}

.status-toggle-btn {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
  outline: none;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.status-toggle-btn:hover {
  transform: scale(1.08);
}

.status-toggle-btn:active {
  transform: scale(0.92);
}

.btn-hadir {
  background-color: #E0F2E9;
  color: #0D7C66;
}

.btn-alpa {
  background-color: #FEE2E2;
  color: #DC2626;
}

.btn-izin {
  background-color: #DBEAFE;
  color: #2563EB;
}

.btn-sakit {
  background-color: #FEF3C7;
  color: #D97706;
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}
</style>
