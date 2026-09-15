<template>
  <q-page class="q-pa-sm q-pa-md-md q-pa-lg-lg">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-sm q-mb-md-md">
      <div>
        <h1 class="text-h6 text-md-h5 text-weight-bolder text-grey-9 q-my-none">Manajemen Pengguna & RBAC</h1>
        <p class="text-caption text-grey-6 q-mb-none text-xs">Kelola akun pengurus, dewan ustadz/ustadzah, dan ketua santri putra/putri.</p>
      </div>

      <q-btn
        color="primary"
        icon="person_add"
        label="Tambah User"
        unelevated
        no-caps
        size="sm"
        size-md="md"
        class="rounded-borders text-weight-bold"
        @click="openAddDialog"
      />
    </div>

    <!-- Users Table & Mobile Cards -->
    <div class="bg-white shadow-1 rounded-borders overflow-hidden">
      <q-table
        :rows="usersList"
        :columns="columns"
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
                <div class="row items-center no-wrap">
                  <q-avatar size="36px" color="primary" text-color="white" class="q-mr-sm text-weight-bold">
                    {{ props.row.name.charAt(0) }}
                  </q-avatar>
                  <div class="column">
                    <span class="text-weight-bold text-grey-9 text-body2 leading-tight">{{ props.row.name }}</span>
                    <span class="text-caption text-grey-6 text-xs ellipsis">{{ props.row.email }}</span>
                  </div>
                </div>

                <q-chip
                  dense
                  size="xs"
                  :color="getRoleChipColor(props.row.role)"
                  text-color="white"
                  class="text-weight-bold text-capitalize"
                >
                  {{ props.row.role?.replace('_', ' ') }}
                </q-chip>
              </div>

              <q-separator class="q-my-xs" />

              <div class="row items-center justify-between text-xs text-grey-7 q-py-xs">
                <span>Scope Wilayah: <span class="text-weight-bold text-grey-9">{{ props.row.gender_scope?.toUpperCase() || 'SEMUA' }}</span></span>
                <span>Username: <span class="text-weight-bold">{{ props.row.username }}</span></span>
              </div>

              <div class="row justify-end q-mt-xs border-top q-pt-xs q-gutter-xs">
                <q-btn flat dense size="sm" color="grey-8" icon="edit" label="Edit" @click="openEditDialog(props.row)" />
                <q-btn flat dense size="sm" color="negative" icon="delete" label="Hapus" @click="handleDelete(props.row)" />
              </div>
            </q-card>
          </div>
        </template>

        <!-- DESKTOP TEMPLATES -->
        <template #body-cell-nama="props">
          <q-td :props="props">
            <div class="row items-center no-wrap">
              <q-avatar size="34px" color="primary" text-color="white" class="q-mr-sm text-weight-bold">
                {{ props.row.name.charAt(0) }}
              </q-avatar>
              <div class="column">
                <span class="text-weight-bold text-grey-9">{{ props.row.name }}</span>
                <span class="text-caption text-grey-6">{{ props.row.email }}</span>
              </div>
            </div>
          </q-td>
        </template>

        <template #body-cell-role="props">
          <q-td :props="props" align="center">
            <q-chip
              dense
              size="sm"
              :color="getRoleChipColor(props.row.role)"
              text-color="white"
              class="text-weight-bold text-capitalize"
            >
              {{ props.row.role?.replace('_', ' ') }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-scope="props">
          <q-td :props="props" align="center">
            <q-badge color="grey-2" text-color="grey-9" class="q-pa-xs">
              {{ props.row.gender_scope?.toUpperCase() || 'SEMUA' }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-aksi="props">
          <q-td :props="props" align="right">
            <q-btn flat dense round color="grey-8" icon="edit" @click="openEditDialog(props.row)">
              <q-tooltip>Edit Pengguna</q-tooltip>
            </q-btn>
            <q-btn flat dense round color="negative" icon="delete" @click="handleDelete(props.row)">
              <q-tooltip>Hapus Pengguna</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog Tambah / Edit Pengguna -->
    <q-dialog v-model="formDialog" max-width="500px">
      <q-card style="width: 500px; max-width: 95vw;" class="dialog-form-card">
        <q-card-section class="bg-primary text-white row items-center justify-between q-py-sm q-px-md">
          <div class="text-subtitle1 text-weight-bold row items-center q-gutter-xs">
            <q-icon :name="isEditing ? 'manage_accounts' : 'person_add'" size="20px" />
            <span>{{ isEditing ? 'Edit Akun Pengguna' : 'Tambah Pengguna Baru' }}</span>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit.prevent="saveUser">
          <q-card-section class="q-pa-md q-pa-sm-lg column q-gutter-y-sm">
            <div>
              <label class="form-label">Nama Lengkap</label>
              <q-input v-model="form.name" outlined dense placeholder="Nama lengkap staf / ustadz" :rules="[val => !!val || 'Wajib diisi']" />
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Username</label>
                <q-input v-model="form.username" outlined dense placeholder="username_login" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Email</label>
                <q-input v-model="form.email" outlined dense type="email" placeholder="email@simtaq.id" :rules="[val => !!val || 'Wajib diisi']" />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-col-gutter-md-md">
              <div class="col-12 col-sm-6">
                <label class="form-label">Peran (Role)</label>
                <q-select
                  v-model="form.role"
                  outlined
                  dense
                  :options="[
                    {label:'Pengurus Yayasan', value:'pengurus'},
                    {label:'Ustadz / Ustadzah', value:'ustadz'},
                    {label:'Ketua Santri', value:'ketua_santri'}
                  ]"
                  emit-value
                  map-options
                />
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Cakupan (Gender Scope)</label>
                <q-select
                  v-model="form.gender_scope"
                  outlined
                  dense
                  :options="[
                    {label:'Semua (Global)', value:''},
                    {label:'Putra (Ikhwan)', value:'putra'},
                    {label:'Putri (Akhwat)', value:'putri'}
                  ]"
                  emit-value
                  map-options
                />
              </div>
            </div>

            <div>
              <label class="form-label">
                {{ isEditing ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password Masuk' }}
              </label>
              <q-input
                v-model="form.password"
                outlined
                dense
                type="password"
                placeholder="Minimal 6 karakter"
                :rules="isEditing ? [] : [val => !!val || 'Password wajib diisi']"
              />
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-px-md q-py-sm bg-grey-1 border-top q-gutter-sm">
            <q-btn flat label="Batal" color="grey-7" no-caps v-close-popup class="rounded-borders text-weight-bold" />
            <q-btn unelevated :label="saving ? 'Menyimpan...' : 'Simpan Akun'" color="primary" type="submit" :loading="saving" no-caps class="rounded-borders text-weight-bold q-px-md" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../utils/api';
import { toastSuccess, toastError, confirmDialog } from '../utils/sweetalert';

const loading = ref(false);
const saving = ref(false);
const usersList = ref([]);

const formDialog = ref(false);
const isEditing = ref(false);
const selectedId = ref(null);

const form = ref({
  name: '',
  username: '',
  email: '',
  role: 'ustadz',
  gender_scope: '',
  password: ''
});

const columns = [
  { name: 'nama', label: 'Nama & Email', field: 'name', align: 'left' },
  { name: 'username', label: 'Username', field: 'username', align: 'left' },
  { name: 'role', label: 'Peran Sistem', field: 'role', align: 'center' },
  { name: 'scope', label: 'Scope Asrama', field: 'gender_scope', align: 'center' },
  { name: 'aksi', label: 'Aksi', align: 'right' }
];

const getRoleChipColor = (role) => {
  switch (role) {
    case 'pengurus': return 'primary';
    case 'ustadz': return 'teal-7';
    case 'ketua_santri': return 'amber-9';
    default: return 'grey-7';
  }
};

const loadData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/users');
    if (res.data.success) {
      usersList.value = res.data.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const openAddDialog = () => {
  isEditing.value = false;
  selectedId.value = null;
  form.value = {
    name: '',
    username: '',
    email: '',
    role: 'ustadz',
    gender_scope: '',
    password: ''
  };
  formDialog.value = true;
};

const openEditDialog = (u) => {
  isEditing.value = true;
  selectedId.value = u.id;
  form.value = {
    name: u.name,
    username: u.username,
    email: u.email,
    role: u.role,
    gender_scope: u.gender_scope || '',
    password: ''
  };
  formDialog.value = true;
};

const saveUser = async () => {
  saving.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/users/${selectedId.value}`, form.value);
      toastSuccess('Akun pengguna berhasil diperbarui.');
    } else {
      await api.post('/users', form.value);
      toastSuccess('Pengguna baru berhasil dibuat.');
    }
    formDialog.value = false;
    loadData();
  } catch (e) {
    toastError(e.response?.data?.message || 'Gagal menyimpan pengguna.');
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (u) => {
  const res = await confirmDialog({
    title: 'Hapus Akun Pengguna?',
    text: `Akun ${u.name} (${u.username}) akan dinonaktifkan / dihapus dari sistem.`,
    confirmButtonText: 'Ya, Hapus',
    confirmButtonColor: '#EF4444'
  });

  if (res.isConfirmed) {
    try {
      await api.delete(`/users/${u.id}`);
      toastSuccess('Pengguna berhasil dihapus.');
      loadData();
    } catch (e) {
      toastError(e.response?.data?.message || 'Gagal menghapus pengguna.');
    }
  }
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
