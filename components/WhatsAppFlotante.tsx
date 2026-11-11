import { WA } from '@/lib/site';
import { IconoRed } from './Redes';

// Botón flotante de WhatsApp (esquina inferior derecha, en todas las páginas)
export default function WhatsAppFlotante() {
  return (
    <a href={WA} target="_blank" rel="noopener noreferrer" aria-label="Escríbenos por WhatsApp"
      className="group fixed bottom-[max(1.25rem,env(safe-area-inset-bottom))] right-5 z-50 flex items-center gap-3">
      <span className="pointer-events-none hidden translate-x-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-grafito opacity-0 shadow-lg transition duration-200 group-hover:translate-x-0 group-hover:opacity-100 group-focus-visible:translate-x-0 group-focus-visible:opacity-100 md:block">
        ¿Hablamos por WhatsApp?
      </span>
      <span className="relative flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl transition-transform duration-200 group-hover:scale-110">
        <span aria-hidden="true" className="absolute inset-0 animate-ping rounded-full bg-[#25D366]/50 [animation-iteration-count:4] motion-reduce:hidden" />
        <IconoRed id="whatsapp" size={30} className="relative" />
      </span>
    </a>
  );
}
