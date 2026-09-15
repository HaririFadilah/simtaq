<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Page Header & Action Bar -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Data Induk Santri</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Kelola data santri aktif, alumni, asrama, dan riwayat wali.</p>
      </div>

      <div class="row items-center q-gutter-xs">
        <q-btn
          outline
          color="primary"
          icon="print"
          label="Cetak Rekap"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold gt-xs"
          @click="handlePrintSantri"
        />
        <q-btn
          outline
          color="positive"
          icon="download"
          label="Export CSV"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="handleExportSantri"
        />
        <q-btn
          v-if="authStore.isPengurus || authStore.isUstadz"
          color="primary"
          icon="person_add"
          label="Tambah Santri"
          unelevated
          no-caps
          class="rounded-borders text-weight-bold"
          size="sm"
          size-md="md"
          @click="openAddDialog"
        />
      </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md q-mb-sm q-mb-md-md">
      <div class="row q-col-gutter-xs q-col-gutter-sm-sm items-center">
        <div class="col-12 col-sm-4">
          <q-input
            v-model="filters.search"
            outlined
            dense
            placeholder="Cari nama atau NIS santri..."
            clearable
            @update:model-value="loadData"
          >
            <template #prepend>
              <q-icon name="search" color="grey-6" />
            </template>
          </q-input>
        </div>

        <div class="col-6 col-sm-3">
          <q-select
            v-model="filters.status"
            outlined
            dense
            :options="statusOptions"
            emit-value
            map-options
            label="Status"
            @update:model-value="loadData"
          />
        </div>

        <div class="col-6 col-sm-3">
          <q-select
            v-model="filters.gender"
            outlined
            dense
            :options="genderOptions"
            emit-value
            map-options
            label="Gender"
            @update:model-value="loadData"
          />
        </div>

        <div class="col-12 col-sm-2 text-right">
          <q-btn
            flat
            no-caps
            color="grey-7"
            icon="refresh"
            label="Reset"
            class="full-width-xs"
            @click="resetFilters"
          />
        </div>
      </div>
    </div>

    <!-- Data Table & Mobile Cards -->
    <div class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="santriList"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :grid="$q.screen.xs"
        v-model:pagination="pagination"
        @request="onRequest"
        flat
        separator="horizontal"
      >
        <!-- MOBILE GRID CARD VIEW (< 600px) -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <div class="row items-center no-wrap">
                  <q-avatar size="36px" :color="props.row.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white" class="q-mr-sm">
                    {{ props.row.nama_lengkap.charAt(0) }}
                  </q-avatar>
                  <div class="column">
                    <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.nama_lengkap }}</span>
                    <span class="text-caption text-grey-6 text-xs">{{ props.row.nis }} &bull; {{ props.row.jenis_kelamin === 'L' ? 'Putra' : 'Putri' }}</span>
                  </div>
                </div>

                <q-chip
                  dense
                  size="xs"
                  :color="getStatusColor(props.row.status)"
                  text-color="white"
                  class="text-weight-bold text-capitalize"
                >
                  {{ props.row.status }}
                </q-chip>
              </div>

              <q-separator class="q-my-xs" />

              <div class="row items-center justify-between text-xs text-grey-7 q-py-xs">
                <div>
                  <q-icon name="home" size="14px" class="q-mr-xs text-grey-6" />
                  <span>{{ props.row.asrama?.nama_asrama || '-' }} (Kamar: {{ props.row.kamar || '-' }})</span>
                </div>
                <div class="text-weight-bolder text-primary">
                  <q-icon name="menu_book" size="14px" class="q-mr-xs" />
                  <span>{{ props.row.total_juz_hafalan }} Juz ({{ props.row.total_halaman_hafalan }} Hlm)</span>
                </div>
              </div>

              <div class="row justify-end q-mt-xs q-gutter-xs border-top q-pt-xs">
                <q-btn flat dense size="sm" color="primary" icon="visibility" label="Rincian" @click="viewDetail(props.row)" />
                <q-btn v-if="authStore.isPengurus || authStore.isUstadz" flat dense size="sm" color="grey-8" icon="edit" label="Edit" @click="openEditDialog(props.row)" />
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TABLE TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <div class="row items-center no-wrap">
              <q-avatar size="34px" :color="props.row.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white" class="q-mr-sm">
                {{ props.row.nama_lengkap.charAt(0) }}
              </q-avatar>
              <div class="column">
                <span class="text-weight-bold text-grey-9">{{ props.row.nama_lengkap }}</span>
                <span class="text-caption text-grey-6">{{ props.row.nis }} &bull; {{ props.row.jenis_kelamin === 'L' ? 'Putra' : 'Putri' }}</span>
              </div>
            </div>
          </q-td>
        </template>

        <template #body-cell-asrama="props">
          <q-td :props="props">
            <q-badge color="grey-2" text-color="grey-9" class="q-pa-xs">
              {{ props.row.asrama?.nama_asrama || '-' }} (Kamar: {{ props.row.kamar || '-' }})
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-hafalan="props">
          <q-td :props="props">
            <div class="row items-center no-wrap">
              <span class="text-weight-bolder text-primary q-mr-xs">{{ props.row.total_juz_hafalan }} Juz</span>
              <span class="text-caption text-grey-6">({{ props.row.total_halaman_hafalan }} Hlm)</span>
            </div>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              dense
              size="sm"
              :color="getStatusColor(props.row.status)"
              text-color="white"
              class="text-weight-bold text-capitalize"
            >
              {{ props.row.status }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-aksi="props">
          <q-td :props="props" align="right">
            <q-btn flat dense round color="primary" icon="visibility" @click="viewDetail(props.row)">
              <q-tooltip>Lihat Rincian</q-tooltip>
            </q-btn>
            <q-btn
              v-if="authStore.isPengurus || authStore.isUstadz"
              flat
              dense
              round
              color="grey-8"
              icon="edit"
              @click="openEditDialog(props.row)"
            >
              <q-tooltip>Edit Data</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog Detail Santri -->
    <q-dialog v-model="detailDialog" max-width="560px">
      <q-card style="width: 560px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="badge" size="20px" />
            <span>Biodata Santri</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-md q-pa-sm-lg" v-if="selectedSantri">
          <div class="row items-center q-mb-md">
            <q-avatar size="54px" :color="selectedSantri.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white" class="q-mr-md text-weight-bold text-h6 shadow-1">
              {{ selectedSantri.nama_lengkap.charAt(0) }}
            </q-avatar>
            <div class="column">
              <span class="text-h6 text-weight-bolder text-grey-9 leading-tight">{{ selectedSantri.nama_lengkap }}</span>
              <span class="text-caption text-grey-6 q-mt-xs">NIS: {{ selectedSantri.nis }} &bull; Angkatan: {{ selectedSantri.angkatan }}</span>
              <q-chip dense size="xs" :color="getStatusColor(selectedSantri.status)" text-color="white" class="self-start q-mt-xs">
                {{ selectedSantri.status }}
              </q-chip>
            </div>
          </div>

          <q-separator class="q-my-sm" />

          <div class="row q-col-gutter-sm text-caption q-py-xs">
            <div class="col-12 col-sm-6"><span class="text-grey-6">Jenis Kelamin:</span> <span class="text-weight-bold q-ml-xs">{{ selectedSantri.jenis_kelamin === 'L' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</span></div>
            <div class="col-12 col-sm-6"><span class="text-grey-6">Asrama / Kamar:</span> <span class="text-weight-bold q-ml-xs">{{ selectedSantri.asrama?.nama_asrama || '-' }} / {{ selectedSantri.kamar || '-' }}</span></div>
            <div class="col-12 col-sm-6"><span class="text-grey-6">Asal Kota:</span> <span class="text-weight-bold q-ml-xs">{{ selectedSantri.asal_kota || '-' }}</span></div>
            <div class="col-12 col-sm-6"><span class="text-grey-6">Capaian Tahfiz:</span> <span class="text-weight-bold text-primary q-ml-xs">{{ selectedSantri.total_juz_hafalan }} Juz</span></div>
          </div>

          <div class="bg-grey-1 rounded-borders border q-pa-sm q-mt-md" v-if="selectedSantri.wali">
            <div class="text-weight-bold text-grey-8 q-mb-xs">Data Wali Santri</div>
            <div class="text-caption text-grey-7">Nama: <span class="text-weight-bold">{{ selectedSantri.wali.nama_wali }}</span> ({{ selectedSantri.wali.hubungan }})</div>
            <div class="text-caption text-grey-7 q-mt-xs">No. HP / WA: <span class="text-weight-bold text-primary">{{ selectedSantri.wali.no_hp || '-' }}</span></div>
            <div class="text-caption text-grey-7 q-mt-xs">Alamat: {{ selectedSantri.wali.alamat || '-' }}</div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top">
          <q-btn flat label="Tutup" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Dialog Tambah / Edit Santri -->
    <q-dialog v-model="formDialog" max-width="600px">
      <q-card style="width: 600px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon :name="isEditing ? 'edit' : 'person_add'" size="20px" />
            <span>{{ isEditing ? 'Edit Data Santri' : 'Tambah Santri Baru' }}</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveSantri">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">NIS (Nomor Induk Santri)</label>
                <q-input v-model="form.nis" outlined dense placeholder="STQ-2026-001" :rules="[val => !!val || 'NIS wajib diisi']" />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Nama Panggilan</label>
                <q-input v-model="form.nama_panggilan" outlined dense placeholder="Ahmad" />
              </div>
            </div>

            <div>
              <label class="form-label">Nama Lengkap Santri</label>
              <q-input v-model="form.nama_lengkap" outlined dense placeholder="Nama lengkap sesuai KK/Akta" :rules="[val => !!val || 'Nama lengkap wajib diisi']" />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Jenis Kelamin</label>
                <q-select
                  v-model="form.jenis_kelamin"
                  outlined
                  dense
                  :options="[{label:'Putra (L)', value:'L'}, {label:'Putri (P)', value:'P'}]"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Status Santri</label>
                <q-select
                  v-model="form.status"
                  outlined
                  dense
                  :options="[{label:'Aktif', value:'aktif'}, {label:'Keluar', value:'keluar'}, {label:'Lulus', value:'lulus'}]"
                  emit-value
                  map-options
                />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Gedung Asrama</label>
                <q-select
                  v-model="form.asrama_id"
                  outlined
                  dense
                  :options="asramaOptions"
                  option-value="id"
                  option-label="nama_asrama"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Nomor Kamar</label>
                <q-input v-model="form.kamar" outlined dense placeholder="Contoh: A-01" />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Asal Kota / Daerah</label>
                <q-input v-model="form.asal_kota" outlined dense placeholder="Contoh: Jakarta" />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Tahun Angkatan</label>
                <q-input v-model.number="form.angkatan" type="number" outlined dense />
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Data'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../utils/api';
import { toastSuccess, toastError } from '../utils/sweetalert';
import { exportToCsv, printReport } from '../utils/export';

const authStore = useAuthStore();

const loading = ref(false);
const saving = ref(false);
const santriList = ref([]);
const asramaOptions = ref([]);

const detailDialog = ref(false);
const selectedSantri = ref(null);

const formDialog = ref(false);
const isEditing = ref(false);
const selectedId = ref(null);

const form = ref({
  nis: '',
  nama_lengkap: '',
  nama_panggilan: '',
  jenis_kelamin: 'L',
  asrama_id: null,
  kamar: '',
  asal_kota: '',
  angkatan: new Date().getFullYear(),
  status: 'aktif'
});

const filters = ref({
  search: '',
  status: '',
  gender: ''
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
});

const statusOptions = [
  { label: 'Semua Status', value: '' },
  { label: 'Aktif', value: 'aktif' },
  { label: 'Keluar', value: 'keluar' },
  { label: 'Lulus', value: 'lulus' }
];

const genderOptions = [
  { label: 'Semua Gender', value: '' },
  { label: 'Putra (L)', value: 'L' },
  { label: 'Putri (P)', value: 'P' }
];

const columns = [
  { name: 'nama', label: 'Nama Santri & NIS', field: 'nama_lengkap', align: 'left', sortable: true },
  { name: 'asrama', label: 'Asrama & Kamar', field: 'asrama', align: 'left' },
  { name: 'hafalan', label: 'Capaian Tahfiz', field: 'total_juz_hafalan', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center', sortable: true },
  { name: 'aksi', label: 'Aksi', align: 'right' }
];

const getStatusColor = (status) => {
  switch (status) {
    case 'aktif': return 'positive';
    case 'keluar': return 'negative';
    case 'lulus': return 'primary';
    default: return 'grey-7';
  }
};

const loadData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/santri', {
      params: {
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage,
        search: filters.value.search,
        status: filters.value.status,
        gender: filters.value.gender
      }
    });

    if (res.data.success) {
      santriList.value = res.data.data.data;
      pagination.value.rowsNumber = res.data.data.total;
    }
  } catch (err) {
    console.error('Error fetching santri:', err);
  } finally {
    loading.value = false;
  }
};

const loadAsrama = async () => {
  try {
    const res = await api.get('/asrama');
    if (res.data.success) {
      asramaOptions.value = res.data.data;
    }
  } catch (err) {
    console.error('Error fetching asrama:', err);
  }
};

const onRequest = (props) => {
  pagination.value = props.pagination;
  loadData();
};

const resetFilters = () => {
  filters.value = { search: '', status: '', gender: '' };
  loadData();
};

const viewDetail = (santri) => {
  selectedSantri.value = santri;
  detailDialog.value = true;
};

const openAddDialog = () => {
  isEditing.value = false;
  selectedId.value = null;
  form.value = {
    nis: 'STQ-' + Math.floor(1000 + Math.random() * 9000),
    nama_lengkap: '',
    nama_panggilan: '',
    jenis_kelamin: 'L',
    asrama_id: asramaOptions.value[0]?.id || null,
    kamar: 'A-01',
    asal_kota: '',
    angkatan: new Date().getFullYear(),
    status: 'aktif'
  };
  formDialog.value = true;
};

const openEditDialog = (santri) => {
  isEditing.value = true;
  selectedId.value = santri.id;
  form.value = {
    nis: santri.nis,
    nama_lengkap: santri.nama_lengkap,
    nama_panggilan: santri.nama_panggilan || '',
    jenis_kelamin: santri.jenis_kelamin,
    asrama_id: santri.asrama_id,
    kamar: santri.kamar || '',
    asal_kota: santri.asal_kota || '',
    angkatan: santri.angkatan || new Date().getFullYear(),
    status: santri.status
  };
  formDialog.value = true;
};

const saveSantri = async () => {
  saving.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/santri/${selectedId.value}`, form.value);
      toastSuccess('Data santri berhasil diperbarui.');
    } else {
      await api.post('/santri', form.value);
      toastSuccess('Santri baru berhasil ditambahkan.');
    }
    formDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal menyimpan santri.');
  } finally {
    saving.value = false;
  }
};

const handleExportSantri = () => {
  const exportCols = [
    { label: 'NIS', field: 'nis' },
    { label: 'Nama Lengkap', field: 'nama_lengkap' },
    { label: 'Nama Panggilan', field: 'nama_panggilan' },
    { label: 'Jenis Kelamin', field: row => row.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' },
    { label: 'Asrama', field: row => row.asrama?.nama || '-' },
    { label: 'Kamar', field: 'kamar' },
    { label: 'Asal Kota', field: 'asal_kota' },
    { label: 'Angkatan', field: 'angkatan' },
    { label: 'Status', field: 'status' },
    { label: 'Nama Wali', field: row => row.wali?.nama || '-' },
    { label: 'No. HP Wali', field: row => row.wali?.no_hp || '-' }
  ];
  exportToCsv('Data_Induk_Santri', exportCols, santriList.value);
};

const handlePrintSantri = () => {
  printReport({
    title: 'BUKU INDUK REKAPITULASI DATA SANTRI',
    subtitle: 'Pondok Pesantren Tahfizul Qur\'an Yayasan Al Mukhlisin',
    stats: [
      { label: 'Total Santri Terdata', value: santriList.value.length, color: '#0D7C66' },
      { label: 'Santri Putra', value: santriList.value.filter(s => s.jenis_kelamin === 'L').length, color: '#10B981' },
      { label: 'Santri Putri', value: santriList.value.filter(s => s.jenis_kelamin === 'P').length, color: '#F59E0B' }
    ],
    columns: [
      { label: 'NIS', field: 'nis', align: 'center' },
      { label: 'Nama Santri', field: 'nama_lengkap' },
      { label: 'L/P', field: 'jenis_kelamin', align: 'center' },
      { label: 'Asrama & Kamar', field: row => `${row.asrama?.nama || '-'} (${row.kamar || '-'})` },
      { label: 'Asal Kota', field: 'asal_kota' },
      { label: 'Angkatan', field: 'angkatan', align: 'center' },
      { label: 'Wali & Kontak', field: row => `${row.wali?.nama || '-'} (${row.wali?.no_hp || '-'})` },
      { label: 'Status', field: 'status', align: 'center' }
    ],
    rows: santriList.value,
    signatories: [
      { title: 'Mengetahui,', role: 'Pimpinan Pondok Pesantren', name: 'Ust. H. Ahmad Dahlan, Lc.' },
      { title: 'Dibuat Oleh,', role: 'Kepala Bagian Kesantrian', name: 'Ust. Ridwan Kamil, S.Pd.I.' }
    ]
  });
};

onMounted(() => {
  loadData();
  loadAsrama();
});
</script>

<style scoped>
.text-xs {
  font-size: 0.75rem;
}
.border {
  border: 1px solid #E2E8F0;
}
.border-top {
  border-top: 1px solid #F1F5F9;
}
@media (max-width: 599px) {
  .full-width-xs {
    width: 100%;
  }
}
</style>
