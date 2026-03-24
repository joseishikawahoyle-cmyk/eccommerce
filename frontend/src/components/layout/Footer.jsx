import { useContext } from "react";
import { BrandContext } from "@/App";

export const Footer = () => {
  const { brand } = useContext(BrandContext);

  return (
    <footer className="bg-[hsl(220,20%,10%)] text-white py-16">
      <div className="max-w-7xl mx-auto px-6 md:px-12">
        <div className="grid md:grid-cols-4 gap-12">
          {/* Brand */}
          <div className="md:col-span-2">
            <h3 className="text-2xl font-bold tracking-tight mb-4">
              {brand.store_name}
            </h3>
            <p className="text-gray-400 text-sm max-w-md">
              Tu tienda de confianza en Perú. Pagos seguros con Yape y Plin.
              Envíos a todo el país.
            </p>
          </div>

          {/* Links */}
          <div>
            <h4 className="label-caps text-gray-400 mb-4">Navegación</h4>
            <ul className="space-y-2 text-sm">
              <li>
                <a href="/" className="text-gray-300 hover:text-white transition-colors">
                  Inicio
                </a>
              </li>
              <li>
                <a href="/productos" className="text-gray-300 hover:text-white transition-colors">
                  Productos
                </a>
              </li>
              <li>
                <a href="/carrito" className="text-gray-300 hover:text-white transition-colors">
                  Carrito
                </a>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="label-caps text-gray-400 mb-4">Pagos</h4>
            <ul className="space-y-2 text-sm text-gray-300">
              {brand.yape_number && <li>Yape: {brand.yape_number}</li>}
              {brand.plin_number && <li>Plin: {brand.plin_number}</li>}
            </ul>
          </div>
        </div>

        <div className="border-t border-white/10 mt-12 pt-8 text-center text-sm text-gray-400">
          <p>&copy; {new Date().getFullYear()} {brand.store_name}. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
