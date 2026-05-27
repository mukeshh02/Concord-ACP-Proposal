/**
 * Standalone IIFE build for ACP_Proposals module.
 * Output: dist/acpproposals.iife.js
 *
 * Run from CRM root:
 *   npx vite build --config modules/ACP_Proposals/vite.module.config.js
 */
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// Absolute paths resolved from this config file's location
const moduleDir = path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1'))
const crmRoot   = path.resolve(moduleDir, '../..')

export default defineConfig({
  plugins: [vue()],

  define: {
    __VUE_OPTIONS_API__:    JSON.stringify(true),
    __VUE_PROD_DEVTOOLS__:  JSON.stringify(false),
    'process.env.NODE_ENV': JSON.stringify('production'),
  },

  resolve: {
    alias: {
      '@': path.join(crmRoot, 'resources/js'),
    },
  },

  build: {
    lib: {
      entry:    path.join(moduleDir, 'resources/js/app.js'),
      name:     'ACPProposals',
      fileName: 'acpproposals',
      formats:  ['iife'],
    },
    outDir:      path.join(moduleDir, 'dist'),
    emptyOutDir: true,

    rollupOptions: {
      // Vue already loaded globally by the CRM — don't bundle it
      external: ['vue'],
      output: {
        globals: { vue: 'Vue' },
        inlineDynamicImports: true,
      },
    },

    minify:       'esbuild',
    sourcemap:    false,
    cssCodeSplit: false,
  },
})
