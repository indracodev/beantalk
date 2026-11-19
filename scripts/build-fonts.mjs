// Salin font woff2 (subset latin + latin-ext) dari @fontsource ke public/fonts dan tulis public/css/fonts.css.
// Jalankan: npm run build:fonts  (hanya perlu bila daftar font/weight berubah)
import { copyFileSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';

const FAMILIES = {
    inter: ['Inter', [300, 400, 500, 600, 700]],
    'jetbrains-mono': ['JetBrains Mono', [400, 500, 600]],
    'plus-jakarta-sans': ['Plus Jakarta Sans', [400, 500, 600, 700, 800]],
};
const SUBSETS = ['latin', 'latin-ext'];
const out = ['/* Font self-hosted (sumber: @fontsource, lisensi OFL). Dibuat oleh scripts/build-fonts.mjs. */'];

for (const [pkg, [family, weights]] of Object.entries(FAMILIES)) {
    const src = `node_modules/@fontsource/${pkg}`;
    const dst = `public/fonts/${pkg}`;
    mkdirSync(dst, { recursive: true });

    for (const w of weights) {
        const css = readFileSync(`${src}/${w}.css`, 'utf8');
        for (const [, label, body] of css.matchAll(/\/\* ([\w-]+) \*\/\s*@font-face\s*\{(.*?)\}/gs)) {
            const subset = label.slice(pkg.length + 1).split(`-${w}-`)[0];
            if (!SUBSETS.includes(subset)) continue;

            const file = body.match(/url\(\.\/files\/([^)]+\.woff2)\)/)[1];
            const range = body.match(/unicode-range:\s*([^;]+);/)[1];
            copyFileSync(`${src}/files/${file}`, `${dst}/${file}`);
            out.push(`@font-face{font-family:'${family}';font-style:normal;font-display:swap;font-weight:${w};`
                + `src:url('../fonts/${pkg}/${file}') format('woff2');unicode-range:${range};}`);
        }
    }
}

writeFileSync('public/css/fonts.css', out.join('\n') + '\n');
console.log(`${out.length - 1} @font-face ditulis ke public/css/fonts.css`);
