<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Penerimaan Santri Baru (PSB)</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Pendaftaran calon santri, seleksi berkas, dan konversi 1-klik ke data santri aktif.</p>
      </div>

      <div class="row items-center q-gutter-xs">
        <q-btn
          outline
          color="teal-8"
          icon="share"
          label="Link Form Publik"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="copyPublicLink"
        >
          <q-tooltip>Salin link pendaftaran online untuk dibagikan ke wali murid</q-tooltip>
        </q-btn>
        <q-btn
          outline
          color="primary"
          icon="print"
          label="Cetak Rekap"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold gt-xs"
          @click="handlePrintPsb"
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
          @click="handleExportPsb"
        />
        <q-btn
          color="primary"
          icon="person_add"
          label="Daftar Calon Santri"
          unelevated
          no-caps
          class="rounded-borders text-weight-bold"
          size="sm"
          size-md="md"
          @click="openRegisterDialog"
        />
      </div>
    </div>

    <!-- Table & Mobile Cards -->
    <div class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="calonList"
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
                    <span class="text-caption text-grey-6 text-xs">{{ props.row.no_pendaftaran }} &bull; {{ props.row.jenis_kelamin === 'L' ? 'Putra' : 'Putri' }}</span>
                  </div>
                </div>

                <q-chip
                  dense
                  size="xs"
                  :color="getStatusColor(props.row.status_seleksi)"
                  text-color="white"
                  class="text-weight-medium text-capitalize"
                >
                  {{ props.row.status_seleksi }}
                </q-chip>
              </div>

              <q-separator class="q-my-xs" />

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div>Asal: <span class="text-weight-bold text-grey-9">{{ props.row.asal_kota || '-' }}</span></div>
                <div>Wali: <span class="text-weight-bold text-grey-9">{{ props.row.nama_wali }}</span> ({{ props.row.no_hp_wali || '-' }})</div>
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs">
                <div>
                  <q-badge v-if="props.row.is_converted" color="positive" class="text-xs">
                    <q-icon name="check" size="12px" class="q-mr-xs" /> Sudah Jadi Santri
                  </q-badge>
                  <q-btn
                    v-else-if="props.row.status_seleksi === 'diterima'"
                    unelevated
                    dense
                    size="sm"
                    color="primary"
                    icon="person_add_alt"
                    label="Jadikan Santri"
                    class="rounded-borders text-weight-bold"
                    @click="handleConvert(props.row)"
                  />
                  <span v-else class="text-caption text-grey-5 text-xs">Menunggu Hasil</span>
                </div>

                <q-btn flat dense size="sm" color="grey-8" icon="edit" label="Ubah Status" @click="openEditDialog(props.row)" />
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <div class="row items-center no-wrap">
              <q-avatar size="34px" :color="props.row.jenis_kelamin === 'L' ? 'blue-8' : 'pink-8'" text-color="white" class="q-mr-sm">
                {{ props.row.nama_lengkap.charAt(0) }}
              </q-avatar>
              <div class="column">
                <span class="text-weight-bold text-grey-9">{{ props.row.nama_lengkap }}</span>
                <span class="text-caption text-grey-6">{{ props.row.no_pendaftaran }} &bull; {{ props.row.jenis_kelamin === 'L' ? 'Putra' : 'Putri' }}</span>
              </div>
            </div>
          </q-td>
        </template>

        <template #body-cell-wali="props">
          <q-td :props="props">
            <div class="column">
              <span class="text-weight-bold text-grey-9">{{ props.row.nama_wali }}</span>
              <span class="text-caption text-grey-6">{{ props.row.no_hp_wali }}</span>
            </div>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props" align="center">
            <q-chip
              dense
              size="sm"
              :color="getStatusColor(props.row.status_seleksi)"
              text-color="white"
              class="text-weight-medium text-capitalize"
            >
              {{ props.row.status_seleksi }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-konversi="props">
          <q-td :props="props" align="center">
            <q-badge v-if="props.row.is_converted" color="positive" class="q-pa-xs">
              <q-icon name="check" size="14px" class="q-mr-xs" /> Sudah Jadi Santri
            </q-badge>
            <q-btn
              v-else-if="props.row.status_seleksi === 'diterima'"
              unelevated
              dense
              size="sm"
              color="primary"
              icon="person_add_alt"
              label="Jadikan Santri"
              class="rounded-borders text-weight-bold q-px-sm"
              @click="handleConvert(props.row)"
            />
            <span v-else class="text-caption text-grey-5">-</span>
          </q-td>
        </template>

        <template #body-cell-aksi="props">
          <q-td :props="props" align="right">
            <q-btn flat dense round color="primary" icon="edit" @click="openEditDialog(props.row)">
              <q-tooltip>Ubah Catatan & Status</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog Pendaftaran PSB Baru -->
    <q-dialog v-model="registerDialog" max-width="600px">
      <q-card style="width: 600px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="person_add" size="20px" />
            <span>Formulir Pendaftaran Calon Santri</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveRegistration">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">No. Pendaftaran</label>
                <q-input v-model="form.no_pendaftaran" outlined dense readonly bg-color="grey-2" />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Nama Panggilan</label>
                <q-input v-model="form.nama_panggilan" outlined dense placeholder="Ahmad" />
              </div>
            </div>

            <div>
              <label class="form-label">Nama Lengkap Calon Santri</label>
              <q-input v-model="form.nama_lengkap" outlined dense placeholder="Nama lengkap sesuai KK/Akta" :rules="[val => !!val || 'Wajib diisi']" />
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
                <label class="form-label">Asal Kota / Daerah</label>
                <q-input v-model="form.asal_kota" outlined dense placeholder="Jakarta, Bogor, dll." />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Nama Wali</label>
                <q-input v-model="form.nama_wali" outlined dense placeholder="Nama ayah/ibu/wali" :rules="[val => !!val || 'Nama wali wajib diisi']" />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">No. HP / WhatsApp Wali</label>
                <q-input v-model="form.no_hp_wali" outlined dense placeholder="08xxxxxxxxxx" :rules="[val => !!val || 'No. HP wajib diisi']" />
              </div>
            </div>

            <div>
              <label class="form-label">Alamat Lengkap</label>
              <q-input v-model="form.alamat_wali" outlined dense type="textarea" rows="2" placeholder="Alamat domisili lengkap..." />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Mendaftarkan...' : 'Daftarkan Calon Santri'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <!-- Dialog Ubah Status Seleksi & Wawancara -->
    <q-dialog v-model="editDialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="rate_review" size="20px" />
            <span>Ubah Status Seleksi PSB</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-md q-pa-sm-lg" v-if="selectedCalon">
          <div class="bg-grey-1 rounded-borders border q-pa-sm q-mb-md">
            <div class="text-subtitle2 text-weight-bold text-grey-9">
              {{ selectedCalon.nama_lengkap }}
            </div>
            <div class="text-caption text-grey-7">
              No. Pendaftaran: <span class="text-weight-bold text-primary">{{ selectedCalon.no_pendaftaran }}</span>
            </div>
          </div>

          <q-form @submit.prevent="updateSelectionStatus" class="column q-gutter-y-sm">
            <div>
              <label class="form-label">Status Hasil Seleksi</label>
              <q-select
                v-model="editForm.status_seleksi"
                outlined
                dense
                :options="[
                  {label:'Diproses (Menunggu Ujian/Wawancara)', value:'diproses'},
                  {label:'Diterima (Lulus Seleksi)', value:'diterima'},
                  {label:'Cadangan', value:'cadangan'},
                  {label:'Ditolak', value:'ditolak'}
                ]"
                emit-value
                map-options
              />
            </div>

            <div>
              <label class="form-label">Catatan Wawancara / Hasil Tes</label>
              <q-input
                v-model="editForm.catatan_wawancara"
                outlined
                dense
                type="textarea"
                rows="3"
                placeholder="Hasil tes tajwid, motivasi calon santri, kesehatan..."
              />
            </div>

            <div class="row justify-end q-mt-md q-gutter-sm">
              <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
              <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Status'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../utils/api';
import { toastSuccess, toastError, confirmDialog } from '../utils/sweetalert';
import { exportToCsv, printReport } from '../utils/export';

const loading = ref(false);
const saving = ref(false);
const calonList = ref([]);

const registerDialog = ref(false);
const editDialog = ref(false);
const selectedCalon = ref(null);

const form = ref({
  no_pendaftaran: '',
  nama_lengkap: '',
  nama_panggilan: '',
  jenis_kelamin: 'L',
  asal_kota: '',
  nama_wali: '',
  no_hp_wali: '',
  alamat_wali: ''
});

const editForm = ref({
  status_seleksi: 'diproses',
  catatan_wawancara: ''
});

const pagination = ref({ page: 1, rowsPerPage: 10, rowsNumber: 0 });

const columns = [
  { name: 'nama', label: 'Nama Calon Santri', field: 'nama_lengkap', align: 'left' },
  { name: 'asal', label: 'Asal Kota', field: 'asal_kota', align: 'left' },
  { name: 'wali', label: 'Wali & Kontak', field: 'nama_wali', align: 'left' },
  { name: 'status', label: 'Hasil Seleksi', field: 'status_seleksi', align: 'center' },
  { name: 'konversi', label: 'Status Santri', field: 'is_converted', align: 'center' },
  { name: 'aksi', label: 'Aksi', align: 'right' }
];

const getStatusColor = (status) => {
  switch (status) {
    case 'diterima': return 'positive';
    case 'diproses': return 'info';
    case 'cadangan': return 'warning';
    case 'ditolak': return 'negative';
    default: return 'grey-7';
  }
};

const loadData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/psb', {
      params: { page: pagination.value.page, per_page: pagination.value.rowsPerPage }
    });
    if (res.data.success) {
      calonList.value = res.data.data.data;
      pagination.value.rowsNumber = res.data.data.total;
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const onRequest = (props) => {
  pagination.value = props.pagination;
  loadData();
};

const openRegisterDialog = () => {
  form.value = {
    no_pendaftaran: 'PSB-' + new Date().getFullYear() + '-' + Math.floor(100 + Math.random() * 900),
    nama_lengkap: '',
    nama_panggilan: '',
    jenis_kelamin: 'L',
    asal_kota: '',
    nama_wali: '',
    no_hp_wali: '',
    alamat_wali: ''
  };
  registerDialog.value = true;
};

const saveRegistration = async () => {
  saving.value = true;
  try {
    await api.post('/psb', form.value);
    toastSuccess('Calon santri berhasil didaftarkan.');
    registerDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal mendaftarkan calon santri.');
  } finally {
    saving.value = false;
  }
};

const openEditDialog = (calon) => {
  selectedCalon.value = calon;
  editForm.value = {
    status_seleksi: calon.status_seleksi,
    catatan_wawancara: calon.catatan_wawancara || ''
  };
  editDialog.value = true;
};

const updateSelectionStatus = async () => {
  saving.value = true;
  try {
    await api.put(`/psb/${selectedCalon.value.id}`, editForm.value);
    toastSuccess('Status seleksi calon santri berhasil diperbarui.');
    editDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal memperbarui status seleksi.');
  } finally {
    saving.value = false;
  }
};

// 1-Click Conversion with SweetAlert2
const handleConvert = async (calon) => {
  const result = await confirmDialog({
    title: 'Konversi Menjadi Santri?',
    text: `Calon santri ${calon.nama_lengkap} akan langsung dijadikan Santri Aktif SIMTAQ tanpa input ulang biodata & wali.`,
    confirmButtonText: 'Ya, Konversi Sekarang!',
    icon: 'question'
  });

  if (result.isConfirmed) {
    try {
      const res = await api.post(`/psb/${calon.id}/convert`);
      if (res.data.success) {
        toastSuccess(`Alhamdulillah! ${calon.nama_lengkap} resmi menjadi santri aktif dengan NIS ${res.data.data.nis}`);
        loadData();
      }
    } catch (err) {
      toastError(err.response?.data?.message || 'Gagal melakukan konversi.');
    }
  }
};

const copyPublicLink = () => {
  const publicUrl = `${window.location.origin}/pendaftaran`;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(publicUrl).then(() => {
      toastSuccess(`Link pendaftaran publik berhasil disalin:\n${publicUrl}`);
    }).catch(() => {
      prompt('Salin link formulir PSB publik berikut:', publicUrl);
    });
  } else {
    prompt('Salin link formulir PSB publik berikut:', publicUrl);
  }
};

const handleExportPsb = () => {
  const exportCols = [
    { label: 'No. Pendaftaran', field: 'no_pendaftaran' },
    { label: 'Tanggal Daftar', field: 'tanggal_daftar' },
    { label: 'Nama Lengkap', field: 'nama_lengkap' },
    { label: 'Jenis Kelamin', field: row => row.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' },
    { label: 'Asal Kota', field: 'asal_kota' },
    { label: 'Asal Sekolah', field: 'asal_sekolah' },
    { label: 'Nama Wali', field: 'nama_wali' },
    { label: 'No. HP Wali', field: 'no_hp_wali' },
    { label: 'Status Seleksi', field: 'status_seleksi' },
    { label: 'Sudah Dikonversi', field: row => row.is_converted ? 'Ya' : 'Belum' }
  ];
  exportToCsv('Rekapitulasi_Pendaftaran_PSB', exportCols, calonList.value);
};

const handlePrintPsb = () => {
  printReport({
    title: 'REKAPITULASI PENDAFTARAN SANTRI BARU (PSB)',
    subtitle: 'Pondok Pesantren Tahfizul Qur\'an Yayasan Al Mukhlisin',
    stats: [
      { label: 'Total Pendaftar', value: calonList.value.length, color: '#0D7C66' },
      { label: 'Lolos Diterima', value: calonList.value.filter(c => c.status_seleksi === 'diterima').length, color: '#10B981' },
      { label: 'Sedang Diproses', value: calonList.value.filter(c => c.status_seleksi === 'diproses').length, color: '#3B82F6' },
      { label: 'Cadangan / Ditolak', value: calonList.value.filter(c => ['cadangan', 'ditolak'].includes(c.status_seleksi)).length, color: '#EF4444' }
    ],
    columns: [
      { label: 'No. Daftar', field: 'no_pendaftaran', align: 'center' },
      { label: 'Nama Calon Santri', field: 'nama_lengkap' },
      { label: 'L/P', field: 'jenis_kelamin', align: 'center' },
      { label: 'Asal Kota & Sekolah', field: row => `${row.asal_kota || '-'} (${row.asal_sekolah || '-'})` },
      { label: 'Wali & Kontak', field: row => `${row.nama_wali} (${row.no_hp_wali})` },
      { label: 'Tanggal Daftar', field: 'tanggal_daftar', align: 'center' },
      { label: 'Status', field: 'status_seleksi', align: 'center' }
    ],
    rows: calonList.value,
    signatories: [
      { title: 'Mengetahui,', role: 'Ketua Yayasan Al Mukhlisin', name: 'Ust. H. Ahmad Dahlan, Lc.' },
      { title: 'Ketua Panitia PSB,', role: 'Bagian Penerimaan Santri Baru', name: 'Ust. Ridwan Kamil, S.Pd.I.' }
    ]
  });
};

onMounted(() => {
  loadData();
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
