<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Kas Operasional Asrama</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Pencatatan uang kas harian asrama santri (kebersihan, listrik, dapur, & darurat).</p>
      </div>

      <div class="row items-center q-gutter-xs">
        <q-btn
          outline
          color="primary"
          icon="print"
          label="Cetak Laporan"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold gt-xs"
          @click="handlePrintReport"
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
          @click="handleExportCsv"
        />
        <q-btn
          color="primary"
          icon="add_card"
          label="Catat Kas"
          unelevated
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="openDialog"
        />
      </div>
    </div>

    <!-- Scope Selector (Pengurus only can switch) -->
    <div v-if="authStore.isPengurus" class="q-mb-sm q-mb-md-md">
      <q-btn-toggle
        v-model="currentScope"
        no-caps
        rounded
        unelevated
        toggle-color="primary"
        color="white"
        text-color="grey-8"
        class="shadow-1"
        :options="[
          { label: 'Asrama Putra (Ikhwan)', value: 'putra' },
          { label: 'Asrama Putri (Akhwat)', value: 'putri' }
        ]"
        @update:model-value="loadData"
      />
    </div>

    <!-- 3 Summary Balance Cards -->
    <div class="row q-col-gutter-xs q-col-gutter-sm-sm q-mb-sm q-mb-md-md">
      <div class="col-12 col-sm-4">
        <div
          class="text-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between"
          :class="currentScope === 'putra' ? 'bg-blue-9' : 'bg-purple-9'"
        >
          <div class="text-caption text-xs" :class="currentScope === 'putra' ? 'text-blue-2' : 'text-purple-2'">
            Saldo Kas Operasional ({{ currentScope.toUpperCase() }})
          </div>
          <div class="text-h5 text-md-h4 text-weight-bolder q-my-xs ellipsis">
            {{ ringkasan.saldo_terkini_formatted || 'Rp 0' }}
          </div>
          <div class="text-xs" :class="currentScope === 'putra' ? 'text-blue-2' : 'text-purple-2'">
            Saldo kas riil asrama
          </div>
        </div>
      </div>

      <div class="col-6 col-sm-4">
        <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between border">
          <div class="text-caption text-grey-6 text-xs">Total Dropping & Masuk</div>
          <div class="text-subtitle1 text-md-h5 text-weight-bolder text-positive q-my-xs ellipsis">
            {{ ringkasan.total_masuk_formatted || 'Rp 0' }}
          </div>
          <div class="text-xs text-grey-5">Dropping yayasan & lainnya</div>
        </div>
      </div>

      <div class="col-6 col-sm-4">
        <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-md full-height column justify-between border">
          <div class="text-caption text-grey-6 text-xs">Total Belanja Operasional</div>
          <div class="text-subtitle1 text-md-h5 text-weight-bolder text-negative q-my-xs ellipsis">
            {{ ringkasan.total_keluar_formatted || 'Rp 0' }}
          </div>
          <div class="text-xs text-grey-5">Belanja santri & asrama</div>
        </div>
      </div>
    </div>

    <!-- Transactions Table & Mobile Cards -->
    <div class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="transaksiList"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :grid="$q.screen.xs"
        v-model:pagination="pagination"
        @request="onRequest"
        flat
        separator="horizontal"
      >
        <!-- MOBILE CARD -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <div class="column">
                  <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.kode_transaksi }}</span>
                  <span class="text-caption text-grey-6 text-xs">{{ props.row.kategori }}</span>
                </div>
                <q-chip
                  dense
                  size="xs"
                  :color="props.row.jenis === 'pemasukan' ? 'positive' : 'negative'"
                  text-color="white"
                  class="text-capitalize"
                >
                  {{ props.row.jenis }}
                </q-chip>
              </div>

              <div class="text-caption text-grey-8 text-xs q-py-xs">
                {{ props.row.keterangan }}
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs">
                <span class="text-caption text-grey-5 text-xs">{{ props.row.tanggal }}</span>
                <div class="column items-end">
                  <span
                    class="text-weight-bolder text-body2"
                    :class="props.row.jenis === 'pemasukan' ? 'text-positive' : 'text-negative'"
                  >
                    {{ props.row.jenis === 'pemasukan' ? '+' : '-' }} {{ props.row.nominal_formatted }}
                  </span>
                  <span class="text-caption text-grey-5" style="font-size: 10px;">
                    Saldo: {{ props.row.saldo_berjalan_formatted }}
                  </span>
                </div>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-kode="props">
          <q-td :props="props">
            <div class="column">
              <span class="text-weight-bold text-grey-9">{{ props.row.kode_transaksi }}</span>
              <span class="text-caption text-grey-6">{{ props.row.kategori }}</span>
            </div>
          </q-td>
        </template>

        <template #body-cell-nominal="props">
          <q-td :props="props" align="right">
            <div class="column items-end">
              <span
                class="text-weight-bolder"
                :class="props.row.jenis === 'pemasukan' ? 'text-positive' : 'text-negative'"
              >
                {{ props.row.jenis === 'pemasukan' ? '+' : '-' }} {{ props.row.nominal_formatted }}
              </span>
              <span class="text-caption text-grey-6">
                Saldo: {{ props.row.saldo_berjalan_formatted }}
              </span>
            </div>
          </q-td>
        </template>

        <template #body-cell-jenis="props">
          <q-td :props="props" align="center">
            <q-chip
              dense
              size="sm"
              :color="props.row.jenis === 'pemasukan' ? 'positive' : 'negative'"
              text-color="white"
              class="text-capitalize"
            >
              {{ props.row.jenis }}
            </q-chip>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog Catat Kas Operasional -->
    <q-dialog v-model="dialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="receipt_long" size="20px" />
            <span>Catat Kas Operasional ({{ currentScope.toUpperCase() }})</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveTransaksi">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Jenis Transaksi</label>
                <q-select
                  v-model="form.jenis"
                  outlined
                  dense
                  :options="[{label:'Pengeluaran', value:'pengeluaran'}, {label:'Pemasukan', value:'pemasukan'}]"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Kategori</label>
                <q-input v-model="form.kategori" outlined dense placeholder="Kebersihan / Dapur / Listrik" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Nominal (Rp)</label>
                <q-input v-model.number="form.nominal" outlined dense type="number" min="1000" placeholder="Contoh: 50000" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Tanggal Transaksi</label>
                <q-input v-model="form.tanggal" outlined dense type="date" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
            </div>

            <div>
              <label class="form-label">Keterangan Belanja / Penggunaan</label>
              <q-input v-model="form.keterangan" type="textarea" outlined dense rows="2" placeholder="Beli sabun pembersih lantai, bola lampu kamar mandi..." :rules="[val => !!val || 'Wajib diisi']" />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Kas Operasional'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
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
const currentScope = ref(authStore.genderScope || 'putra');

const loading = ref(false);
const saving = ref(false);
const transaksiList = ref([]);
const ringkasan = ref({});
const dialog = ref(false);

const form = ref({
  jenis: 'pengeluaran',
  kategori: 'Kebersihan',
  nominal: 50000,
  tanggal: new Date().toISOString().substring(0, 10),
  keterangan: '',
  scope: currentScope.value
});

const pagination = ref({ page: 1, rowsPerPage: 10, rowsNumber: 0 });

const columns = [
  { name: 'kode', label: 'Kode & Kategori', field: 'kode_transaksi', align: 'left' },
  { name: 'keterangan', label: 'Uraian Transaksi', field: 'keterangan', align: 'left' },
  { name: 'jenis', label: 'Jenis', field: 'jenis', align: 'center' },
  { name: 'nominal', label: 'Nominal & Saldo Berjalan', field: 'nominal', align: 'right' },
  { name: 'tanggal', label: 'Tanggal', field: 'tanggal', align: 'right' }
];

const loadData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/kas-operasional', {
      params: {
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage,
        scope: currentScope.value
      }
    });
    if (res.data.success) {
      transaksiList.value = res.data.data.transaksi.data;
      pagination.value.rowsNumber = res.data.data.transaksi.total;
      ringkasan.value = res.data.data.ringkasan;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const onRequest = (props) => {
  pagination.value = props.pagination;
  loadData();
};

const openDialog = () => {
  form.value = {
    jenis: 'pengeluaran',
    kategori: 'Kebersihan',
    nominal: 50000,
    tanggal: new Date().toISOString().substring(0, 10),
    keterangan: '',
    scope: currentScope.value
  };
  dialog.value = true;
};

const saveTransaksi = async () => {
  saving.value = true;
  form.value.scope = currentScope.value;
  try {
    await api.post('/kas-operasional', form.value);
    toastSuccess('Transaksi kas operasional berhasil disimpan.');
    dialog.value = false;
    loadData();
  } catch (e) {
    toastError(e.response?.data?.message || 'Gagal menyimpan transaksi.');
  } finally {
    saving.value = false;
  }
};

const handleExportCsv = () => {
  const scopeName = currentScope.value === 'putra' ? 'Putra' : 'Putri';
  const exportCols = [
    { label: 'Kode Transaksi', field: 'kode_transaksi' },
    { label: 'Tanggal', field: 'tanggal' },
    { label: 'Kategori', field: 'kategori' },
    { label: 'Uraian Keterangan', field: 'keterangan' },
    { label: 'Jenis', field: 'jenis' },
    { label: 'Pemasukan (Rp)', field: row => row.jenis === 'pemasukan' ? row.nominal : 0 },
    { label: 'Pengeluaran (Rp)', field: row => row.jenis === 'pengeluaran' ? row.nominal : 0 },
    { label: 'Saldo Berjalan (Rp)', field: 'saldo_sesudah' }
  ];
  exportToCsv(`Laporan_Kas_Operasional_${scopeName}`, exportCols, transaksiList.value);
};

const handlePrintReport = () => {
  const scopeName = currentScope.value === 'putra' ? 'Asrama Putra (Ikhwan)' : 'Asrama Putri (Akhwat)';
  printReport({
    title: `LAPORAN KAS OPERASIONAL ${scopeName.toUpperCase()}`,
    subtitle: 'Rekapitulasi Keuangan & Pembukuan Operasional Harian Santri',
    stats: [
      { label: 'Saldo Kas Asrama', value: ringkasan.value.saldo_terkini_formatted || 'Rp 0', color: '#0D7C66' },
      { label: 'Total Pemasukan', value: ringkasan.value.total_masuk_formatted || 'Rp 0', color: '#10B981' },
      { label: 'Total Pengeluaran', value: ringkasan.value.total_keluar_formatted || 'Rp 0', color: '#EF4444' }
    ],
    columns: [
      { label: 'Tanggal', field: 'tanggal', align: 'center' },
      { label: 'Kode & Kategori', field: row => `${row.kode_transaksi} (${row.kategori})` },
      { label: 'Uraian Keterangan', field: 'keterangan' },
      { label: 'Pemasukan', field: row => row.jenis === 'pemasukan' ? `Rp ${Number(row.nominal).toLocaleString('id-ID')}` : '-', align: 'right' },
      { label: 'Pengeluaran', field: row => row.jenis === 'pengeluaran' ? `Rp ${Number(row.nominal).toLocaleString('id-ID')}` : '-', align: 'right' },
      { label: 'Saldo Berjalan', field: row => `Rp ${Number(row.saldo_sesudah).toLocaleString('id-ID')}`, align: 'right' }
    ],
    rows: transaksiList.value,
    signatories: [
      { title: 'Mengetahui,', role: 'Ketua Yayasan / Pimpinan Pondok', name: 'Ust. H. Ahmad Dahlan, Lc.' },
      { title: 'Dibuat Oleh,', role: `Ketua Santri ${scopeName}`, name: authStore.userName || 'Ketua Asrama' }
    ]
  });
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.border {
  border: 1px solid #E2E8F0;
}
.border-top {
  border-top: 1px solid #F1F5F9;
}
.text-xs {
  font-size: 0.75rem;
}
</style>
