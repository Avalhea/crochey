import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    // tell Vite where your "entry" is:
    root: 'assets',            // if your JS lives under assets/
    publicDir: 'public',       // where your static files are
    build: {
        outDir: '../public/build',
        manifest: true,
        rollupOptions: {
            input: {
                app: path.resolve(__dirname, 'assets/app.js'),
                // add more entries here if you need:
                // hello: path.resolve(__dirname, 'assets/components/Hello.vue')
            }
        }
    },
    server: {
        port: 3000,
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true
            }
        }
    },
    plugins: [vue()]
});
