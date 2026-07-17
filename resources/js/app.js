import { createApp, watch } from 'vue';
import App from './components/App.vue'
import router from './components/router';
import VueApexCharts from "vue3-apexcharts";

const app = createApp(App)


app.use(router)
app.component("apexchart", VueApexCharts);
app.mount('#app')
