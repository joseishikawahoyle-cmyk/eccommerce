import { useEffect, useState, useContext } from "react";
import axios from "axios";
import { Plus, Pencil, Trash2, Upload, X } from "lucide-react";
import { motion } from "framer-motion";
import { AdminLayout } from "@/components/layout/AdminLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { AuthContext, API } from "@/App";
import { toast } from "sonner";

export default function AdminBanners() {
  const [banners, setBanners] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingBanner, setEditingBanner] = useState(null);
  const [uploading, setUploading] = useState(false);
  const { token } = useContext(AuthContext);

  const [formData, setFormData] = useState({
    title: "",
    subtitle: "",
    image_url: "",
    link: "",
    size: "large",
    position: 0,
    is_active: true,
  });

  const headers = { Authorization: `Bearer ${token}` };

  const fetchBanners = async () => {
    try {
      const response = await axios.get(`${API}/admin/banners`, { headers });
      setBanners(response.data);
    } catch (e) {
      console.error("Error fetching banners:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBanners();
  }, [token]);

  const resetForm = () => {
    setFormData({
      title: "",
      subtitle: "",
      image_url: "",
      link: "",
      size: "large",
      position: 0,
      is_active: true,
    });
    setEditingBanner(null);
  };

  const openDialog = (banner = null) => {
    if (banner) {
      setEditingBanner(banner);
      setFormData({
        title: banner.title,
        subtitle: banner.subtitle || "",
        image_url: banner.image_url,
        link: banner.link || "",
        size: banner.size,
        position: banner.position,
        is_active: banner.is_active,
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
    const file = e.target.files[0];
    if (!file) return;

    setUploading(true);
    const form = new FormData();
    form.append("file", file);
    form.append("folder", "banners");

    try {
      const response = await axios.post(`${API}/upload`, form, {
        headers: { ...headers, "Content-Type": "multipart/form-data" },
      });
      setFormData({ ...formData, image_url: `${API}/files/${response.data.path}` });
      toast.success("Imagen subida");
    } catch (error) {
      toast.error("Error al subir imagen");
    } finally {
      setUploading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      if (editingBanner) {
        await axios.put(`${API}/admin/banners/${editingBanner.id}`, formData, {
          headers,
        });
        toast.success("Banner actualizado");
      } else {
        await axios.post(`${API}/admin/banners`, formData, { headers });
        toast.success("Banner creado");
      }
      setIsDialogOpen(false);
      resetForm();
      fetchBanners();
    } catch (error) {
      toast.error(error.response?.data?.detail || "Error al guardar");
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm("¿Estás seguro de eliminar este banner?")) return;

    try {
      await axios.delete(`${API}/admin/banners/${id}`, { headers });
      toast.success("Banner eliminado");
      fetchBanners();
    } catch (error) {
      toast.error("Error al eliminar");
    }
  };

  return (
    <AdminLayout>
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold tracking-tight" data-testid="admin-banners-title">
            Banners
          </h1>
          <p className="text-gray-600">Gestiona los banners promocionales</p>
        </div>
        <Button
          onClick={() => openDialog()}
          className="btn-primary"
          data-testid="add-banner-button"
        >
          <Plus className="w-4 h-4 mr-2" />
          Nuevo Banner
        </Button>
      </div>

      {loading ? (
        <div className="grid md:grid-cols-2 gap-6">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="animate-pulse bg-gray-200 h-48 rounded-sm" />
          ))}
        </div>
      ) : banners.length === 0 ? (
        <div className="text-center py-16 bg-white rounded-sm border border-gray-100">
          <p className="text-gray-500 mb-4">No hay banners aún</p>
          <Button onClick={() => openDialog()} className="btn-primary">
            <Plus className="w-4 h-4 mr-2" />
            Crear Primer Banner
          </Button>
        </div>
      ) : (
        <div className="grid md:grid-cols-2 gap-6">
          {banners.map((banner, index) => (
            <motion.div
              key={banner.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
              className="bg-white rounded-sm border border-gray-100 overflow-hidden"
              data-testid={`banner-card-${banner.id}`}
            >
              <div className="aspect-video bg-gray-100 relative">
                <img
                  src={banner.image_url}
                  alt={banner.title}
                  className="w-full h-full object-cover"
                />
                {!banner.is_active && (
                  <span className="absolute top-2 left-2 px-2 py-1 bg-gray-800 text-white text-xs rounded-sm">
                    Inactivo
                  </span>
                )}
                <span className="absolute top-2 right-2 px-2 py-1 bg-white/90 text-xs font-medium rounded-sm">
                  {banner.size === "large" ? "Grande" : "Pequeño"}
                </span>
              </div>
              <div className="p-4">
                <h3 className="font-medium mb-1">{banner.title}</h3>
                {banner.subtitle && (
                  <p className="text-sm text-gray-500 mb-2">{banner.subtitle}</p>
                )}
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => openDialog(banner)}
                    className="flex-1 rounded-sm"
                    data-testid={`edit-banner-${banner.id}`}
                  >
                    <Pencil className="w-4 h-4 mr-1" />
                    Editar
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleDelete(banner.id)}
                    className="text-red-600 hover:text-red-700 rounded-sm"
                    data-testid={`delete-banner-${banner.id}`}
                  >
                    <Trash2 className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      )}

      {/* Banner Dialog */}
      <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>
              {editingBanner ? "Editar Banner" : "Nuevo Banner"}
            </DialogTitle>
          </DialogHeader>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <Label htmlFor="title">Título</Label>
              <Input
                id="title"
                name="title"
                value={formData.title}
                onChange={handleChange}
                required
                className="mt-1 rounded-sm"
                data-testid="banner-title-input"
              />
            </div>

            <div>
              <Label htmlFor="subtitle">Subtítulo</Label>
              <Input
                id="subtitle"
                name="subtitle"
                value={formData.subtitle}
                onChange={handleChange}
                className="mt-1 rounded-sm"
                data-testid="banner-subtitle-input"
              />
            </div>

            {/* Image */}
            <div>
              <Label>Imagen</Label>
              <div className="mt-2">
                {formData.image_url ? (
                  <div className="relative aspect-video bg-gray-100 rounded-sm overflow-hidden">
                    <img
                      src={formData.image_url}
                      alt="Preview"
                      className="w-full h-full object-cover"
                    />
                    <button
                      type="button"
                      onClick={() => setFormData({ ...formData, image_url: "" })}
                      className="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full"
                    >
                      <X className="w-4 h-4" />
                    </button>
                  </div>
                ) : (
                  <label className="block aspect-video border-2 border-dashed border-gray-300 rounded-sm flex flex-col items-center justify-center cursor-pointer hover:border-gray-400">
                    <input
                      type="file"
                      accept="image/*"
                      onChange={handleImageUpload}
                      className="hidden"
                      disabled={uploading}
                    />
                    <Upload className="w-8 h-8 text-gray-400 mb-2" />
                    <span className="text-sm text-gray-500">
                      {uploading ? "Subiendo..." : "Subir imagen"}
                    </span>
                  </label>
                )}
              </div>
              <Input
                name="image_url"
                value={formData.image_url}
                onChange={handleChange}
                className="mt-2 rounded-sm text-sm"
                placeholder="O ingresa URL de imagen"
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="size">Tamaño</Label>
                <Select
                  value={formData.size}
                  onValueChange={(value) =>
                    setFormData({ ...formData, size: value })
                  }
                >
                  <SelectTrigger className="mt-1 rounded-sm">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="large">Grande (Principal)</SelectItem>
                    <SelectItem value="small">Pequeño (Secundario)</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="position">Posición</Label>
                <Input
                  id="position"
                  name="position"
                  type="number"
                  value={formData.position}
                  onChange={handleChange}
                  className="mt-1 rounded-sm"
                />
              </div>
            </div>

            <div>
              <Label htmlFor="link">Link (opcional)</Label>
              <Input
                id="link"
                name="link"
                value={formData.link}
                onChange={handleChange}
                className="mt-1 rounded-sm"
                placeholder="/productos o URL externa"
              />
            </div>

            <div className="flex items-center gap-2">
              <Switch
                checked={formData.is_active}
                onCheckedChange={(checked) =>
                  setFormData({ ...formData, is_active: checked })
                }
                data-testid="banner-active-switch"
              />
              <Label>Banner activo</Label>
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
              <Button type="submit" className="flex-1 btn-primary" data-testid="save-banner-button">
                {editingBanner ? "Guardar Cambios" : "Crear Banner"}
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </AdminLayout>
  );
}
