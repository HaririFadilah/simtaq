<template>
  <div class="login-wrapper flex flex-center q-pa-md">
    <div
      v-motion
      :initial="{ opacity: 0, y: 30 }"
      :enter="{ opacity: 1, y: 0, transition: { duration: 500, ease: 'easeOut' } }"
      class="login-card bg-white shadow-10 rounded-borders q-pa-lg"
    >
      <!-- Logo Branding & Header -->
      <div class="column items-center q-mb-md text-center">
        <div class="logo-container q-mb-sm shadow-2">
          <img
            src="/assets/logo-inti.jpeg"
            alt="SIMTAQ Logo Inti"
            class="logo-image"
          />
        </div>
        <h1 class="text-h5 text-weight-bolder text-primary q-my-none">SIMTAQ</h1>
        <div class="text-subtitle2 text-weight-medium text-grey-8">Yayasan Al Mukhlisin</div>
        <div class="text-caption text-grey-6 q-mt-xs">
          Sistem Informasi Manajemen Santri & Tahfiz Al-Qur'an
        </div>
      </div>

      <q-separator class="q-my-md" />

      <!-- Login Form -->
      <q-form @submit.prevent="submitLogin" class="q-gutter-md">
        <div>
          <label class="text-caption text-weight-bold text-grey-8 q-mb-xs block">Email Akun</label>
          <q-input
            v-model="email"
            outlined
            dense
            type="email"
            placeholder="nama@simtaq.test"
            :rules="[val => !!val || 'Email wajib diisi']"
            lazy-rules
          >
            <template #prepend>
              <q-icon name="email" color="primary" />
            </template>
          </q-input>
        </div>

        <div>
          <label class="text-caption text-weight-bold text-grey-8 q-mb-xs block">Kata Sandi</label>
          <q-input
            v-model="password"
            outlined
            dense
            :type="showPassword ? 'text' : 'password'"
            placeholder="Masukkan kata sandi"
            :rules="[val => !!val || 'Kata sandi wajib diisi']"
            lazy-rules
          >
            <template #prepend>
              <q-icon name="lock" color="primary" />
            </template>
            <template #append>
              <q-icon
                :name="showPassword ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="showPassword = !showPassword"
              />
            </template>
          </q-input>
        </div>

        <q-btn
          type="submit"
          label="Masuk ke Sistem"
          color="primary"
          unelevated
          size="md"
          class="full-width text-weight-bold q-py-sm rounded-borders"
          :loading="authStore.isLoading"
        >
          <template #loading>
            <q-spinner-dots />
          </template>
        </q-btn>
      </q-form>

      <!-- Quick Demo Login Selection (Mudah untuk Pengujian Peran) -->
      <div class="q-mt-lg pt-2 border-top">
        <div class="text-caption text-weight-bold text-grey-7 text-center q-mb-sm">
          Pilihan Akses Cepat (Demo Role):
        </div>
        <div class="row q-col-gutter-xs">
          <div class="col-6">
            <q-btn
              outline
              no-caps
              dense
              size="sm"
              color="primary"
              label="1. Pengurus"
              class="full-width"
              @click="setQuickLogin('hariri@simtaq.test')"
            />
          </div>
          <div class="col-6">
            <q-btn
              outline
              no-caps
              dense
              size="sm"
              color="teal-8"
              label="2. Ustadz"
              class="full-width"
              @click="setQuickLogin('ustadz.ahmad@simtaq.test')"
            />
          </div>
          <div class="col-6 q-mt-xs">
            <q-btn
              outline
              no-caps
              dense
              size="sm"
              color="blue-9"
              label="3. Ketua Putra"
              class="full-width"
              @click="setQuickLogin('ketua.putra@simtaq.test')"
            />
          </div>
          <div class="col-6 q-mt-xs">
            <q-btn
              outline
              no-caps
              dense
              size="sm"
              color="purple-8"
              label="4. Ketua Putri"
              class="full-width"
              @click="setQuickLogin('ketua.putri@simtaq.test')"
            />
          </div>
        </div>

        <!-- Link Pendaftaran Santri Baru (PSB) Online untuk Wali Murid -->
        <div class="q-mt-md text-center">
          <q-btn
            unelevated
            color="positive"
            text-color="white"
            icon="how_to_reg"
            label="Pendaftaran Santri Baru (PSB) Online"
            no-caps
            class="full-width text-weight-bold shadow-1"
            to="/pendaftaran"
          />
          <div class="text-caption text-grey-6 text-xs q-mt-xs">
            Wali murid dapat mendaftar mandiri tanpa login
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('hariri@simtaq.test');
const password = ref('password');
const showPassword = ref(false);

const setQuickLogin = (selectedEmail) => {
  email.value = selectedEmail;
  password.value = 'password';
};

const submitLogin = async () => {
  const success = await authStore.login(email.value, password.value);
  if (success) {
    router.push('/dashboard');
  }
};
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #0A5344 0%, #0D7C66 50%, #E8F5F1 100%);
}

.login-card {
  width: 100%;
  max-width: 420px;
  border-radius: 16px;
}

.logo-container {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #0D7C66;
  background-color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.border-top {
  border-top: 1px dashed #E2E8F0;
  padding-top: 12px;
}
</style>
