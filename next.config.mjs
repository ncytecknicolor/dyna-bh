/** @type {import('next').NextConfig} */
const nextConfig = {
  // Sitio estático para Cloudflare Pages: Next genera la carpeta "out" con todo el sitio.
  output: 'export',
  // La optimización de imágenes de Next necesita servidor; las imágenes ya están comprimidas.
  images: { unoptimized: true },
};
export default nextConfig;
