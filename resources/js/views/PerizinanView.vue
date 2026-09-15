<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Perizinan Santri</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Pencatatan izin keluar pondok, deteksi santri telat kembali, dan konfirmasi kepulangan.</p>
      </div>

      <q-btn
        v-if="authStore.isPengurus || authStore.isUstadz"
        color="primary"
        icon="add_circle"
        label="Catat Izin Baru"
        unelevated
        no-caps
        class="rounded-borders text-weight-bold"
        size="sm"
        size-md="md"
        @click="openAddDialog"
      />
    </div>

    <!-- Filter Bar -->
    <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md q-mb-sm q-mb-md-md">
      <div class="row q-col-gutter-xs q-col-gutter-sm-sm items-center">
        <div class="col-6 col-sm-3">
          <q-select
            v-model="filters.status"
            outlined
            dense
            :options="[{label:'Semua Status', value:''}, {label:'Izin Aktif', value:'aktif'}, {label:'Terlambat', value:'terlambat'}, {label:'Selesai/Kembali', value:'kembali'}]"
            emit-value
            map-options
            label="Status Izin"
            @update:model-value="loadData"
          />
        </div>
        <div class="col-6 col-sm-3">
          <q-select
            v-model="filters.jenis"
            outlined
            dense
            :options="[{label:'Semua Jenis', value:''}, {label:'Pulang ke Rumah', value:'pulang'}, {label:'Keluar Komplek', value:'keluar_komplek'}, {label:'Sakit / RS', value:'sakit'}]"
            emit-value
            map-options
            label="Jenis Izin"
            @update:model-value="loadData"
          />
        </div>
      </div>
    </div>

    <!-- Data Table & Mobile Cards -->
    <div class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="izinList"
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
                  <q-avatar size="36px" :color="props.row.santri?.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white" class="q-mr-sm">
                    {{ props.row.santri?.nama_lengkap.charAt(0) }}
                  </q-avatar>
                  <div class="column">
                    <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.santri?.nama_lengkap }}</span>
                    <span class="text-caption text-grey-6 text-xs">{{ props.row.kode_izin }} &bull; {{ props.row.jenis_izin.replace('_', ' ') }}</span>
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

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div>Alasan: <span class="text-weight-medium text-grey-9">{{ props.row.alasan }}</span></div>
                <div>Batas Kembali: <span class="text-weight-bold" :class="props.row.status === 'terlambat' ? 'text-negative' : 'text-grey-9'">{{ props.row.batas_kembali }}</span></div>
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs">
                <span class="text-caption text-grey-5 text-xs">Penanggungjawab: {{ props.row.penanggungjawab?.name || 'Ustadz' }}</span>
                <q-btn
                  v-if="props.row.status === 'aktif' || props.row.status === 'terlambat'"
                  unelevated
                  dense
                  size="sm"
                  color="positive"
                  icon="assignment_turned_in"
                  label="Konfirmasi Kembali"
                  class="rounded-borders text-weight-bold"
                  @click="handleKembali(props.row)"
                />
                <span v-else class="text-caption text-positive text-weight-bold text-xs">Sudah Kembali</span>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <div class="row items-center no-wrap">
              <q-avatar size="34px" :color="props.row.santri?.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white" class="q-mr-sm">
                {{ props.row.santri?.nama_lengkap.charAt(0) }}
              </q-avatar>
              <div class="column">
                <span class="text-weight-bold text-grey-9">{{ props.row.santri?.nama_lengkap }}</span>
                <span class="text-caption text-grey-6">{{ props.row.kode_izin }}</span>
              </div>
            </div>
          </q-td>
        </template>

        <template #body-cell-jenis="props">
          <q-td :props="props" align="center">
            <q-badge color="grey-2" text-color="grey-9" class="text-capitalize q-pa-xs">
              {{ props.row.jenis_izin.replace('_', ' ') }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-batas="props">
          <q-td :props="props">
            <div class="column">
              <span :class="props.row.status === 'terlambat' ? 'text-negative text-weight-bolder' : 'text-grey-9'">
                {{ props.row.batas_kembali }}
              </span>
              <span v-if="props.row.status === 'terlambat'" class="text-caption text-negative text-xs">
                ⚠️ Telat Kembali
              </span>
            </div>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props" align="center">
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
            <q-btn
              v-if="props.row.status === 'aktif' || props.row.status === 'terlambat'"
              unelevated
              dense
              size="sm"
              color="positive"
              icon="assignment_turned_in"
              label="Konfirmasi Kembali"
              class="rounded-borders text-weight-bold q-px-sm"
              @click="handleKembali(props.row)"
            />
            <span v-else class="text-caption text-grey-5">Selesai</span>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog Catat Izin Baru -->
    <q-dialog v-model="addDialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="assignment_late" size="20px" />
            <span>Formulir Perizinan Santri</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveIzin">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Pilih Santri</label>
              <q-select
                v-model="form.santri_id"
                outlined
                dense
                use-input
                input-debounce="300"
                :options="santriOptions"
                option-value="id"
                option-label="label"
                emit-value
                map-options
                @filter="filterSantri"
                placeholder="Ketik nama santri..."
                :rules="[val => !!val || 'Santri wajib dipilih']"
              />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Jenis Izin</label>
                <q-select
                  v-model="form.jenis_izin"
                  outlined
                  dense
                  :options="[{label:'Pulang ke Rumah', value:'pulang'}, {label:'Keluar Komplek', value:'keluar_komplek'}, {label:'Sakit / Berobat', value:'sakit'}]"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Batas Waktu Kembali</label>
                <q-input v-model="form.batas_kembali" outlined dense type="datetime-local" :rules="[val => !!val || 'Batas kembali wajib diisi']" />
              </div>
            </div>

            <div>
              <label class="form-label">Alasan / Keperluan Izin</label>
              <q-input v-model="form.alasan" outlined dense type="textarea" rows="2" placeholder="Menjenguk keluarga sakit, urusan keluarga..." :rules="[val => !!val || 'Alasan wajib diisi']" />
            </div>

            <div>
              <label class="form-label">Penjemput / Pendamping</label>
              <q-input v-model="form.penjemput" outlined dense placeholder="Ayah kandung / Paman" />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menerbitkan...' : 'Terbitkan Surat Izin'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
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
import { toastSuccess, toastError, confirmDialog } from '../utils/sweetalert';

const authStore = useAuthStore();
const loading = ref(false);
const saving = ref(false);
const izinList = ref([]);
const addDialog = ref(false);

const santriOptions = ref([]);
const allSantri = ref([]);

const filters = ref({ status: '', jenis: '' });
const pagination = ref({ page: 1, rowsPerPage: 10, rowsNumber: 0 });

const form = ref({
  santri_id: null,
  jenis_izin: 'pulang',
  batas_kembali: '',
  alasan: '',
  penjemput: ''
});

const columns = [
  { name: 'nama', label: 'Nama Santri & Kode', field: 'santri', align: 'left' },
  { name: 'jenis', label: 'Jenis Izin', field: 'jenis_izin', align: 'center' },
  { name: 'alasan', label: 'Alasan / Keperluan', field: 'alasan', align: 'left' },
  { name: 'batas', label: 'Batas Kembali', field: 'batas_kembali', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'aksi', label: 'Aksi', align: 'right' }
];

const getStatusColor = (status) => {
  switch (status) {
    case 'aktif': return 'info';
    case 'terlambat': return 'negative';
    case 'kembali': return 'positive';
    default: return 'grey-7';
  }
};

const loadData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/perizinan', {
      params: {
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage,
        status: filters.value.status,
        jenis_izin: filters.value.jenis
      }
    });
    if (res.data.success) {
      izinList.value = res.data.data.data;
      pagination.value.rowsNumber = res.data.data.total;
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const loadSantriOptions = async () => {
  try {
    const res = await api.get('/santri', { params: { per_page: 200, status: 'aktif' } });
    if (res.data.success) {
      allSantri.value = res.data.data.data.map(s => ({
        id: s.id,
        label: `${s.nama_lengkap} (${s.nis} - ${s.jenis_kelamin === 'L' ? 'Putra' : 'Putri'})`
      }));
      santriOptions.value = allSantri.value;
    }
  } catch (err) {
    console.error(err);
  }
};

const filterSantri = (val, update) => {
  if (val === '') {
    update(() => { santriOptions.value = allSantri.value; });
    return;
  }
  update(() => {
    const needle = val.toLowerCase();
    santriOptions.value = allSantri.value.filter(v => v.label.toLowerCase().indexOf(needle) > -1);
  });
};

const onRequest = (props) => {
  pagination.value = props.pagination;
  loadData();
};

const openAddDialog = () => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 2);
  const formatted = tomorrow.toISOString().substring(0, 16);

  form.value = {
    santri_id: null,
    jenis_izin: 'pulang',
    batas_kembali: formatted,
    alasan: '',
    penjemput: ''
  };
  addDialog.value = true;
};

const saveIzin = async () => {
  saving.value = true;
  try {
    await api.post('/perizinan', form.value);
    toastSuccess('Surat izin santri berhasil diterbitkan.');
    addDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal menerbitkan perizinan.');
  } finally {
    saving.value = false;
  }
};

const handleKembali = async (izin) => {
  const res = await confirmDialog({
    title: 'Konfirmasi Kepulangan Santri?',
    text: `Santri ${izin.santri?.nama_lengkap} telah tiba kembali di pondok pesantren.`,
    confirmButtonText: 'Ya, Konfirmasi Kembali',
    icon: 'question'
  });

  if (res.isConfirmed) {
    try {
      await api.post(`/perizinan/${izin.id}/kembali`);
      toastSuccess('Status izin santri berhasil diperbarui: Kembali ke Pondok.');
      loadData();
    } catch (err) {
      toastError(err.response?.data?.message || 'Gagal mengonfirmasi kepulangan.');
    }
  }
};

onMounted(() => {
  loadData();
  loadSantriOptions();
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
</style>
