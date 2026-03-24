import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import axios from "axios";
import { ArrowRight } from "lucide-react";
import { motion } from "framer-motion";
import { Navbar } from "@/components/layout/Navbar";
import { Footer } from "@/components/layout/Footer";
import { ProductCard } from "@/components/ProductCard";
import { API } from "@/App";

export default function HomePage() {
  const [products, setProducts] = useState([]);
  const [banners, setBanners] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [productsRes, bannersRes] = await Promise.all([
          axios.get(`${API}/products`),
          axios.get(`${API}/banners`),
        ]);
        setProducts(productsRes.data.slice(0, 8));
        setBanners(bannersRes.data);
      } catch (e) {
        console.error("Error fetching data:", e);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  const featuredBanner = banners.find((b) => b.size === "large") || banners[0];
  const smallBanners = banners.filter((b) => b.size === "small").slice(0, 2);

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      <main className="flex-1">
        {/* Hero Section - Bento Grid */}
        <section className="py-8 px-6 md:px-12 max-w-7xl mx-auto">
          <div className="bento-grid">
            {/* Large Banner */}
            <motion.div
              className="bento-large relative overflow-hidden rounded-sm bg-gray-100 min-h-[400px] md:min-h-[500px]"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              {featuredBanner ? (
                <Link
                  to={featuredBanner.link || "/productos"}
                  className="block h-full"
                  data-testid="hero-banner"
                >
                  <img
                    src={featuredBanner.image_url}
                    alt={featuredBanner.title}
                    className="w-full h-full object-cover"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                  <div className="absolute bottom-0 left-0 p-8 md:p-12">
                    <span className="label-caps text-white/80 mb-2 block">
                      Promoción Especial
                    </span>
                    <h1 className="text-3xl md:text-5xl font-bold text-white tracking-tight mb-4">
                      {featuredBanner.title}
                    </h1>
                    {featuredBanner.subtitle && (
                      <p className="text-white/90 text-lg mb-6 max-w-lg">
                        {featuredBanner.subtitle}
                      </p>
                    )}
                    <span className="inline-flex items-center gap-2 bg-white text-[hsl(var(--primary))] px-6 py-3 rounded-sm font-medium hover:bg-gray-100 transition-colors">
                      Ver Ofertas
                      <ArrowRight className="w-4 h-4" />
                    </span>
                  </div>
                </Link>
              ) : (
                <div className="h-full flex flex-col justify-end p-8 md:p-12">
                  <span className="label-caps text-gray-500 mb-2">Bienvenido</span>
                  <h1 className="text-3xl md:text-5xl font-bold tracking-tight mb-4">
                    Cyber Days
                  </h1>
                  <p className="text-gray-600 text-lg mb-6">
                    Descuentos increíbles en todos los productos
                  </p>
                  <Link
                    to="/productos"
                    className="inline-flex items-center gap-2 bg-[hsl(var(--primary))] text-white px-6 py-3 rounded-sm font-medium w-fit hover:bg-[hsl(220,20%,20%)] transition-colors"
                    data-testid="shop-now-button"
                  >
                    Comprar Ahora
                    <ArrowRight className="w-4 h-4" />
                  </Link>
                </div>
              )}
            </motion.div>

            {/* Small Banners */}
            <div className="bento-small flex flex-col gap-4">
              {smallBanners.length > 0 ? (
                smallBanners.map((banner, index) => (
                  <motion.div
                    key={banner.id}
                    className="flex-1 relative overflow-hidden rounded-sm bg-gray-100 min-h-[200px]"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: 0.1 * (index + 1) }}
                  >
                    <Link
                      to={banner.link || "/productos"}
                      className="block h-full"
                      data-testid={`small-banner-${index}`}
                    >
                      <img
                        src={banner.image_url}
                        alt={banner.title}
                        className="w-full h-full object-cover"
                      />
                      <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                      <div className="absolute bottom-0 left-0 p-4 md:p-6">
                        <h3 className="text-lg md:text-xl font-bold text-white">
                          {banner.title}
                        </h3>
                      </div>
                    </Link>
                  </motion.div>
                ))
              ) : (
                <>
                  <motion.div
                    className="flex-1 relative overflow-hidden rounded-sm bg-stone-100 p-6 flex flex-col justify-end"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: 0.1 }}
                  >
                    <span className="label-caps text-gray-500 mb-1">Pago Fácil</span>
                    <h3 className="text-lg font-bold">Yape & Plin</h3>
                  </motion.div>
                  <motion.div
                    className="flex-1 relative overflow-hidden rounded-sm bg-stone-100 p-6 flex flex-col justify-end"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: 0.2 }}
                  >
                    <span className="label-caps text-gray-500 mb-1">Envío Gratis</span>
                    <h3 className="text-lg font-bold">A Todo el Perú</h3>
                  </motion.div>
                </>
              )}
            </div>
          </div>
        </section>

        {/* Featured Products */}
        <section className="py-16 md:py-24 px-6 md:px-12 max-w-7xl mx-auto">
          <div className="flex items-end justify-between mb-12">
            <div>
              <span className="label-caps text-gray-500 mb-2 block">Colección</span>
              <h2 className="text-2xl md:text-3xl font-bold tracking-tight">
                Productos Destacados
              </h2>
            </div>
            <Link
              to="/productos"
              className="hidden md:inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-[hsl(var(--primary))] transition-colors"
              data-testid="view-all-products"
            >
              Ver Todos
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>

          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="animate-pulse">
                  <div className="aspect-square bg-gray-200 rounded-sm mb-4" />
                  <div className="h-4 bg-gray-200 rounded w-1/2 mb-2" />
                  <div className="h-5 bg-gray-200 rounded w-3/4" />
                </div>
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {products.map((product, index) => (
                <ProductCard key={product.id} product={product} index={index} />
              ))}
            </div>
          )}

          <Link
            to="/productos"
            className="md:hidden mt-8 flex items-center justify-center gap-2 text-sm font-medium text-gray-600 hover:text-[hsl(var(--primary))] transition-colors"
          >
            Ver Todos los Productos
            <ArrowRight className="w-4 h-4" />
          </Link>
        </section>

        {/* Features Section */}
        <section className="py-16 md:py-24 bg-stone-50">
          <div className="max-w-7xl mx-auto px-6 md:px-12">
            <div className="grid md:grid-cols-3 gap-8">
              <motion.div
                className="text-center md:text-left"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5 }}
              >
                <h3 className="text-lg font-semibold mb-2">Pago con Yape/Plin</h3>
                <p className="text-gray-600 text-sm">
                  Paga fácil y rápido con tu billetera digital favorita
                </p>
              </motion.div>
              <motion.div
                className="text-center md:text-left"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: 0.1 }}
              >
                <h3 className="text-lg font-semibold mb-2">Envío Seguro</h3>
                <p className="text-gray-600 text-sm">
                  Entrega a domicilio en todo el Perú
                </p>
              </motion.div>
              <motion.div
                className="text-center md:text-left"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: 0.2 }}
              >
                <h3 className="text-lg font-semibold mb-2">Calidad Garantizada</h3>
                <p className="text-gray-600 text-sm">
                  Productos seleccionados con los mejores estándares
                </p>
              </motion.div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
