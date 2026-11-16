import { FB, WA } from './site';

export type Red = { id: 'facebook' | 'instagram' | 'linkedin' | 'tiktok' | 'youtube' | 'x' | 'whatsapp'; nombre: string; url: string };

// Escribe la dirección de cada red social de Dynamedia. Las que dejes vacías ('') no se muestran.
export const redes: Red[] = [
  { id: 'facebook', nombre: 'Facebook', url: FB },
  { id: 'instagram', nombre: 'Instagram', url: 'https://www.instagram.com/dynamediamx' },
  { id: 'linkedin', nombre: 'LinkedIn', url: '' },
  { id: 'tiktok', nombre: 'TikTok', url: 'https://www.tiktok.com/@dynamediamx' },
  { id: 'youtube', nombre: 'YouTube', url: 'https://www.youtube.com/@dynamediamx' },
  { id: 'x', nombre: 'X (Twitter)', url: '' },
  { id: 'whatsapp', nombre: 'WhatsApp', url: WA },
];
export const redesActivas = redes.filter((r) => r.url.trim() !== '');
