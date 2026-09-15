<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Pengelolaan Donatur & Donasi</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Pencatatan infaq, sedekah uang, logistik makanan, serta database muhsinin/donatur.</p>
      </div>

      <div class="row q-gutter-xs q-mt-xs q-mt-md-none">
        <q-btn
          color="primary"
          icon="volunteer_activism"
          label="Catat Donasi"
          unelevated
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="openDonasiDialog"
        />
        <q-btn
          outline
          color="primary"
          icon="person_add"
          label="Tambah Donatur"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders"
          @click="openDonaturDialog"
        />
      </div>
    </div>

    <!-- Tabs Navigation -->
    <q-card class="shadow-1 rounded-borders q-mb-sm q-mb-md-md">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey-7"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
      >
        <q-tab name="donasi" icon="paid" label="Riwayat Donasi Masuk" />
        <q-tab name="donatur" icon="people" label="Database Donatur (Muhsinin)" />
      </q-tabs>
    </q-card>

    <!-- Tab 1: Riwayat Donasi -->
    <div v-if="tab === 'donasi'" class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="donasiList"
        :columns="donasiColumns"
        row-key="id"
        :loading="loadingDonasi"
        :grid="$q.screen.xs"
        flat
        separator="horizontal"
      >
        <!-- MOBILE CARD FOR DONASI -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.nama_donatur_display }}</span>
                <q-chip
                  dense
                  size="xs"
                  :color="props.row.jenis_donasi === 'uang' ? 'positive' : 'amber-9'"
                  text-color="white"
                  class="text-capitalize"
                >
                  {{ props.row.jenis_donasi }}
                </q-chip>
              </div>

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div v-if="props.row.jenis_donasi === 'uang'" class="text-h6 text-weight-bolder text-positive">
                  {{ props.row.nominal_formatted }}
                </div>
                <div v-else class="text-weight-bold text-grey-9">
                  {{ props.row.jumlah_barang }} {{ props.row.nama_barang }}
                </div>
                <div class="text-grey-6">{{ props.row.keterangan || 'Infaq umum' }}</div>
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs text-xs text-grey-5">
                <span>{{ props.row.kode_donasi }}</span>
                <span>{{ props.row.tanggal_donasi }}</span>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-donatur="props">
          <q-td :props="props">
            <div class="column">
              <span class="text-weight-bold text-grey-9">{{ props.row.nama_donatur_display }}</span>
              <span class="text-caption text-grey-6">{{ props.row.kode_donasi }}</span>
            </div>
          </q-td>
        </template>

        <template #body-cell-bentuk="props">
          <q-td :props="props">
            <div v-if="props.row.jenis_donasi === 'uang'" class="column">
              <span class="text-weight-bolder text-positive">{{ props.row.nominal_formatted }}</span>
              <span class="text-caption text-grey-6">Uang Tunai / Transfer</span>
            </div>
            <div v-else class="column">
              <span class="text-weight-bold text-grey-9">{{ props.row.jumlah_barang }} {{ props.row.nama_barang }}</span>
              <span class="text-caption text-amber-9 text-capitalize">{{ props.row.jenis_donasi }}</span>
            </div>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Tab 2: Database Donatur -->
    <div v-if="tab === 'donatur'" class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="donaturList"
        :columns="donaturColumns"
        row-key="id"
        :loading="loadingDonatur"
        :grid="$q.screen.xs"
        flat
        separator="horizontal"
      >
        <!-- MOBILE CARD FOR DONATUR -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.nama }}</span>
                <q-badge :color="props.row.kategori === 'rutin' ? 'primary' : 'teal-7'" class="text-xs">
                  {{ props.row.kategori?.toUpperCase() }}
                </q-badge>
              </div>

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div>Kontak: <span class="text-weight-medium text-grey-9">{{ props.row.no_hp || '-' }}</span></div>
                <div>Tipe: <span class="text-capitalize">{{ props.row.tipe_donatur?.replace('_', ' ') }}</span></div>
                <div v-if="props.row.alamat">Alamat: {{ props.row.alamat }}</div>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-kategori="props">
          <q-td :props="props" align="center">
            <q-badge :color="props.row.kategori === 'rutin' ? 'primary' : 'teal-7'">
              {{ props.row.kategori?.toUpperCase() }}
            </q-badge>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog Catat Donasi Baru -->
    <!-- Dialog Catat Donasi Baru -->
    <q-dialog v-model="donasiDialog" max-width="520px">
      <q-card style="width: 520px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="volunteer_activism" size="20px" />
            <span>Pencatatan Donasi Baru</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveDonasi">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Jenis Donasi</label>
              <q-select
                v-model="donasiForm.jenis_donasi"
                outlined
                dense
                :options="[{label:'Uang Tunai / Transfer', value:'uang'}, {label:'Makanan / Konsumsi', value:'makanan'}, {label:'Barang / Logistik', value:'barang'}]"
                emit-value
                map-options
              />
            </div>

            <div>
              <label class="form-label">Nama Donatur</label>
              <q-input v-model="donasiForm.nama_donatur_manual" outlined dense placeholder="Nama donatur atau Hamba Allah..." :rules="[val => !!val || 'Wajib diisi']" />
            </div>

            <div v-if="donasiForm.jenis_donasi === 'uang'">
              <label class="form-label">Nominal Donasi (Rp)</label>
              <q-input v-model.number="donasiForm.nominal" outlined dense type="number" min="1000" placeholder="Contoh: 100000" :rules="[val => !!val || 'Nominal wajib diisi']" />

              <q-checkbox v-model="donasiForm.masuk_ke_kas_yayasan" label="Otomatis masukkan ke Kas Utama Yayasan" color="primary" dense class="q-mt-xs text-caption text-weight-medium" />
            </div>

            <div v-else class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-8">
                <label class="form-label">Nama Barang / Makanan</label>
                <q-input v-model="donasiForm.nama_barang" outlined dense placeholder="Beras / Buah / Mushaf" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
              <div class="col-12 col-sm-4">
                <label class="form-label">Jumlah</label>
                <q-input v-model="donasiForm.jumlah_barang" outlined dense placeholder="2 Karung / 5 Dus" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
            </div>

            <div>
              <label class="form-label">Tanggal Donasi</label>
              <q-input v-model="donasiForm.tanggal_donasi" outlined dense type="date" :rules="[val => !!val || 'Wajib diisi']" />
            </div>

            <div>
              <label class="form-label">Keterangan / Akad</label>
              <q-input v-model="donasiForm.keterangan" outlined dense placeholder="Infaq santri yatim, sedekah subuh, dll." />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Donasi'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <!-- Dialog Tambah Donatur Baru -->
    <q-dialog v-model="donaturDialog" max-width="480px">
      <q-card style="width: 480px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="person_add" size="20px" />
            <span>Tambah Data Donatur Baru</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveDonatur">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Nama Donatur / Instansi</label>
              <q-input v-model="donaturForm.nama" outlined dense placeholder="Nama perorangan atau yayasan/perusahaan" :rules="[val => !!val || 'Wajib diisi']" />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Tipe Donatur</label>
                <q-select v-model="donaturForm.tipe_donatur" outlined dense :options="[{label:'Perorangan', value:'perorangan'}, {label:'Lembaga', value:'lembaga'}, {label:'Hamba Allah', value:'hamba_allah'}]" emit-value map-options />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Kategori</label>
                <q-select v-model="donaturForm.kategori" outlined dense :options="[{label:'Donatur Rutin', value:'rutin'}, {label:'Insidental', value:'insidental'}]" emit-value map-options />
              </div>
            </div>

            <div>
              <label class="form-label">Nomor WhatsApp / HP</label>
              <q-input v-model="donaturForm.no_hp" outlined dense placeholder="08xxxxxxxxxx" />
            </div>

            <div>
              <label class="form-label">Alamat Donatur</label>
              <q-input v-model="donaturForm.alamat" outlined dense type="textarea" rows="2" placeholder="Alamat atau domisili donatur..." />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Donatur'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../utils/api';
import { toastSuccess, toastError } from '../utils/sweetalert';

const tab = ref('donasi');
const loadingDonasi = ref(false);
const loadingDonatur = ref(false);
const saving = ref(false);

const donasiList = ref([]);
const donaturList = ref([]);

const donasiDialog = ref(false);
const donaturDialog = ref(false);

const donasiForm = ref({
  jenis_donasi: 'uang',
  nama_donatur_manual: '',
  nominal: 100000,
  nama_barang: '',
  jumlah_barang: '',
  tanggal_donasi: new Date().toISOString().substring(0, 10),
  masuk_ke_kas_yayasan: true,
  keterangan: ''
});

const donaturForm = ref({
  nama: '',
  tipe_donatur: 'perorangan',
  kategori: 'rutin',
  no_hp: '',
  alamat: ''
});

const donasiColumns = [
  { name: 'donatur', label: 'Donatur & Kode', field: 'nama_donatur_display', align: 'left' },
  { name: 'bentuk', label: 'Bentuk & Nilai Donasi', field: 'nominal', align: 'left' },
  { name: 'keterangan', label: 'Keterangan', field: 'keterangan', align: 'left' },
  { name: 'tanggal', label: 'Tanggal', field: 'tanggal_donasi', align: 'right' }
];

const donaturColumns = [
  { name: 'nama', label: 'Nama Donatur / Instansi', field: 'nama', align: 'left' },
  { name: 'tipe', label: 'Tipe', field: 'tipe_donatur', align: 'left' },
  { name: 'kategori', label: 'Kategori', field: 'kategori', align: 'center' },
  { name: 'kontak', label: 'No. HP', field: 'no_hp', align: 'left' },
  { name: 'alamat', label: 'Alamat', field: 'alamat', align: 'left' }
];

const loadDonasi = async () => {
  loadingDonasi.value = true;
  try {
    const res = await api.get('/donasi');
    if (res.data.success) {
      donasiList.value = res.data.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loadingDonasi.value = false;
  }
};

const loadDonatur = async () => {
  loadingDonatur.value = true;
  try {
    const res = await api.get('/donatur');
    if (res.data.success) {
      donaturList.value = res.data.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loadingDonatur.value = false;
  }
};

const openDonasiDialog = () => {
  donasiForm.value = {
    jenis_donasi: 'uang',
    nama_donatur_manual: '',
    nominal: 100000,
    nama_barang: '',
    jumlah_barang: '',
    tanggal_donasi: new Date().toISOString().substring(0, 10),
    masuk_ke_kas_yayasan: true,
    keterangan: ''
  };
  donasiDialog.value = true;
};

const saveDonasi = async () => {
  saving.value = true;
  try {
    await api.post('/donasi', donasiForm.value);
    toastSuccess('Donasi berhasil dicatat.');
    donasiDialog.value = false;
    loadDonasi();
  } catch (e) {
    toastError(e.response?.data?.message || 'Gagal mencatat donasi.');
  } finally {
    saving.value = false;
  }
};

const openDonaturDialog = () => {
  donaturForm.value = {
    nama: '',
    tipe_donatur: 'perorangan',
    kategori: 'rutin',
    no_hp: '',
    alamat: ''
  };
  donaturDialog.value = true;
};

const saveDonatur = async () => {
  saving.value = true;
  try {
    await api.post('/donatur', donaturForm.value);
    toastSuccess('Donatur berhasil ditambahkan.');
    donaturDialog.value = false;
    loadDonatur();
  } catch (e) {
    toastError(e.response?.data?.message || 'Gagal menambahkan donatur.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadDonasi();
  loadDonatur();
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
