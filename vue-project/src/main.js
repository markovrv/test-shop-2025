import './assets/main.css'
import '../node_modules/bootstrap/dist/css/bootstrap.css'

import { createApp } from 'vue'
import { createVuestic } from "vuestic-ui";
import "vuestic-ui/css";
import App from './App.vue'

createApp(App).use(createVuestic()).mount('#app')
