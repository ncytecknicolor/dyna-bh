import { FB, WA } from './site';

export type Red = { id: 'facebook' | 'instagram' | 'linkedin' | 'tiktok' | 'youtube' | 'x' | 'whatsapp'; nombre: string; url: string };

// Escribe la dirección de cada red social de Dynamedia. Las que dejes vacías ('') no se muestran.
export const redes: Red[] = [
  { id: 'facebook', nombre: 'Facebook', url: FB },
  { id: 'instagram', nombre: 'Instagram', url: '' },
  { id: 'linkedin', nombre: 'LinkedIn', url: '' },
  { id: 'tiktok', nombre: 'TikTok', url: '' },
  { id: 'youtube', nombre: 'YouTube', url: '' },
  { id: 'x', nombre: 'X (Twitter)', url: '' },
  { id: 'whatsapp', nombre: 'WhatsApp', url: WA },
];
export const redesActivas = redes.filter((r) => r.url.trim() !== '');
