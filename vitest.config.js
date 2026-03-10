import { defineConfig } from 'vitest/config';
import path from 'path';

export default defineConfig({
  test: {
    globals: true,
    environment: 'happy-dom',
    setupFiles: ['resources/assets/js/tests/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['resources/assets/js/**/*.js'],
      exclude: [
        'resources/assets/js/tests/**/*.test.js',
        'resources/assets/js/tests/**/*.spec.js',
        'resources/assets/js/tests/**/*.md',
        'node_modules/',
      ],
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/assets/js'),
    },
  },
});
