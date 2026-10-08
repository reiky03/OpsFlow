import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    hmr: {
      clientPort: Number(process.env.VITE_HMR_CLIENT_PORT ?? 8080),
    },
    watch: {
      usePolling: true,
    },
  },
})
