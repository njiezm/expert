import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Interface
                bunny('Inter', { weights: [400, 500, 600, 700] }),
                // Énoncés de cours : une serif pour les longs blocs de lecture
                bunny('Source Serif 4', { weights: [400, 600] }),
                // Code, formules, triplets de Hoare
                bunny('JetBrains Mono', { weights: [400, 500, 700] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});