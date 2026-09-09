const esbuild = require('esbuild');
const fs = require('fs');
const path = require('path');

async function build() {
  console.log('🚀 Building BeanTalk Universal Chat SDK & Shadow DOM Widget...');

  const distDir = path.join(__dirname, 'dist');
  const publicDir = path.join(__dirname, '..', '..', 'public');

  if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
  }

  const result = await esbuild.build({
    entryPoints: [path.join(__dirname, 'src', 'index.ts')],
    bundle: true,
    minify: true,
    format: 'iife',
    globalName: 'BeanTalk',
    target: ['es2020'],
    outfile: path.join(distDir, 'chat-widget.js'),
    metafile: true,
  });

  // Copy to public/chat-widget.js, public/chat.js, public/widget.js, and public/vendor/chat/
  const distFile = path.join(distDir, 'chat-widget.js');
  const publicFile = path.join(publicDir, 'chat-widget.js');
  const vendorChatDir = path.join(publicDir, 'vendor', 'chat');

  if (!fs.existsSync(vendorChatDir)) {
    fs.mkdirSync(vendorChatDir, { recursive: true });
  }

  fs.copyFileSync(distFile, publicFile);
  fs.copyFileSync(distFile, path.join(publicDir, 'chat.js'));
  fs.copyFileSync(distFile, path.join(publicDir, 'widget.js'));
  fs.copyFileSync(distFile, path.join(vendorChatDir, 'chat-widget.js'));

  const stats = fs.statSync(publicFile);
  const sizeKb = (stats.size / 1024).toFixed(2);

  console.log(`✅ Build successful! Outputs: chat-widget.js, chat.js, widget.js (${sizeKb} KB)`);
}

build().catch((err) => {
  console.error('❌ Build failed:', err);
  process.exit(1);
});
