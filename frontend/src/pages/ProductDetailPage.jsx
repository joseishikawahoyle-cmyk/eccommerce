import { useEffect, useState, useContext } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import { Minus, Plus, ShoppingBag, ArrowLeft, Check } from "lucide-react";
import { motion } from "framer-motion";
import { Navbar } from "@/components/layout/Navbar";
import { Footer } from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { CartContext, API } from "@/App";
import { toast } from "sonner";

export default function ProductDetailPage() {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [selectedImage, setSelectedImage] = useState(0);
  const { addToCart } = useContext(CartContext);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const response = await axios.get(`${API}/products/${id}`);
        setProduct(response.data);
      } catch (e) {
        console.error("Error fetching product:", e);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();
  }, [id]);

  const handleAddToCart = () => {
    if (product) {
      addToCart(product, quantity);
      toast.success(`${product.name} agregado al carrito`);
    }
  };

  const getImageUrl = (img) => {
    if (!img) return "https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg";
    return img.startsWith("http") ? img : `${API}/files/${img}`;
  };

  if (loading) {
    return (
      <div className="min-h-screen flex flex-col">
        <Navbar />
        <main className="flex-1 py-12 px-6 md:px-12 max-w-7xl mx-auto">
          <div className="animate-pulse grid md:grid-cols-2 gap-12">
            <div className="aspect-square bg-gray-200 rounded-sm" />
            <div className="space-y-4">
              <div className="h-6 bg-gray-200 rounded w-1/4" />
              <div className="h-10 bg-gray-200 rounded w-3/4" />
              <div className="h-8 bg-gray-200 rounded w-1/3" />
              <div className="h-32 bg-gray-200 rounded" />
            </div>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="min-h-screen flex flex-col">
        <Navbar />
        <main className="flex-1 py-12 px-6 md:px-12 max-w-7xl mx-auto text-center">
          <h1 className="text-2xl font-bold mb-4">Producto no encontrado</h1>
          <Link to="/productos" className="text-[hsl(var(--primary))] hover:underline">
            Volver al catálogo
          </Link>
        </main>
        <Footer />
      </div>
    );
  }

  const images = product.images?.length > 0 ? product.images : [null];
  const price = product.current_price || product.price;

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      <main className="flex-1 py-8 md:py-12 px-6 md:px-12 max-w-7xl mx-auto w-full">
        {/* Breadcrumb */}
        <Link
          to="/productos"
          className="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[hsl(var(--primary))] mb-8 transition-colors"
          data-testid="back-to-products"
        >
          <ArrowLeft className="w-4 h-4" />
          Volver al catálogo
        </Link>

        <div className="grid md:grid-cols-2 gap-8 md:gap-12">
          {/* Images */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5 }}
          >
            <div className="aspect-square overflow-hidden bg-gray-100 rounded-sm mb-4">
              <img
                src={getImageUrl(images[selectedImage])}
                alt={product.name}
                className="w-full h-full object-cover"
                data-testid="product-main-image"
              />
            </div>

            {/* Thumbnails */}
            {images.length > 1 && (
              <div className="flex gap-2 overflow-x-auto pb-2">
                {images.map((img, index) => (
                  <button
                    key={index}
                    onClick={() => setSelectedImage(index)}
                    className={`flex-shrink-0 w-20 h-20 overflow-hidden rounded-sm border-2 transition-colors ${
                      selectedImage === index
                        ? "border-[hsl(var(--primary))]"
                        : "border-transparent"
                    }`}
                    data-testid={`thumbnail-${index}`}
                  >
                    <img
                      src={getImageUrl(img)}
                      alt={`${product.name} ${index + 1}`}
                      className="w-full h-full object-cover"
                    />
                  </button>
                ))}
              </div>
            )}
          </motion.div>

          {/* Product Info */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.1 }}
            className="flex flex-col"
          >
            <span className="label-caps text-gray-500 mb-2">{product.category}</span>
            <h1
              className="text-2xl md:text-4xl font-bold tracking-tight mb-4"
              data-testid="product-name"
            >
              {product.name}
            </h1>

            {/* Price */}
            <div className="flex items-baseline gap-3 mb-6">
              <span
                className="text-2xl md:text-3xl font-bold"
                data-testid="product-price"
              >
                S/ {price.toFixed(2)}
              </span>
              {product.on_sale && (
                <span className="text-lg text-gray-400 line-through">
                  S/ {product.price.toFixed(2)}
                </span>
              )}
              {product.on_sale && (
                <span className="sale-badge">
                  -{Math.round((1 - price / product.price) * 100)}%
                </span>
              )}
            </div>

            {/* Stock */}
            <div className="flex items-center gap-2 mb-6">
              {product.stock > 0 ? (
                <>
                  <Check className="w-4 h-4 text-green-600" />
                  <span className="text-sm text-green-600">
                    En stock ({product.stock} disponibles)
                  </span>
                </>
              ) : (
                <span className="text-sm text-red-600">Agotado</span>
              )}
            </div>

            {/* Description */}
            <p className="text-gray-600 mb-8 leading-relaxed" data-testid="product-description">
              {product.description}
            </p>

            {/* Quantity & Add to Cart */}
            {product.stock > 0 && (
              <div className="flex flex-col sm:flex-row gap-4 mt-auto">
                <div className="flex items-center border border-gray-200 rounded-sm">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="p-3 hover:bg-gray-100 transition-colors"
                    data-testid="quantity-decrease"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span
                    className="px-6 py-3 font-medium min-w-[60px] text-center"
                    data-testid="quantity-value"
                  >
                    {quantity}
                  </span>
                  <button
                    onClick={() =>
                      setQuantity(Math.min(product.stock, quantity + 1))
                    }
                    className="p-3 hover:bg-gray-100 transition-colors"
                    data-testid="quantity-increase"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>

                <Button
                  onClick={handleAddToCart}
                  className="flex-1 btn-primary flex items-center justify-center gap-2"
                  data-testid="add-to-cart-button"
                >
                  <ShoppingBag className="w-5 h-5" />
                  Agregar al Carrito
                </Button>
              </div>
            )}

            {/* Payment Info */}
            <div className="mt-8 p-4 bg-stone-50 rounded-sm">
              <p className="text-sm font-medium mb-2">Métodos de Pago</p>
              <p className="text-sm text-gray-600">
                Aceptamos Yape, Plin y transferencias bancarias
              </p>
            </div>
          </motion.div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
