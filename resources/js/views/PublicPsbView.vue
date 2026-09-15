<template>
  <div class="public-psb-page min-h-screen bg-[#F4F7F6]">
    <!-- TOP APP BAR / BRANDING -->
    <header class="public-header shadow-1 bg-white q-py-sm q-px-md sticky-top">
      <div class="row items-center justify-between max-w-screen q-mx-auto">
        <div class="row items-center q-gutter-sm cursor-pointer" @click="$router.push('/')">
          <img src="/assets/logo-header.jpeg" alt="Logo Header" class="header-logo" />
          <div class="column">
            <span class="text-weight-bolder text-primary text-subtitle1 leading-tight">SIMTAQ</span>
            <span class="text-caption text-grey-6 text-xs">Yayasan Al Mukhlisin</span>
          </div>
        </div>

        <div class="row items-center q-gutter-xs">
          <q-btn
            flat
            dense
            no-caps
            color="primary"
            label="Masuk Sistem"
            icon="login"
            to="/login"
            class="text-weight-bold"
          />
        </div>
      </div>
    </header>

    <!-- HERO SECTION -->
    <section class="public-hero q-pa-md q-pa-md-xl text-center text-white">
      <div class="max-w-screen q-mx-auto column items-center">
        <q-badge color="amber-8" text-color="black" class="q-px-sm q-py-xs text-weight-bold q-mb-sm text-xs">
          PENERIMAAN SANTRI BARU (PSB)
        </q-badge>
        <h1 class="text-h5 text-md-h4 text-weight-bolder leading-tight q-my-none">
          Pendaftaran Santri Baru & Tahfiz Al-Qur'an
        </h1>
        <p class="text-caption text-md-subtitle1 text-emerald-100 q-mt-sm max-w-md">
          Pondok Pesantren Tahfizul Qur'an Terpadu Yayasan Al Mukhlisin. Cetak generasi penghafal Qur'an yang berakhlak mulia.
        </p>

        <!-- Tab Navigasi (Pendaftaran vs Cek Status) -->
        <div class="hero-tabs-wrapper q-mt-md">
          <q-btn-toggle
            v-model="activeTab"
            no-caps
            rounded
            unelevated
            toggle-color="white"
            toggle-text-color="primary"
            color="teal-9"
            text-color="white"
            :options="[
              { label: 'Formulir Pendaftaran', value: 'register', icon: 'edit_document' },
              { label: 'Cek Status Seleksi', value: 'check', icon: 'search' }
            ]"
            class="shadow-2"
          />
        </div>
      </div>
    </section>

    <!-- CONTENT WRAPPER -->
    <main class="max-w-screen q-mx-auto q-px-sm q-px-md-md q-py-md q-py-md-lg content-container">
      <!-- TAB 1: FORMULIR PENDAFTARAN MANDIRI -->
      <div v-if="activeTab === 'register'" class="row justify-center">
        <div class="col-12 col-md-10 col-lg-8">
          <q-card class="shadow-2 rounded-borders overflow-hidden bg-white">
            <div class="bg-emerald-light q-pa-md border-bottom row items-center justify-between">
              <div class="row items-center q-gutter-sm">
                <q-avatar color="primary" text-color="white" icon="how_to_reg" size="36px" />
                <div class="column">
                  <span class="text-subtitle1 text-weight-bolder text-grey-9">Formulir Pendaftaran Santri Baru</span>
                  <span class="text-caption text-grey-7 text-xs">Lengkapi seluruh data calon santri dan orang tua / wali di bawah ini</span>
                </div>
              </div>
              <q-badge color="positive" class="text-caption text-weight-bold">Buka Pendaftaran</q-badge>
            </div>

            <q-card-section class="q-pa-md q-pa-md-lg">
              <q-form @submit.prevent="submitRegistration" class="q-gutter-y-md">
                <!-- 1. IDENTITAS CALON SANTRI -->
                <div>
                  <div class="row items-center q-mb-sm">
                    <q-icon name="person" color="primary" size="20px" class="q-mr-xs" />
                    <span class="text-subtitle2 text-weight-bold text-primary">A. Identitas Calon Santri</span>
                  </div>
                  <q-separator class="q-mb-md" />

                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-sm-8">
                      <label class="form-label">Nama Lengkap Santri *</label>
                      <q-input
                        v-model="form.nama_lengkap"
                        outlined
                        dense
                        placeholder="Sesuai Akta Kelahiran / KK"
                        :rules="[val => !!val || 'Nama lengkap wajib diisi']"
                      />
                    </div>
                    <div class="col-12 col-sm-4">
                      <label class="form-label">Nama Panggilan</label>
                      <q-input v-model="form.nama_panggilan" outlined dense placeholder="Nama akrab" />
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="form-label">Jenis Kelamin *</label>
                      <q-select
                        v-model="form.jenis_kelamin"
                        outlined
                        dense
                        :options="[
                          { label: 'Putra (Laki-laki)', value: 'L' },
                          { label: 'Putri (Perempuan)', value: 'P' }
                        ]"
                        emit-value
                        map-options
                        :rules="[val => !!val || 'Pilih jenis kelamin']"
                      />
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="form-label">Asal Sekolah Sebelumnya</label>
                      <q-input v-model="form.asal_sekolah" outlined dense placeholder="SDIT / MI / SMP / MTs..." />
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="form-label">Tempat Lahir</label>
                      <q-input v-model="form.tempat_lahir" outlined dense placeholder="Kota kelahiran" />
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="form-label">Tanggal Lahir</label>
                      <q-input v-model="form.tanggal_lahir" type="date" outlined dense />
                    </div>

                    <div class="col-12">
                      <label class="form-label">Asal Kota / Kabupaten Tempat Tinggal</label>
                      <q-input v-model="form.asal_kota" outlined dense placeholder="Misal: Bandung, Jakarta Selatan, Bogor" />
                    </div>
                  </div>
                </div>

                <!-- 2. DATA ORANG TUA / WALI -->
                <div class="q-pt-sm">
                  <div class="row items-center q-mb-sm">
                    <q-icon name="family_restroom" color="primary" size="20px" class="q-mr-xs" />
                    <span class="text-subtitle2 text-weight-bold text-primary">B. Data Orang Tua / Wali Santri</span>
                  </div>
                  <q-separator class="q-mb-md" />

                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6">
                      <label class="form-label">Nama Lengkap Wali *</label>
                      <q-input
                        v-model="form.nama_wali"
                        outlined
                        dense
                        placeholder="Nama Ayah / Ibu / Wali"
                        :rules="[val => !!val || 'Nama wali wajib diisi']"
                      />
                    </div>

                    <div class="col-12 col-sm-6">
                      <label class="form-label">Nomor WhatsApp Aktif *</label>
                      <q-input
                        v-model="form.no_hp_wali"
                        outlined
                        dense
                        placeholder="08xxxxxxxxxx"
                        hint="Informasi hasil seleksi akan dikirim ke nomor ini"
                        :rules="[
                          val => !!val || 'Nomor WhatsApp wajib diisi',
                          val => val.length >= 10 || 'Nomor HP minimal 10 digit'
                        ]"
                      >
                        <template #prepend>
                          <q-icon name="chat" color="positive" size="18px" />
                        </template>
                      </q-input>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Alamat Domisili Lengkap</label>
                      <q-input
                        v-model="form.alamat_wali"
                        type="textarea"
                        outlined
                        dense
                        rows="2"
                        placeholder="Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan, Kota..."
                      />
                    </div>

                    <div class="col-12">
                      <label class="form-label">Motivasi Masuk Pondok / Catatan Khusus</label>
                      <q-input
                        v-model="form.catatan_wali"
                        type="textarea"
                        outlined
                        dense
                        rows="2"
                        placeholder="Contoh: Ingin fokus menghafal 30 juz, ada riwayat alergi asma..."
                      />
                    </div>
                  </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="q-pt-md">
                  <q-btn
                    type="submit"
                    color="primary"
                    unelevated
                    size="lg"
                    class="full-width rounded-borders text-weight-bolder shadow-2"
                    :loading="submitting"
                  >
                    <q-icon name="send" class="q-mr-sm" />
                    Kirim Pendaftaran Santri Baru
                  </q-btn>
                  <p class="text-caption text-grey-6 text-center q-mt-sm text-xs">
                    Dengan mengirim formulir ini, Anda menyetujui seluruh tata tertib PSB Yayasan Al Mukhlisin.
                  </p>
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- TAB 2: CEK STATUS PENDAFTARAN -->
      <div v-else class="row justify-center">
        <div class="col-12 col-md-8 col-lg-6">
          <q-card class="shadow-2 rounded-borders overflow-hidden bg-white">
            <div class="bg-primary text-white q-pa-md row items-center q-gutter-sm">
              <q-icon name="search" size="24px" />
              <div class="column">
                <span class="text-subtitle1 text-weight-bolder">Cek Status Seleksi Calon Santri</span>
                <span class="text-caption text-emerald-100 text-xs">Masukkan nomor pendaftaran yang didapatkan saat registrasi</span>
              </div>
            </div>

            <q-card-section class="q-pa-md q-pa-md-lg">
              <q-form @submit.prevent="checkStatus" class="q-gutter-y-sm">
                <label class="text-caption text-weight-bold text-grey-8">Nomor Pendaftaran</label>
                <div class="row q-col-gutter-sm">
                  <div class="col">
                    <q-input
                      v-model="searchNoPendaftaran"
                      outlined
                      dense
                      placeholder="Contoh: PSB-2026-001"
                      :rules="[val => !!val || 'Nomor pendaftaran wajib diisi']"
                    >
                      <template #prepend>
                        <q-icon name="badge" color="primary" />
                      </template>
                    </q-input>
                  </div>
                  <div class="col-auto">
                    <q-btn
                      color="primary"
                      unelevated
                      label="Cari"
                      type="submit"
                      :loading="checking"
                      style="height: 40px;"
                      class="text-weight-bold q-px-md"
                    />
                  </div>
                </div>
              </q-form>

              <!-- HASIL PENGECEKAN STATUS -->
              <div v-if="searchResult" class="q-mt-lg border rounded-borders q-pa-md bg-grey-1">
                <div class="row items-center justify-between q-mb-sm">
                  <span class="text-caption text-grey-6">Nomor Pendaftaran:</span>
                  <span class="text-weight-bolder text-primary text-subtitle2">{{ searchResult.no_pendaftaran }}</span>
                </div>
                <div class="row items-center justify-between q-mb-sm">
                  <span class="text-caption text-grey-6">Nama Calon Santri:</span>
                  <span class="text-weight-bold text-grey-9">{{ searchResult.nama_lengkap }}</span>
                </div>
                <div class="row items-center justify-between q-mb-sm">
                  <span class="text-caption text-grey-6">Tanggal Daftar:</span>
                  <span class="text-weight-medium text-grey-8">{{ searchResult.tanggal_daftar }}</span>
                </div>
                <div class="row items-center justify-between q-mb-md">
                  <span class="text-caption text-grey-6">Status Seleksi:</span>
                  <q-chip
                    dense
                    :color="getStatusColor(searchResult.status_seleksi)"
                    text-color="white"
                    class="text-weight-bold text-capitalize"
                  >
                    {{ searchResult.status_seleksi }}
                  </q-chip>
                </div>

                <div class="q-pa-sm rounded-borders bg-white border text-caption text-grey-8">
                  <q-icon name="info" color="primary" size="18px" class="q-mr-xs" />
                  {{ searchResult.catatan }}
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </main>

    <!-- DIALOG KONFIRMASI SUKSES PENDAFTARAN -->
    <q-dialog v-model="successDialog" persistent>
      <q-card style="width: 520px; max-width: 95vw;" class="rounded-borders text-center q-pa-md q-pa-md-lg">
        <q-avatar size="68px" color="positive" text-color="white" icon="check_circle" class="q-mb-md shadow-2" />

        <div class="text-h6 text-weight-bolder text-grey-9">Pendaftaran Berhasil Terkirim!</div>
        <p class="text-caption text-grey-6 q-mt-xs">
          Alhamdulillah, formulir pendaftaran santri baru telah diterima sistem SIMTAQ Yayasan Al Mukhlisin.
        </p>

        <div class="bg-emerald-light rounded-borders q-pa-md q-my-md column items-center border">
          <span class="text-caption text-weight-bold text-grey-7">NOMOR PENDAFTARAN ANDA:</span>
          <span class="text-h4 text-weight-bolder text-primary q-my-xs tracking-wider">
            {{ registeredData.no_pendaftaran }}
          </span>
          <span class="text-caption text-grey-7">
            Nama: <b>{{ registeredData.nama_lengkap }}</b>
          </span>
        </div>

        <div class="text-caption text-grey-7 text-left q-mb-md bg-grey-1 q-pa-sm rounded-borders">
          <b>Langkah Selanjutnya:</b><br>
          1. Simpan/Screenshot nomor pendaftaran di atas.<br>
          2. Panitia PSB akan menghubungi nomor WhatsApp <b>{{ registeredData.no_hp_wali }}</b> untuk jadwal tes tahsin & wawancara.
        </div>

        <div class="column q-gutter-y-sm">
          <q-btn
            color="positive"
            unelevated
            no-caps
            class="text-weight-bold"
            icon="chat"
            :href="whatsappConfirmationUrl"
            target="_blank"
            label="Kirim Konfirmasi ke WhatsApp Panitia"
          />

          <q-btn
            outline
            color="primary"
            no-caps
            class="text-weight-bold"
            icon="print"
            @click="printRegistrationProof"
            label="Cetak Bukti Pendaftaran"
          />

          <q-btn
            flat
            color="grey-7"
            no-caps
            label="Tutup & Selesai"
            v-close-popup
            @click="resetForm"
          />
        </div>
      </q-card>
    </q-dialog>

    <!-- FOOTER -->
    <footer class="public-footer q-py-lg text-center text-caption text-grey-6 bg-white border-top q-mt-xl">
      <div class="max-w-screen q-mx-auto">
        &copy; 2026 SIMTAQ &bull; Yayasan Al Mukhlisin. Sistem Informasi Manajemen Santri & Tahfiz Terpadu.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import { printReport } from '../utils/export';
import { toastSuccess, toastError } from '../utils/sweetalert';

const activeTab = ref('register');
const submitting = ref(false);
const checking = ref(false);
const successDialog = ref(false);

const form = reactive({
  nama_lengkap: '',
  nama_panggilan: '',
  jenis_kelamin: 'L',
  tempat_lahir: '',
  tanggal_lahir: '',
  asal_kota: '',
  asal_sekolah: '',
  nama_wali: '',
  no_hp_wali: '',
  alamat_wali: '',
  catatan_wali: ''
});

const registeredData = ref({});
const searchNoPendaftaran = ref('');
const searchResult = ref(null);

const whatsappConfirmationUrl = computed(() => {
  const phone = '6281234567890';
  const text = encodeURIComponent(
    `Assalamu'alaikum Panitia PSB Yayasan Al Mukhlisin,\n\nSaya telah mendaftarkan calon santri melalui SIMTAQ Online:\n- No. Pendaftaran: ${registeredData.value.no_pendaftaran}\n- Nama Santri: ${registeredData.value.nama_lengkap}\n- Wali: ${registeredData.value.nama_wali}\n- No. HP: ${registeredData.value.no_hp_wali}\n\nMohon konfirmasi dan informasi tahapan seleksi selanjutnya. Terima kasih.`
  );
  return `https://wa.me/${phone}?text=${text}`;
});

const getStatusColor = (status) => {
  switch (status) {
    case 'diterima': return 'positive';
    case 'diproses': return 'info';
    case 'cadangan': return 'warning';
    case 'ditolak': return 'negative';
    default: return 'grey-7';
  }
};

const submitRegistration = async () => {
  submitting.value = true;
  try {
    const response = await axios.post('/api/public/psb/daftar', form);
    if (response.data.success) {
      registeredData.value = response.data.data;
      successDialog.value = true;
      toastSuccess('Alhamdulillah! Pendaftaran berhasil dikirim.');
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Terjadi kesalahan saat memproses pendaftaran.';
    toastError(msg);
  } finally {
    submitting.value = false;
  }
};

const checkStatus = async () => {
  if (!searchNoPendaftaran.value) return;
  checking.value = true;
  searchResult.value = null;

  try {
    const response = await axios.get(`/api/public/psb/cek/${encodeURIComponent(searchNoPendaftaran.value.trim())}`);
    if (response.data.success) {
      searchResult.value = response.data.data;
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Nomor pendaftaran tidak ditemukan.';
    toastError(msg);
  } finally {
    checking.value = false;
  }
};

const printRegistrationProof = () => {
  printReport({
    title: 'BUKTI PENDAFTARAN SANTRI BARU (PSB)',
    subtitle: `Nomor Registrasi: ${registeredData.value.no_pendaftaran}`,
    stats: [
      { label: 'Status Pendaftaran', value: 'DIPROSES (Online)', color: '#0D7C66' },
      { label: 'Tanggal Registrasi', value: registeredData.value.tanggal_daftar || '-', color: '#1F2937' }
    ],
    columns: [
      { label: 'Parameter Data', field: 'key' },
      { label: 'Keterangan Data Calon Santri & Wali', field: 'val' }
    ],
    rows: [
      { key: 'Nomor Pendaftaran', val: registeredData.value.no_pendaftaran },
      { key: 'Nama Lengkap Calon Santri', val: registeredData.value.nama_lengkap },
      { key: 'Jenis Kelamin', val: registeredData.value.jenis_kelamin === 'L' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' },
      { key: 'Nama Orang Tua / Wali', val: registeredData.value.nama_wali },
      { key: 'No. WhatsApp Wali', val: registeredData.value.no_hp_wali },
      { key: 'Status Verifikasi', val: 'Menunggu Verifikasi & Wawancara' }
    ],
    signatories: [
      { title: 'Pemohon / Wali Santri,', role: 'Orang Tua Calon Santri', name: registeredData.value.nama_wali || 'Wali Santri' },
      { title: 'Panitia PSB,', role: 'Bagian Penerimaan Santri Baru', name: 'Ust. Ridwan Kamil, S.Pd.I.' }
    ]
  });
};

const resetForm = () => {
  form.nama_lengkap = '';
  form.nama_panggilan = '';
  form.jenis_kelamin = 'L';
  form.tempat_lahir = '';
  form.tanggal_lahir = '';
  form.asal_kota = '';
  form.asal_sekolah = '';
  form.nama_wali = '';
  form.no_hp_wali = '';
  form.alamat_wali = '';
  form.catatan_wali = '';
};
</script>

<style scoped>
.public-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid #E5E7EB;
}

.header-logo {
  height: 38px;
  width: auto;
  border-radius: 6px;
  object-fit: contain;
}

.public-hero {
  background: linear-gradient(135deg, #0A5344 0%, #0D7C66 65%, #006A67 100%);
  border-bottom-left-radius: 24px;
  border-bottom-right-radius: 24px;
}

.max-w-screen {
  max-width: 1100px;
}

.max-w-md {
  max-width: 650px;
}

.content-container {
  margin-top: -24px;
}

.border-bottom {
  border-bottom: 1px solid #E5E7EB;
}

.border-top {
  border-top: 1px solid #E5E7EB;
}

.border {
  border: 1px solid #E2E8F0;
}

.bg-emerald-light {
  background-color: #EBF7F3;
}

.tracking-wider {
  letter-spacing: 1.5px;
}

.text-xs {
  font-size: 0.72rem;
}
</style>
