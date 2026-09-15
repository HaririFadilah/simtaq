<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Hafalan & Tahfiz Al-Qur'an</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Pencatatan setoran harian, sima'an muroja'ah per juz, dan ujian evaluasi akbar kelipatan 5 juz.</p>
      </div>

      <div class="row items-center q-gutter-xs q-mt-xs q-mt-md-none">
        <q-btn
          outline
          color="primary"
          icon="print"
          label="Cetak Rekap"
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold gt-xs"
          @click="handlePrintTahfiz"
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
          @click="handleExportTahfiz"
        />
        <q-btn
          v-if="currentTab === 'setoran'"
          color="primary"
          icon="add_circle"
          label="Setoran Baru"
          unelevated
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="openSetoranDialog"
        />
        <q-btn
          v-else-if="currentTab === 'murojaah'"
          color="teal-8"
          icon="verified"
          label="Muroja'ah 1 Juz"
          unelevated
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="openMurojaahDialog"
        />
        <q-btn
          v-else-if="currentTab === 'evaluasi'"
          color="amber-9"
          icon="workspace_premium"
          label="Evaluasi Akbar"
          unelevated
          no-caps
          size="sm"
          size-md="md"
          class="rounded-borders text-weight-bold"
          @click="openEvaluasiDialog"
        />
      </div>
    </div>

    <!-- Navigation Tabs -->
    <q-card class="shadow-1 rounded-borders q-mb-sm q-mb-md-md">
      <q-tabs
        v-model="currentTab"
        dense
        class="text-grey-7"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
      >
        <q-tab name="setoran" icon="menu_book" label="1. Setoran Harian" />
        <q-tab name="murojaah" icon="repeat" label="2. Sima'an Muroja'ah (1 Juz)" />
        <q-tab name="evaluasi" icon="stars" label="3. Evaluasi Akbar (Kelipatan 5 Juz)" />
      </q-tabs>
    </q-card>

    <!-- Tab 1: Setoran Harian -->
    <div v-if="currentTab === 'setoran'" class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="setoranList"
        :columns="setoranColumns"
        row-key="id"
        :loading="loading"
        :grid="$q.screen.xs"
        flat
        separator="horizontal"
      >
        <!-- MOBILE CARD -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.santri?.nama_lengkap }}</span>
                <q-badge color="primary" class="text-xs">Juz {{ props.row.juz }}</q-badge>
              </div>

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div>Surat: <span class="text-weight-medium text-grey-9">{{ props.row.surat }} (Ayat {{ props.row.ayat_awal }} - {{ props.row.ayat_akhir }})</span></div>
                <div>Halaman: <span class="text-weight-bold">{{ props.row.halaman }}</span> &bull; Kualitas: <span class="text-weight-bold text-capitalize text-primary">{{ props.row.kualitas }}</span></div>
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs text-xs text-grey-5">
                <span>Pengampu: {{ props.row.pengampu?.name || 'Ustadz' }}</span>
                <span>{{ props.row.tanggal_setoran }}</span>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <div class="column">
              <span class="text-weight-bold text-grey-9">{{ props.row.santri?.nama_lengkap }}</span>
              <span class="text-caption text-grey-6">{{ props.row.santri?.nis }}</span>
            </div>
          </q-td>
        </template>
        <template #body-cell-juz="props">
          <q-td :props="props" align="center">
            <q-badge color="primary" class="q-pa-xs">Juz {{ props.row.juz }}</q-badge>
          </q-td>
        </template>
        <template #body-cell-kualitas="props">
          <q-td :props="props" align="center">
            <q-badge :color="props.row.kualitas === 'lancar' ? 'positive' : 'warning'" class="text-capitalize q-pa-xs">
              {{ props.row.kualitas }}
            </q-badge>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Tab 2: Sima'an Muroja'ah (1 Juz) -->
    <div v-if="currentTab === 'murojaah'" class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="murojaahList"
        :columns="murojaahColumns"
        row-key="id"
        :loading="loading"
        :grid="$q.screen.xs"
        flat
        separator="horizontal"
      >
        <!-- MOBILE CARD -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.santri?.nama_lengkap }}</span>
                <q-chip dense size="xs" :color="props.row.status === 'lulus' ? 'positive' : 'warning'" text-color="white" class="text-capitalize">
                  {{ props.row.status }}
                </q-chip>
              </div>

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div>Juz Diuji: <span class="text-weight-bold text-teal-8">Juz {{ props.row.juz }}</span></div>
                <div>Predikat Nilai: <span class="text-weight-bold">{{ props.row.nilai_predikat }}</span> (Salah: {{ props.row.jumlah_salah }})</div>
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs text-xs text-grey-5">
                <span>Penguji: {{ props.row.penguji?.name || 'Ustadz' }}</span>
                <span>{{ props.row.tanggal_ujian }}</span>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <span class="text-weight-bold text-grey-9">{{ props.row.santri?.nama_lengkap }}</span>
          </q-td>
        </template>
        <template #body-cell-juz="props">
          <q-td :props="props" align="center">
            <q-badge color="teal-8" class="q-pa-xs">Juz {{ props.row.juz }}</q-badge>
          </q-td>
        </template>
        <template #body-cell-status="props">
          <q-td :props="props" align="center">
            <q-chip dense size="sm" :color="props.row.status === 'lulus' ? 'positive' : 'warning'" text-color="white" class="text-capitalize">
              {{ props.row.status }}
            </q-chip>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Tab 3: Evaluasi Akbar -->
    <div v-if="currentTab === 'evaluasi'" class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="evaluasiList"
        :columns="evaluasiColumns"
        row-key="id"
        :loading="loading"
        :grid="$q.screen.xs"
        flat
        separator="horizontal"
      >
        <!-- MOBILE CARD -->
        <template #item="props">
          <div class="col-12 q-pa-xs">
            <q-card class="shadow-1 rounded-borders q-pa-sm bg-white border">
              <div class="row items-center justify-between no-wrap q-mb-xs">
                <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.santri?.nama_lengkap }}</span>
                <q-chip dense size="xs" :color="props.row.status === 'lulus' ? 'positive' : 'warning'" text-color="white" class="text-capitalize">
                  {{ props.row.status }}
                </q-chip>
              </div>

              <div class="text-caption text-grey-7 text-xs q-py-xs">
                <div>Kategori Ujian: <span class="text-weight-bold text-amber-9">{{ props.row.kategori_juz }} Juz Sekali Duduk</span></div>
                <div>Predikat: <span class="text-weight-bold">{{ props.row.predikat }}</span> (Nilai: {{ props.row.nilai_akhir }})</div>
              </div>

              <div class="row items-center justify-between q-mt-xs border-top q-pt-xs text-xs text-grey-5">
                <span>Penguji: {{ props.row.penguji?.name || 'Dewan Ustadz' }}</span>
                <span>{{ props.row.tanggal_ujian }}</span>
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <span class="text-weight-bold text-grey-9">{{ props.row.santri?.nama_lengkap }}</span>
          </q-td>
        </template>
        <template #body-cell-kategori="props">
          <q-td :props="props" align="center">
            <q-badge color="amber-9" class="q-pa-xs text-weight-bold">
              {{ props.row.kategori_juz }} Juz
            </q-badge>
          </q-td>
        </template>
        <template #body-cell-status="props">
          <q-td :props="props" align="center">
            <q-chip dense size="sm" :color="props.row.status === 'lulus' ? 'positive' : 'warning'" text-color="white" class="text-capitalize">
              {{ props.row.status }}
            </q-chip>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Modal Setoran Harian -->
    <q-dialog v-model="setoranDialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="menu_book" size="20px" />
            <span>Catat Setoran Harian Baru</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveSetoran">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Pilih Santri</label>
              <q-select
                v-model="setoranForm.santri_id"
                outlined
                dense
                :options="santriOptions"
                option-value="id"
                option-label="label"
                emit-value
                map-options
                :rules="[val => !!val || 'Santri wajib dipilih']"
              />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-4">
                <label class="form-label">Juz</label>
                <q-input v-model.number="setoranForm.juz" outlined dense type="number" min="1" max="30" :rules="[val => !!val || 'Wajib']" />
              </div>
              <div class="col-12 col-sm-4">
                <label class="form-label">Halaman</label>
                <q-input v-model.number="setoranForm.halaman" outlined dense type="number" min="1" max="604" :rules="[val => !!val || 'Wajib']" />
              </div>
              <div class="col-12 col-sm-4">
                <label class="form-label">Kualitas</label>
                <q-select
                  v-model="setoranForm.kualitas"
                  outlined
                  dense
                  :options="[{label:'Lancar (Mutqin)', value:'lancar'}, {label:'Cukup', value:'cukup'}, {label:'Kurang', value:'kurang'}]"
                  emit-value
                  map-options
                />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Nama Surat</label>
                <q-input v-model="setoranForm.surat" outlined dense placeholder="Contoh: Al-Baqarah" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
              <div class="col-6 col-sm-3">
                <label class="form-label">Ayat Awal</label>
                <q-input v-model.number="setoranForm.ayat_awal" outlined dense type="number" min="1" />
              </div>
              <div class="col-6 col-sm-3">
                <label class="form-label">Ayat Akhir</label>
                <q-input v-model.number="setoranForm.ayat_akhir" outlined dense type="number" min="1" />
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Setoran'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <!-- Modal Murojaah 1 Juz -->
    <q-dialog v-model="murojaahDialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-teal-8 text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="repeat" size="20px" />
            <span>Catat Sima'an Muroja'ah (1 Juz)</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveMurojaah">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Pilih Santri</label>
              <q-select
                v-model="murojaahForm.santri_id"
                outlined
                dense
                :options="santriOptions"
                option-value="id"
                option-label="label"
                emit-value
                map-options
                :rules="[val => !!val || 'Santri wajib dipilih']"
              />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-4">
                <label class="form-label">Juz Diuji</label>
                <q-input v-model.number="murojaahForm.juz" outlined dense type="number" min="1" max="30" :rules="[val => !!val || 'Wajib']" />
              </div>
              <div class="col-12 col-sm-4">
                <label class="form-label">Jumlah Salah</label>
                <q-input v-model.number="murojaahForm.jumlah_salah" outlined dense type="number" min="0" />
              </div>
              <div class="col-12 col-sm-4">
                <label class="form-label">Predikat</label>
                <q-select
                  v-model="murojaahForm.nilai_predikat"
                  outlined
                  dense
                  :options="['Mumtaz', 'Jayyid Jiddan', 'Jayyid', 'Maqbul']"
                />
              </div>
            </div>

            <div>
              <label class="form-label">Hasil Kelulusan</label>
              <q-select
                v-model="murojaahForm.status"
                outlined
                dense
                :options="[{label:'Lulus Murojaah', value:'lulus'}, {label:'Mengulang Simaah', value:'mengulang'}]"
                emit-value
                map-options
              />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Muroja\'ah'" color="teal-8" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <!-- Modal Evaluasi Akbar (5 Juz) -->
    <q-dialog v-model="evaluasiDialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-amber-9 text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon name="stars" size="20px" />
            <span>Ujian Evaluasi Akbar Kelipatan 5 Juz</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveEvaluasi">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Pilih Santri</label>
              <q-select
                v-model="evaluasiForm.santri_id"
                outlined
                dense
                :options="santriOptions"
                option-value="id"
                option-label="label"
                emit-value
                map-options
                :rules="[val => !!val || 'Santri wajib dipilih']"
              />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Kategori Ujian</label>
                <q-select
                  v-model.number="evaluasiForm.kategori_juz"
                  outlined
                  dense
                  :options="[5, 10, 15, 20, 25, 30]"
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Nilai Angka (0-100)</label>
                <q-input v-model.number="evaluasiForm.nilai_akhir" outlined dense type="number" min="0" max="100" />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Predikat</label>
                <q-select
                  v-model="evaluasiForm.predikat"
                  outlined
                  dense
                  :options="['Mumtaz', 'Jayyid Jiddan', 'Jayyid', 'Maqbul']"
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Status Kelulusan</label>
                <q-select
                  v-model="evaluasiForm.status"
                  outlined
                  dense
                  :options="[{label:'Lulus Evaluasi', value:'lulus'}, {label:'Mengulang', value:'mengulang'}]"
                  emit-value
                  map-options
                />
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Evaluasi'" color="amber-9" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
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
import { exportToCsv, printReport } from '../utils/export';

const currentTab = ref('setoran');
const loading = ref(false);
const saving = ref(false);

const setoranList = ref([]);
const murojaahList = ref([]);
const evaluasiList = ref([]);
const santriOptions = ref([]);

const setoranDialog = ref(false);
const murojaahDialog = ref(false);
const evaluasiDialog = ref(false);

const setoranForm = ref({
  santri_id: null,
  juz: 1,
  surat: '',
  ayat_awal: 1,
  ayat_akhir: 10,
  halaman: 1,
  kualitas: 'lancar',
  tanggal_setoran: new Date().toISOString().substring(0, 10)
});

const murojaahForm = ref({
  santri_id: null,
  juz: 1,
  jumlah_salah: 0,
  nilai_predikat: 'Mumtaz',
  status: 'lulus',
  tanggal_ujian: new Date().toISOString().substring(0, 10)
});

const evaluasiForm = ref({
  santri_id: null,
  kategori_juz: 5,
  nilai_akhir: 90,
  predikat: 'Mumtaz',
  status: 'lulus',
  tanggal_ujian: new Date().toISOString().substring(0, 10)
});

const setoranColumns = [
  { name: 'nama', label: 'Nama Santri', field: 'santri', align: 'left' },
  { name: 'juz', label: 'Juz', field: 'juz', align: 'center' },
  { name: 'surat', label: 'Surat & Halaman', field: 'surat', align: 'left', format: (val, row) => `${val} (${row.ayat_awal}-${row.ayat_akhir}) - Hlm ${row.halaman}` },
  { name: 'kualitas', label: 'Kualitas', field: 'kualitas', align: 'center' },
  { name: 'tanggal', label: 'Tanggal', field: 'tanggal_setoran', align: 'right' }
];

const murojaahColumns = [
  { name: 'nama', label: 'Nama Santri', field: 'santri', align: 'left' },
  { name: 'juz', label: 'Juz Diuji', field: 'juz', align: 'center' },
  { name: 'predikat', label: 'Predikat (Salah)', field: 'nilai_predikat', align: 'center', format: (val, row) => `${val} (${row.jumlah_salah} salah)` },
  { name: 'status', label: 'Hasil', field: 'status', align: 'center' },
  { name: 'tanggal', label: 'Tanggal', field: 'tanggal_ujian', align: 'right' }
];

const evaluasiColumns = [
  { name: 'nama', label: 'Nama Santri', field: 'santri', align: 'left' },
  { name: 'kategori', label: 'Kategori Ujian', field: 'kategori_juz', align: 'center' },
  { name: 'nilai', label: 'Nilai & Predikat', field: 'nilai_akhir', align: 'center', format: (val, row) => `${val} (${row.predikat})` },
  { name: 'status', label: 'Hasil', field: 'status', align: 'center' },
  { name: 'tanggal', label: 'Tanggal Ujian', field: 'tanggal_ujian', align: 'right' }
];

const loadData = async () => {
  loading.value = true;
  try {
    const [resSetoran, resMurojaah, resEvaluasi] = await Promise.all([
      api.get('/hafalan/setoran'),
      api.get('/hafalan/murojaah'),
      api.get('/hafalan/evaluasi')
    ]);

    if (resSetoran.data.success) setoranList.value = resSetoran.data.data.data;
    if (resMurojaah.data.success) murojaahList.value = resMurojaah.data.data.data;
    if (resEvaluasi.data.success) evaluasiList.value = resEvaluasi.data.data.data;
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
      santriOptions.value = res.data.data.data.map(s => ({
        id: s.id,
        label: `${s.nama_lengkap} (Juz ${s.total_juz_hafalan})`
      }));
    }
  } catch (err) {
    console.error(err);
  }
};

const openSetoranDialog = () => {
  setoranForm.value = {
    santri_id: santriOptions.value[0]?.id || null,
    juz: 1,
    surat: 'Al-Baqarah',
    ayat_awal: 1,
    ayat_akhir: 10,
    halaman: 1,
    kualitas: 'lancar',
    tanggal_setoran: new Date().toISOString().substring(0, 10)
  };
  setoranDialog.value = true;
};

const saveSetoran = async () => {
  saving.value = true;
  try {
    await api.post('/hafalan/setoran', setoranForm.value);
    toastSuccess('Setoran hafalan santri berhasil dicatat.');
    setoranDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal mencatat setoran.');
  } finally {
    saving.value = false;
  }
};

const openMurojaahDialog = () => {
  murojaahForm.value = {
    santri_id: santriOptions.value[0]?.id || null,
    juz: 1,
    jumlah_salah: 0,
    nilai_predikat: 'Mumtaz',
    status: 'lulus',
    tanggal_ujian: new Date().toISOString().substring(0, 10)
  };
  murojaahDialog.value = true;
};

const saveMurojaah = async () => {
  saving.value = true;
  try {
    await api.post('/hafalan/murojaah', murojaahForm.value);
    toastSuccess('Hasil sima\'an muroja\'ah 1 juz berhasil disimpan.');
    murojaahDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal menyimpan murojaah.');
  } finally {
    saving.value = false;
  }
};

const openEvaluasiDialog = () => {
  evaluasiForm.value = {
    santri_id: santriOptions.value[0]?.id || null,
    kategori_juz: 5,
    nilai_akhir: 90,
    predikat: 'Mumtaz',
    status: 'lulus',
    tanggal_ujian: new Date().toISOString().substring(0, 10)
  };
  evaluasiDialog.value = true;
};

const saveEvaluasi = async () => {
  saving.value = true;
  try {
    await api.post('/hafalan/evaluasi', evaluasiForm.value);
    toastSuccess('Ujian evaluasi akbar berhasil dicatat.');
    evaluasiDialog.value = false;
    loadData();
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal menyimpan evaluasi akbar.');
  } finally {
    saving.value = false;
  }
};

const handleExportTahfiz = () => {
  if (currentTab.value === 'setoran') {
    const cols = [
      { label: 'Tanggal', field: 'tanggal_setoran' },
      { label: 'Santri', field: row => row.santri?.nama_lengkap || '-' },
      { label: 'Juz', field: 'juz' },
      { label: 'Surat', field: 'surat' },
      { label: 'Ayat', field: row => `${row.ayat_awal} - ${row.ayat_akhir}` },
      { label: 'Kualitas', field: 'kualitas' },
      { label: 'Penyimak', field: row => row.ustadz?.name || '-' }
    ];
    exportToCsv('Rekap_Setoran_Hafalan', cols, setoranList.value);
  } else if (currentTab.value === 'murojaah') {
    const cols = [
      { label: 'Tanggal', field: 'tanggal_murojaah' },
      { label: 'Santri', field: row => row.santri?.nama_lengkap || '-' },
      { label: 'Juz', field: 'juz' },
      { label: 'Halaman', field: row => `${row.halaman_awal} - ${row.halaman_akhir}` },
      { label: 'Kualitas', field: 'kualitas' },
      { label: 'Penyimak', field: row => row.ustadz?.name || '-' }
    ];
    exportToCsv('Rekap_Murojaah_Santri', cols, murojaahList.value);
  } else {
    const cols = [
      { label: 'Tanggal', field: 'tanggal_evaluasi' },
      { label: 'Santri', field: row => row.santri?.nama_lengkap || '-' },
      { label: 'Juz Diuji', field: row => `Juz ${row.juz_awal} - ${row.juz_akhir}` },
      { label: 'Kelancaran', field: 'nilai_kelancaran' },
      { label: 'Makhraj', field: 'nilai_makhraj' },
      { label: 'Tajwid', field: 'nilai_tajwid' },
      { label: 'Nilai Akhir', field: 'nilai_rata_rata' },
      { label: 'Predikat', field: 'predikat' },
      { label: 'Penguji', field: row => row.penguji?.name || '-' }
    ];
    exportToCsv('Rekap_Evaluasi_Akbar_Tahfiz', cols, evaluasiList.value);
  }
};

const handlePrintTahfiz = () => {
  if (currentTab.value === 'setoran') {
    printReport({
      title: 'REKAPITULASI SETORAN HARIAN (ZIYADAH) TAHFIZ',
      subtitle: 'Pondok Pesantren Tahfizul Qur\'an Yayasan Al Mukhlisin',
      stats: [
        { label: 'Total Setoran', value: setoranList.value.length, color: '#0D7C66' },
        { label: 'Kualitas Lancar', value: setoranList.value.filter(s => s.kualitas === 'lancar').length, color: '#10B981' },
        { label: 'Perlu Mengulang', value: setoranList.value.filter(s => s.kualitas === 'mengulang').length, color: '#EF4444' }
      ],
      columns: [
        { label: 'Tanggal', field: 'tanggal_setoran', align: 'center' },
        { label: 'Nama Santri', field: row => row.santri?.nama_lengkap || '-' },
        { label: 'Juz & Surat', field: row => `Juz ${row.juz} - QS. ${row.surat}` },
        { label: 'Ayat', field: row => `${row.ayat_awal} - ${row.ayat_akhir}`, align: 'center' },
        { label: 'Kualitas', field: 'kualitas', align: 'center' },
        { label: 'Ustadz Penyimak', field: row => row.ustadz?.name || '-' }
      ],
      rows: setoranList.value,
      signatories: [
        { title: 'Mengetahui,', role: 'Pimpinan Pondok Pesantren', name: 'Ust. H. Ahmad Dahlan, Lc.' },
        { title: 'Kepala Divisi Tahfiz,', role: 'Musyrif Halaqah Al-Qur\'an', name: 'Ust. Ahmad Al-Hafiz' }
      ]
    });
  } else if (currentTab.value === 'murojaah') {
    printReport({
      title: 'REKAPITULASI SIMA\'AN MUROJA\'AH PER JUZ',
      subtitle: 'Monitoring Kemutqinan Hafalan Santri Yayasan Al Mukhlisin',
      stats: [
        { label: 'Total Sima\'an Muroja\'ah', value: murojaahList.value.length, color: '#006A67' }
      ],
      columns: [
        { label: 'Tanggal', field: 'tanggal_murojaah', align: 'center' },
        { label: 'Nama Santri', field: row => row.santri?.nama_lengkap || '-' },
        { label: 'Juz Di-Muroja\'ah', field: row => `Juz ${row.juz}`, align: 'center' },
        { label: 'Halaman', field: row => `Hlm. ${row.halaman_awal} - ${row.halaman_akhir}`, align: 'center' },
        { label: 'Kualitas', field: 'kualitas', align: 'center' },
        { label: 'Penyimak', field: row => row.ustadz?.name || '-' }
      ],
      rows: murojaahList.value,
      signatories: [
        { title: 'Mengetahui,', role: 'Pimpinan Pondok Pesantren', name: 'Ust. H. Ahmad Dahlan, Lc.' },
        { title: 'Kepala Divisi Tahfiz,', role: 'Musyrif Halaqah Al-Qur\'an', name: 'Ust. Ahmad Al-Hafiz' }
      ]
    });
  } else {
    printReport({
      title: 'RAPORT EVALUASI AKBAR UJIAN TAHFIZ JUZ',
      subtitle: 'Hasil Ujian Kelipatan Juz Al-Qur\'an Yayasan Al Mukhlisin',
      stats: [
        { label: 'Total Santri Diuji', value: evaluasiList.value.length, color: '#F59E0B' },
        { label: 'Predikat Mumtaz', value: evaluasiList.value.filter(e => e.predikat === 'Mumtaz').length, color: '#10B981' }
      ],
      columns: [
        { label: 'Tanggal', field: 'tanggal_evaluasi', align: 'center' },
        { label: 'Nama Santri', field: row => row.santri?.nama_lengkap || '-' },
        { label: 'Juz Diuji', field: row => `Juz ${row.juz_awal} - ${row.juz_akhir}`, align: 'center' },
        { label: 'Kelancaran', field: 'nilai_kelancaran', align: 'center' },
        { label: 'Makhraj', field: 'nilai_makhraj', align: 'center' },
        { label: 'Tajwid', field: 'nilai_tajwid', align: 'center' },
        { label: 'Rata-rata', field: 'nilai_rata_rata', align: 'center' },
        { label: 'Predikat', field: 'predikat', align: 'center' },
        { label: 'Penguji', field: row => row.penguji?.name || '-' }
      ],
      rows: evaluasiList.value,
      signatories: [
        { title: 'Mengetahui,', role: 'Ketua Yayasan Al Mukhlisin', name: 'Ust. H. Ahmad Dahlan, Lc.' },
        { title: 'Ketua Dewan Penguji,', role: 'Khadimul Qur\'an Al Mukhlisin', name: 'Ust. Ahmad Al-Hafiz' }
      ]
    });
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
