<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="bg-white shadow-1 rounded-borders q-pa-sm q-pa-md-lg">
          <!-- Profile Header -->
          <div class="column items-center text-center q-mb-md q-mb-md-lg">
            <q-avatar size="64px" color="primary" text-color="white" class="text-h6 text-weight-bolder q-mb-sm shadow-2">
              {{ initials }}
            </q-avatar>
            <h1 class="text-subtitle1 text-md-h6 text-weight-bolder text-grey-9 q-my-none">{{ authStore.userName }}</h1>
            <div class="text-caption text-grey-6 text-xs">{{ authStore.user?.email }}</div>
            <q-chip dense size="sm" color="primary" text-color="white" class="q-mt-xs">
              {{ authStore.roleLabel }}
            </q-chip>
          </div>

          <q-separator class="q-my-sm" />

          <!-- Form Edit Profil & Password -->
          <q-form @submit.prevent="submitUpdate" class="q-gutter-sm">
            <div>
              <label class="text-caption text-weight-bold text-grey-8 text-xs">Nama Lengkap</label>
              <q-input v-model="form.name" outlined dense :rules="[val => !!val || 'Nama wajib diisi']" />
            </div>

            <div>
              <label class="text-caption text-weight-bold text-grey-8 text-xs">Nomor WhatsApp / HP</label>
              <q-input v-model="form.no_hp" outlined dense placeholder="08xxxxxxxxxx" />
            </div>

            <div>
              <label class="text-caption text-weight-bold text-grey-8 text-xs">Kata Sandi Baru (Kosongkan jika tidak ingin mengubah)</label>
              <q-input v-model="form.password" outlined dense type="password" placeholder="Minimal 8 karakter..." />
            </div>

            <div>
              <label class="text-caption text-weight-bold text-grey-8 text-xs">Konfirmasi Kata Sandi Baru</label>
              <q-input v-model="form.password_confirmation" outlined dense type="password" placeholder="Ulangi kata sandi baru..." />
            </div>

            <div class="row justify-end q-mt-md">
              <q-btn
                type="submit"
                color="primary"
                label="Simpan Perubahan Profil"
                unelevated
                class="rounded-borders text-weight-bold full-width-xs"
                :loading="saving"
              />
            </div>
          </q-form>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { toastError } from '../utils/sweetalert';

const authStore = useAuthStore();
const saving = ref(false);

const form = ref({
  name: authStore.userName,
  no_hp: authStore.user?.no_hp || '',
  password: '',
  password_confirmation: ''
});

const initials = computed(() => {
  const name = authStore.userName || 'U';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
});

const submitUpdate = async () => {
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    toastError('Konfirmasi kata sandi tidak cocok.');
    return;
  }

  saving.value = true;
  try {
    const payload = {
      name: form.value.name,
      no_hp: form.value.no_hp
    };
    if (form.value.password) {
      payload.password = form.value.password;
      payload.password_confirmation = form.value.password_confirmation;
    }

    const ok = await authStore.updateProfile(payload);
    if (ok) {
      form.value.password = '';
      form.value.password_confirmation = '';
    }
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.text-xs {
  font-size: 0.75rem;
}
@media (max-width: 599px) {
  .full-width-xs {
    width: 100%;
  }
}
</style>
