/** @type {import('next').NextConfig} */
// NEXT_PUBLIC_BASE_PATH = '/new' para publicar en una carpeta (pruebas); vacío para la raíz del dominio.
const base = process.env.NEXT_PUBLIC_BASE_PATH || '';
const nextConfig = {
  // Sitio estático: Next genera la carpeta "out" con todo el sitio.
  output: 'export',
  // La optimización de imágenes de Next necesita servidor; las imágenes ya están comprimidas.
  images: { unoptimized: true },
  ...(base ? { basePath: base } : {}),
};
export default nextConfig;
