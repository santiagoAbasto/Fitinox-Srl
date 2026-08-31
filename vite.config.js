import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

const lucideDirectImports = () => ({
    name: 'fitinox-lucide-direct-imports',
    enforce: 'pre',
    transform(code, id) {
        if (!id.includes('/resources/js/') || !code.includes('lucide-react')) return null;

        const transformed = code.replace(
            /import\s*\{([^}]*)\}\s*from\s*['"]lucide-react['"];?/g,
            (_statement, specifiers) => specifiers
                .split(',')
                .map((specifier) => specifier.trim())
                .filter(Boolean)
                .map((specifier) => {
                    const [imported, local = imported] = specifier.split(/\s+as\s+/);
                    const file = imported
                        .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
                        .replace(/([A-Za-z])(\d+)/g, '$1-$2')
                        .toLowerCase();

                    return `import ${local} from 'lucide-react/dist/esm/icons/${file}.mjs';`;
                })
                .join('\n'),
        );

        return transformed === code ? null : { code: transformed, map: null };
    },
});

export default defineConfig({
    plugins: [
        lucideDirectImports(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx', 'resources/js/public-motion.js'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
