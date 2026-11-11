'use client';
import { useEffect, useRef, useState } from 'react';
import { BASE } from '@/lib/base';

// Casilla "No soy un robot": Google reCAPTCHA (v2) o Cloudflare Turnstile.
// El servidor (api/contact.php) dice cuál usar y entrega la clave pública; si no hay captcha configurado, no se muestra nada.
type Proveedor = 'recaptcha' | 'turnstile';
type Widget = { render: (el: HTMLElement, o: Record<string, unknown>) => string | number; reset: (id?: string | number) => void };
declare global {
  interface Window { grecaptcha?: Widget; turnstile?: Widget; __capListo?: () => void }
}
const SCRIPT: Record<Proveedor, string> = {
  recaptcha: 'https://www.google.com/recaptcha/api.js?onload=__capListo&render=explicit&hl=es-419',
  turnstile: 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=__capListo&render=explicit',
};
const api = (p: Proveedor) => (p === 'recaptcha' ? window.grecaptcha : window.turnstile);

type Props = { onConfig: (requerido: boolean) => void; onToken: (t: string) => void; resetKey: number; error?: string };

export default function Captcha({ onConfig, onToken, resetKey, error }: Props) {
  const caja = useRef<HTMLDivElement>(null);
  const id = useRef<string | number | null>(null);
  const [cfg, setCfg] = useState<{ proveedor: Proveedor; clave: string } | null>(null);

  // 1) Pregunta al servidor si hay captcha y con qué clave
  useEffect(() => {
    let vivo = true;
    fetch(`${BASE}/api/contact`)
      .then((r) => r.json())
      .then((j) => {
        const c = j?.captcha;
        if (vivo && c && typeof c.clave === 'string' && c.clave && (c.proveedor === 'recaptcha' || c.proveedor === 'turnstile')) {
          setCfg({ proveedor: c.proveedor, clave: c.clave });
          onConfig(true);
        }
      })
      .catch(() => { /* sin captcha: el formulario funciona igual */ });
    return () => { vivo = false; };
  }, [onConfig]);

  // 2) Carga el script del proveedor y dibuja la casilla
  useEffect(() => {
    if (!cfg) return;
    const pintar = () => {
      const w = api(cfg.proveedor);
      if (!w || !caja.current || id.current !== null) return;
      id.current = w.render(caja.current, {
        sitekey: cfg.clave,
        callback: onToken,
        'expired-callback': () => onToken(''),
        'error-callback': () => onToken(''),
        ...(cfg.proveedor === 'turnstile' ? { language: 'es' } : {}),
      });
    };
    if (api(cfg.proveedor)) { pintar(); return; }
    window.__capListo = pintar;
    const sid = `captcha-${cfg.proveedor}`;
    if (!document.getElementById(sid)) {
      const s = document.createElement('script');
      s.id = sid; s.src = SCRIPT[cfg.proveedor]; s.async = true; s.defer = true;
      document.head.appendChild(s);
    }
  }, [cfg, onToken]);

  // 3) Reinicia la casilla después de un envío fallido
  useEffect(() => {
    if (resetKey > 0 && cfg && id.current !== null) api(cfg.proveedor)?.reset(id.current);
  }, [resetKey, cfg]);

  if (!cfg) return null;
  return (
    <div className="mt-6">
      <div ref={caja} className="origin-left max-[400px]:scale-[0.85]" />
      {error && <p role="alert" className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
  );
}
