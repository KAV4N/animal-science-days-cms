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

app.use(createPinia())
app.use(router)
app.use(PrimeVue, primevueConfig);

app.use(ToastService);
app.use(ConfirmationService);

app.mount('#app')
