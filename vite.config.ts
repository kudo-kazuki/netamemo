import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import Pages from 'vite-plugin-pages'
import { resolve } from 'path'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        Pages({
            dirs: 'src/pages',
            extensions: ['vue'],
        }),
        Components({
            dirs: ['src/components'],
            extensions: ['vue'],
            deep: true,
            dts: 'src/components.d.ts',
            resolvers: [ElementPlusResolver()],
        }),
        AutoImport({
            imports: ['vue', 'vue-router'],
            dts: 'src/auto-imports.d.ts',
            resolvers: [ElementPlusResolver()],
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5174,
        proxy: {
            '/api': {
                target: 'http://localhost:8082', // ← Nginx（PHP）のポート
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, '/api'), // /api を維持
            },
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'src'), // '@'を'src'ディレクトリにマッピング
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
      @use "@/scss/variables.scss" as var;
      @use "@/scss/mixins.scss" as mixin;
    `,
            },
        },
    },
    assetsInclude: ['**/*.mp3', '**/*.wav'],
})
