import { resolve } from 'node:path'
import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    lib: {
      entry: resolve(import.meta.dirname, 'src/ts/app.ts'),
      name: 'App',
      fileName: 'app',
      formats: ['es'],
    },
  },
})