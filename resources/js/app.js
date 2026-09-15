import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { Quasar, Notify, Dialog, Loading } from 'quasar';
import { MotionPlugin } from '@vueuse/motion';
import VueApexCharts from 'vue3-apexcharts';

import router from './router';
import App from './App.vue';

// Import Quasar & icon styles
import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/src/css/index.sass';
import '../css/app.css';

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(Quasar, {
  plugins: {
    Notify,
    Dialog,
    Loading
  },
  config: {
    brand: {
      primary: '#0D7C66',
      secondary: '#006A67',
      accent: '#14B8A6',
      dark: '#111827',
      positive: '#10B981',
      negative: '#EF4444',
      info: '#3B82F6',
      warning: '#F59E0B'
    }
  }
});
app.use(MotionPlugin);
app.use(VueApexCharts);

app.mount('#app');
