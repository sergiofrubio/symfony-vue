import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    host: '0.0.0.0', // necesario en Docker
    watch: {
      usePolling: true, // fuerza a usar polling
      interval: 100     // opcional, frecuencia en ms
    },
     proxy: {
      '/api': 'https://localhost:8443', // todas las peticiones que empiecen con /api van al backend
    }
  }
})
