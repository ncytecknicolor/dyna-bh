// Ruta base cuando el sitio se publica dentro de una carpeta (por ejemplo /new para pruebas). Vacía en la raíz del dominio.
export const BASE = process.env.NEXT_PUBLIC_BASE_PATH ?? '';
export const ruta = (p: string) => (BASE && p.startsWith('/') && !p.startsWith(`${BASE}/`) ? `${BASE}${p}` : p);
