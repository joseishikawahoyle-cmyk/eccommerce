import { useEffect, useState, useContext } from "react";
import axios from "axios";
import { Plus, Pencil, Trash2, Upload, X } from "lucide-react";
import { motion } from "framer-motion";
import { AdminLayout } from "@/components/layout/AdminLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { AuthContext, API } from "@/App";
import { toast } from "sonner";

export default function AdminProducts() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState(null);
  const [uploading, setUploading] = useState(false);
  const { token } = useContext(AuthContext);

  const [formData, setFormData] = useState({
    name: "",
    description: "",
    price: "",
    sale_price: "",
    stock: "",
    category: "",
    images: [],
    is_active: true,
  });

  const headers = { Authorization: `Bearer ${token}` };

  const fetchProducts = async () => {
    try {
      const response = await axios.get(`${API}/admin/products`, { headers });
      setProducts(response.data);
    } catch (e) {
      console.error("Error fetching products:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, [token]);

  const resetForm = () => {
    setFormData({
      name: "",
      description: "",
      price: "",
      sale_price: "",
      stock: "",
      category: "",
      images: [],
      is_active: true,
    });
    setEditingProduct(null);
  };

  const openDialog = (product = null) => {
    if (product) {
      setEditingProduct(product);
      setFormData({
        name: product.name,
        description: product.description,
        price: product.price.toString(),
        sale_price: product.sale_price?.toString() || "",
        stock: product.stock.toString(),
        category: product.category,
        images: product.images || [],
        is_active: product.is_active,
      });
    } else {
      resetForm();
    }
    setIsDialogOpen(true);
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleImageUpload = async (e) => {
    const files = e.target.files;
    if (!files.length) return;

    setUploading(true);
    const newImages = [...formData.images];

    for (const file of files) {
      const form = new FormData();
      form.append("file", file);
      form.append("folder", "products");

      try {
        const response = await axios.post(`${API}/upload`, form, {
          headers: { ...headers, "Content-Type": "multipart/form-data" },
        });
        newImages.push(response.data.path);
      } catch (error) {
        toast.error(`Error al subir ${file.name}`);
      }
    }

    setFormData({ ...formData, images: newImages });
    setUploading(false);
  };

  const removeImage = (index) => {
    const newImages = formData.images.filter((_, i) => i !== index);
    setFormData({ ...formData, images: newImages });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const data = {
      ...formData,
      price: parseFloat(formData.price),
      sale_price: formData.sale_price ? parseFloat(formData.sale_price) : null,
      stock: parseInt(formData.stock),
    };

    try {
      if (editingProduct) {
        await axios.put(`${API}/admin/products/${editingProduct.id}`, data, {
          headers,
        });
        toast.success("Producto actualizado");
      } else {
        await axios.post(`${API}/admin/products`, data, { headers });
        toast.success("Producto creado");
      }
      setIsDialogOpen(false);
      resetForm();
      fetchProducts();
    } catch (error) {
      toast.error(error.response?.data?.detail || "Error al guardar");
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm("¿Estás seguro de eliminar este producto?")) return;

    try {
      await axios.delete(`${API}/admin/products/${id}`, { headers });
      toast.success("Producto eliminado");
      fetchProducts();
    } catch (error) {
      toast.error("Error al eliminar");
    }
  };

  const getImageUrl = (img) => {
    if (!img) return "";
    return img.startsWith("http") ? img : `${API}/files/${img}`;
  };

  return (
    <AdminLayout>
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold tracking-tight" data-testid="admin-products-title">
            Productos
          </h1>
          <p className="text-gray-600">Gestiona tu catálogo de productos</p>
        </div>
        <Button
          onClick={() => openDialog()}
          className="btn-primary"
          data-testid="add-product-button"
        >
          <Plus className="w-4 h-4 mr-2" />
          Nuevo Producto
        </Button>
      </div>

      {loading ? (
        <div className="grid md:grid-cols-3 gap-6">
          {[...Array(6)].map((_, i) => (
            <div key={i} className="animate-pulse bg-gray-200 h-64 rounded-sm" />
          ))}
        </div>
      ) : products.length === 0 ? (
        <div className="text-center py-16 bg-white rounded-sm border border-gray-100">
          <p className="text-gray-500 mb-4">No hay productos aún</p>
          <Button onClick={() => openDialog()} className="btn-primary">
            <Plus className="w-4 h-4 mr-2" />
            Crear Primer Producto
          </Button>
        </div>
      ) : (
        <div className="grid md:grid-cols-3 gap-6">
          {products.map((product, index) => (
            <motion.div
              key={product.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
              className="bg-white rounded-sm border border-gray-100 overflow-hidden"
              data-testid={`product-card-${product.id}`}
            >
              <div className="aspect-square bg-gray-100 relative">
                {product.images?.[0] ? (
                  <img
                    src={getImageUrl(product.images[0])}
                    alt={product.name}
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-gray-400">
                    Sin imagen
                  </div>
                )}
                {!product.is_active && (
                  <span className="absolute top-2 left-2 px-2 py-1 bg-gray-800 text-white text-xs rounded-sm">
                    Inactivo
                  </span>
                )}
              </div>
              <div className="p-4">
                <h3 className="font-medium mb-1 line-clamp-1">{product.name}</h3>
                <p className="text-sm text-gray-500 mb-2">{product.category}</p>
                <div className="flex items-center justify-between">
                  <div>
                    <span className="font-bold">S/ {product.price.toFixed(2)}</span>
                    {product.sale_price && (
                      <span className="ml-2 text-sm text-red-600">
                        S/ {product.sale_price.toFixed(2)}
                      </span>
                    )}
                  </div>
                  <span className="text-sm text-gray-500">
                    Stock: {product.stock}
                  </span>
                </div>
                <div className="flex gap-2 mt-4">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => openDialog(product)}
                    className="flex-1 rounded-sm"
                    data-testid={`edit-product-${product.id}`}
                  >
                    <Pencil className="w-4 h-4 mr-1" />
                    Editar
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleDelete(product.id)}
                    className="text-red-600 hover:text-red-700 rounded-sm"
                    data-testid={`delete-product-${product.id}`}
                  >
                    <Trash2 className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      )}

      {/* Product Dialog */}
      <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              {editingProduct ? "Editar Producto" : "Nuevo Producto"}
            </DialogTitle>
          </DialogHeader>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="name">Nombre</Label>
                <Input
                  id="name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  required
                  className="mt-1 rounded-sm"
                  data-testid="product-name-input"
                />
              </div>
              <div>
                <Label htmlFor="category">Categoría</Label>
                <Input
                  id="category"
                  name="category"
                  value={formData.category}
                  onChange={handleChange}
                  required
                  className="mt-1 rounded-sm"
                  placeholder="Ej: Bolsos, Accesorios"
                  data-testid="product-category-input"
                />
              </div>
            </div>

            <div>
              <Label htmlFor="description">Descripción</Label>
              <Textarea
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                required
                className="mt-1 rounded-sm"
                rows={3}
                data-testid="product-description-input"
              />
            </div>

            <div className="grid md:grid-cols-3 gap-4">
              <div>
                <Label htmlFor="price">Precio (S/)</Label>
                <Input
                  id="price"
                  name="price"
                  type="number"
                  step="0.01"
                  value={formData.price}
                  onChange={handleChange}
                  required
                  className="mt-1 rounded-sm"
                  data-testid="product-price-input"
                />
              </div>
              <div>
                <Label htmlFor="sale_price">Precio Oferta (S/)</Label>
                <Input
                  id="sale_price"
                  name="sale_price"
                  type="number"
                  step="0.01"
                  value={formData.sale_price}
                  onChange={handleChange}
                  className="mt-1 rounded-sm"
                  placeholder="Opcional"
                  data-testid="product-sale-price-input"
                />
              </div>
              <div>
                <Label htmlFor="stock">Stock</Label>
                <Input
                  id="stock"
                  name="stock"
                  type="number"
                  value={formData.stock}
                  onChange={handleChange}
                  required
                  className="mt-1 rounded-sm"
                  data-testid="product-stock-input"
                />
              </div>
            </div>

            {/* Images */}
            <div>
              <Label>Imágenes</Label>
              <div className="mt-2 flex flex-wrap gap-2">
                {formData.images.map((img, index) => (
                  <div
                    key={index}
                    className="relative w-20 h-20 bg-gray-100 rounded-sm overflow-hidden"
                  >
                    <img
                      src={getImageUrl(img)}
                      alt={`Imagen ${index + 1}`}
                      className="w-full h-full object-cover"
                    />
                    <button
                      type="button"
                      onClick={() => removeImage(index)}
                      className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full"
                    >
                      <X className="w-3 h-3" />
                    </button>
                  </div>
                ))}
                <label className="w-20 h-20 border-2 border-dashed border-gray-300 rounded-sm flex flex-col items-center justify-center cursor-pointer hover:border-gray-400">
                  <input
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={handleImageUpload}
                    className="hidden"
                    disabled={uploading}
                  />
                  <Upload className="w-5 h-5 text-gray-400" />
                  <span className="text-xs text-gray-400 mt-1">
                    {uploading ? "..." : "Subir"}
                  </span>
                </label>
              </div>
            </div>

            {/* Active Toggle */}
            <div className="flex items-center gap-2">
              <Switch
                checked={formData.is_active}
                onCheckedChange={(checked) =>
                  setFormData({ ...formData, is_active: checked })
                }
                data-testid="product-active-switch"
              />
              <Label>Producto activo</Label>
            </div>

            <div className="flex gap-4 pt-4">
              <Button
                type="button"
                variant="outline"
                onClick={() => setIsDialogOpen(false)}
                className="flex-1 rounded-sm"
              >
                Cancelar
              </Button>
              <Button type="submit" className="flex-1 btn-primary" data-testid="save-product-button">
                {editingProduct ? "Guardar Cambios" : "Crear Producto"}
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </AdminLayout>
  );
}
