import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // ✅ STEP 6: OPTIMIZED VITE CONFIGURATION
    
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],

    // ✅ BUILD OPTIMIZATION
    build: {
        // Minify CSS and JS
        minify: 'terser',
        
        // Optimize for production
        target: 'esnext',
        
        // Source maps for production debugging
        sourcemap: false,
        
        // Chunk size warnings
        chunkSizeWarningLimit: 1000,
        
        // Rollup options
        rollupOptions: {
            output: {
                // Optimize chunks
                manualChunks: {
                    // Separate vendor code
                    vendor: ['jquery', 'bootstrap'],
                }
            }
        }
    },

    // ✅ SERVER OPTIMIZATION
    server: {
        middlewareMode: false,
        hmr: {
            host: 'localhost',
            port: 5173,
        }
    },

    // ✅ OPTIMIZATION SETTINGS
    optimizeDeps: {
        include: ['jquery', 'bootstrap'],
    },

    // ✅ DEFINE OPTIMIZATION
    define: {
        __DEV__: JSON.stringify(false),
    }
});