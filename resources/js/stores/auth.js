import { defineStore } from 'pinia';
import api from '../utils/api';
import { toastSuccess, toastError } from '../utils/sweetalert';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('simtaq_user') || 'null'),
    token: localStorage.getItem('simtaq_token') || null,
    isLoading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    role: (state) => state.user?.role || '',
    genderScope: (state) => state.user?.gender_scope || 'semua',
    isPengurus: (state) => state.user?.role === 'pengurus',
    isUstadz: (state) => state.user?.role === 'ustadz',
    isKetuaSantri: (state) => state.user?.role === 'ketua_santri',
    userName: (state) => state.user?.name || 'Pengguna',
    roleLabel: (state) => {
      switch (state.user?.role) {
        case 'pengurus': return 'Pengurus Yayasan';
        case 'ustadz': return 'Asatidz / Ustadz';
        case 'ketua_santri': return state.user?.gender_scope === 'putra' ? 'Ketua Santri Putra' : 'Ketua Santri Putri';
        default: return 'Pengguna';
      }
    }
  },

  actions: {
    async login(email, password) {
      this.isLoading = true;
      try {
        const response = await api.post('/login', { email, password });
        const { token, user } = response.data.data;

        this.token = token;
        this.user = user;
        localStorage.setItem('simtaq_token', token);
        localStorage.setItem('simtaq_user', JSON.stringify(user));

        toastSuccess(`Ahlan wa Sahlan, ${user.name}`);
        return true;
      } catch (error) {
        const msg = error.response?.data?.message || 'Gagal masuk. Periksa kembali email dan password.';
        toastError(msg);
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchMe() {
      if (!this.token) return;
      try {
        const response = await api.get('/me');
        this.user = response.data.data.user;
        localStorage.setItem('simtaq_user', JSON.stringify(this.user));
      } catch (error) {
        this.logout();
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/logout');
        }
      } catch (e) {
        // ignore logout failure
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('simtaq_token');
        localStorage.removeItem('simtaq_user');
        window.location.href = '/login';
      }
    },

    async updateProfile(profileData) {
      this.isLoading = true;
      try {
        const response = await api.put('/profile', profileData);
        this.user = { ...this.user, ...response.data.data.user };
        localStorage.setItem('simtaq_user', JSON.stringify(this.user));
        toastSuccess('Profil berhasil diperbarui');
        return true;
      } catch (error) {
        const msg = error.response?.data?.message || 'Gagal memperbarui profil.';
        toastError(msg);
        return false;
      } finally {
        this.isLoading = false;
      }
    }
  }
});
