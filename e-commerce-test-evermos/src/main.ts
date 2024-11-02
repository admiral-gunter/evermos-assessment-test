import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
//@ts-ignore
import { SnackbarService, Vue3Snackbar } from "vue3-snackbar";
import "vue3-snackbar/styles"
import App from '@/App.vue'
import router from '@/router'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(SnackbarService);
app.component("vue3-snackbar", Vue3Snackbar);
app.mount('#app')
