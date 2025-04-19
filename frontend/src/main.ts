import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config';

import { PrimeIcons } from '@primevue/core/api';
import { primevueConfig } from './plugins/primevue';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';

import { useAuthStore } from './stores/authStore';

import App from './App.vue'
import router from './router'

import './plugins/axios';


const app = createApp(App)

app.use(ToastService);
app.use(ConfirmationService);

app.use(createPinia())
app.use(router)
app.use(PrimeVue, primevueConfig);

const authStore = useAuthStore();
authStore.fetchCurrentUser();

app.mount('#app')
