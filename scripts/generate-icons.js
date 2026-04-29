import sharp from 'sharp';
import { readFileSync } from 'fs';

const svg = readFileSync('public/icons/icon.svg');

// Generate 192x192
await sharp(svg)
  .resize(192, 192)
  .png()
  .toFile('public/icons/icon-192.png');

// Generate 512x512
await sharp(svg)
  .resize(512, 512)
  .png()
  .toFile('public/icons/icon-512.png');

console.log('✓ PWA icons generated');
