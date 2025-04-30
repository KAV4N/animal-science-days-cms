import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config';

import { primevueConfig } from './plugins/primevue';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';

import App from './App.vue'
import router from './router'

import './plugins/axios';

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(ToastService);
app.use(ConfirmationService);
app.use(PrimeVue, primevueConfig);
app.use(router)

app.mount('#app');