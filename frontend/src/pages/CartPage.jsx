import { useContext } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Trash2, Minus, Plus, ArrowLeft, ArrowRight } from "lucide-react";
import { motion } from "framer-motion";
import { Navbar } from "@/components/layout/Navbar";
import { Footer } from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { CartContext, API } from "@/App";

export default function CartPage() {
  const { cart, removeFromCart, updateQuantity, getTotal } = useContext(CartContext);
  const navigate = useNavigate();

  const getImageUrl = (img) => {
    if (!img) return "https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg";
    return img.startsWith("http") ? img : `${API}/files/${img}`;
  };

  if (cart.length === 0) {
    return (
      <div className="min-h-screen flex flex-col">
        <Navbar />
        <main className="flex-1 py-12 px-6 md:px-12 max-w-7xl mx-auto text-center">
          <h1 className="text-2xl md:text-4xl font-bold tracking-tight mb-4">
            Tu carrito está vacío
          </h1>
          <p className="text-gray-600 mb-8">
            Agrega productos para comenzar tu compra
          </p>
          <Link
            to="/productos"
            className="inline-flex items-center gap-2 btn-primary"
            data-testid="continue-shopping"
          >
            Explorar Productos
            <ArrowRight className="w-4 h-4" />
          </Link>
        </main>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      <main className="flex-1 py-8 md:py-12 px-6 md:px-12 max-w-7xl mx-auto w-full">
        {/* Header */}
        <div className="mb-8 md:mb-12">
          <Link
            to="/productos"
            className="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[hsl(var(--primary))] mb-4 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
            Seguir comprando
          </Link>
          <h1 className="text-2xl md:text-4xl font-bold tracking-tight">
            Tu Carrito
          </h1>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-2 space-y-4">
            {cart.map((item, index) => (
              <motion.div
                key={item.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.1 }}
                className="flex gap-4 p-4 bg-white border border-gray-100 rounded-sm"
                data-testid={`cart-item-${item.id}`}
              >
                <Link
                  to={`/producto/${item.id}`}
                  className="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-sm overflow-hidden"
                >
                  <img
                    src={getImageUrl(item.images?.[0])}
                    alt={item.name}
                    className="w-full h-full object-cover"
                  />
                </Link>

                <div className="flex-1 flex flex-col">
                  <div className="flex justify-between">
                    <div>
                      <Link
                        to={`/producto/${item.id}`}
                        className="font-medium hover:text-[hsl(var(--primary))] transition-colors"
                      >
                        {item.name}
                      </Link>
                      <p className="text-sm text-gray-500">{item.category}</p>
                    </div>
                    <button
                      onClick={() => removeFromCart(item.id)}
                      className="p-2 text-gray-400 hover:text-red-500 transition-colors"
                      data-testid={`remove-item-${item.id}`}
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>

                  <div className="mt-auto flex items-center justify-between">
                    <div className="flex items-center border border-gray-200 rounded-sm">
                      <button
                        onClick={() => updateQuantity(item.id, item.quantity - 1)}
                        className="p-2 hover:bg-gray-100 transition-colors"
                        data-testid={`decrease-${item.id}`}
                      >
                        <Minus className="w-3 h-3" />
                      </button>
                      <span className="px-3 text-sm font-medium">
                        {item.quantity}
                      </span>
                      <button
                        onClick={() =>
                          updateQuantity(
                            item.id,
                            Math.min(item.stock, item.quantity + 1)
                          )
                        }
                        className="p-2 hover:bg-gray-100 transition-colors"
                        data-testid={`increase-${item.id}`}
                      >
                        <Plus className="w-3 h-3" />
                      </button>
                    </div>

                    <span className="font-semibold">
                      S/ {((item.current_price || item.price) * item.quantity).toFixed(2)}
                    </span>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-stone-50 p-6 rounded-sm sticky top-24">
              <h2 className="text-lg font-bold mb-4">Resumen del Pedido</h2>

              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-600">Subtotal</span>
                  <span>S/ {getTotal().toFixed(2)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-600">Envío</span>
                  <span className="text-green-600">Por calcular</span>
                </div>
                <div className="border-t border-gray-200 pt-3 flex justify-between font-bold">
                  <span>Total</span>
                  <span data-testid="cart-total">S/ {getTotal().toFixed(2)}</span>
                </div>
              </div>

              <Button
                onClick={() => navigate("/checkout")}
                className="w-full btn-primary"
                data-testid="proceed-to-checkout"
              >
                Proceder al Pago
                <ArrowRight className="w-4 h-4 ml-2" />
              </Button>

              <p className="text-xs text-gray-500 text-center mt-4">
                Aceptamos Yape, Plin y transferencias
              </p>
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
