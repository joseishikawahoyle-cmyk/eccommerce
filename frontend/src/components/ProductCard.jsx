import { useContext } from "react";
import { Link } from "react-router-dom";
import { ShoppingBag } from "lucide-react";
import { CartContext, API } from "@/App";
import { motion } from "framer-motion";
import { toast } from "sonner";

export const ProductCard = ({ product, index = 0 }) => {
  const { addToCart } = useContext(CartContext);

  const handleAddToCart = (e) => {
    e.preventDefault();
    e.stopPropagation();
    addToCart(product, 1);
    toast.success(`${product.name} agregado al carrito`);
  };

  const imageUrl = product.images?.[0]
    ? product.images[0].startsWith("http")
      ? product.images[0]
      : `${API}/files/${product.images[0]}`
    : "https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg";

  const price = product.current_price || product.price;
  const originalPrice = product.on_sale ? product.price : null;

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: index * 0.1 }}
    >
      <Link
        to={`/producto/${product.id}`}
        className="product-card group block"
        data-testid={`product-card-${product.id}`}
      >
        <div className="relative aspect-square overflow-hidden bg-gray-100 mb-4">
          <img
            src={imageUrl}
            alt={product.name}
            className="product-image w-full h-full object-cover"
            loading="lazy"
          />
          {product.on_sale && (
            <span className="sale-badge absolute top-3 left-3" data-testid="sale-badge">
              OFERTA
            </span>
          )}
          <button
            onClick={handleAddToCart}
            className="absolute bottom-3 right-3 p-3 bg-white/90 backdrop-blur-sm rounded-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white"
            data-testid={`add-to-cart-${product.id}`}
          >
            <ShoppingBag className="w-5 h-5" />
          </button>
        </div>

        <div>
          <p className="label-caps text-gray-500 mb-1">{product.category}</p>
          <h3 className="font-medium text-[hsl(var(--foreground))] mb-2 line-clamp-2">
            {product.name}
          </h3>
          <div className="flex items-center gap-2">
            <span className="font-semibold text-lg" data-testid="product-price">
              S/ {price.toFixed(2)}
            </span>
            {originalPrice && (
              <span className="text-gray-400 line-through text-sm">
                S/ {originalPrice.toFixed(2)}
              </span>
            )}
          </div>
          {product.stock <= 5 && product.stock > 0 && (
            <p className="text-xs text-orange-600 mt-1">
              ¡Solo quedan {product.stock}!
            </p>
          )}
          {product.stock === 0 && (
            <p className="text-xs text-red-600 mt-1">Agotado</p>
          )}
        </div>
      </Link>
    </motion.div>
  );
};

export default ProductCard;
