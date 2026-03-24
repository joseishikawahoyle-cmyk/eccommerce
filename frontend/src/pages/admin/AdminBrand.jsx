import { useEffect, useState, useContext } from "react";
import axios from "axios";
import { Save, Upload } from "lucide-react";
import { motion } from "framer-motion";
import { AdminLayout } from "@/components/layout/AdminLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { AuthContext, BrandContext, API } from "@/App";
import { toast } from "sonner";

export default function AdminBrand() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const { token } = useContext(AuthContext);
  const { brand, setBrand } = useContext(BrandContext);

  const [formData, setFormData] = useState({
    store_name: "",
    primary_color: "220 20% 10%",
    yape_number: "",
    plin_number: "",
  });

  const headers = { Authorization: `Bearer ${token}` };

  useEffect(() => {
    const fetchBrand = async () => {
      try {
        const response = await axios.get(`${API}/brand`);
        setFormData({
          store_name: response.data.store_name || "",
          primary_color: response.data.primary_color || "220 20% 10%",
          yape_number: response.data.yape_number || "",
          plin_number: response.data.plin_number || "",
        });
      } catch (e) {
        console.error("Error fetching brand:", e);
      } finally {
        setLoading(false);
      }
    };
    fetchBrand();
  }, []);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);

    try {
      const response = await axios.put(`${API}/admin/brand`, formData, {
        headers,
      });
      setBrand(response.data);
      document.documentElement.style.setProperty(
        "--primary",
        formData.primary_color
      );
      toast.success("Configuración guardada");
    } catch (error) {
      toast.error("Error al guardar");
    } finally {
      setSaving(false);
    }
  };

  const colorPresets = [
    { name: "Charcoal", value: "220 20% 10%" },
    { name: "Navy", value: "220 50% 20%" },
    { name: "Forest", value: "150 40% 25%" },
    { name: "Burgundy", value: "350 50% 30%" },
    { name: "Terracotta", value: "20 60% 45%" },
    { name: "Slate", value: "210 20% 35%" },
  ];

  if (loading) {
    return (
      <AdminLayout>
        <div className="animate-pulse space-y-4">
          <div className="h-8 bg-gray-200 rounded w-1/3" />
          <div className="h-64 bg-gray-200 rounded" />
        </div>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout>
      <div className="mb-8">
        <h1 className="text-2xl font-bold tracking-tight" data-testid="admin-brand-title">
          Configuración de Marca
        </h1>
        <p className="text-gray-600">Personaliza la identidad de tu tienda</p>
      </div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white rounded-sm border border-gray-100 p-6"
      >
        <form onSubmit={handleSubmit} className="space-y-6 max-w-xl">
          {/* Store Name */}
          <div>
            <Label htmlFor="store_name">Nombre de la Tienda</Label>
            <Input
              id="store_name"
              name="store_name"
              value={formData.store_name}
              onChange={handleChange}
              className="mt-1 rounded-sm"
              data-testid="brand-store-name-input"
            />
          </div>

          {/* Primary Color */}
          <div>
            <Label>Color Principal (HSL)</Label>
            <div className="mt-2 flex flex-wrap gap-2">
              {colorPresets.map((color) => (
                <button
                  key={color.value}
                  type="button"
                  onClick={() =>
                    setFormData({ ...formData, primary_color: color.value })
                  }
                  className={`w-10 h-10 rounded-sm border-2 transition-transform hover:scale-105 ${
                    formData.primary_color === color.value
                      ? "border-black scale-105"
                      : "border-transparent"
                  }`}
                  style={{ backgroundColor: `hsl(${color.value})` }}
                  title={color.name}
                  data-testid={`color-preset-${color.name.toLowerCase()}`}
                />
              ))}
            </div>
            <Input
              name="primary_color"
              value={formData.primary_color}
              onChange={handleChange}
              className="mt-2 rounded-sm font-mono text-sm"
              placeholder="220 20% 10%"
              data-testid="brand-primary-color-input"
            />
            <p className="text-xs text-gray-500 mt-1">
              Formato HSL: Hue (0-360) Saturation% Lightness%
            </p>
          </div>

          {/* Preview */}
          <div>
            <Label>Vista Previa</Label>
            <div className="mt-2 p-4 bg-stone-50 rounded-sm">
              <div
                className="p-4 rounded-sm text-white font-medium"
                style={{ backgroundColor: `hsl(${formData.primary_color})` }}
              >
                {formData.store_name || "Mi Tienda"}
              </div>
            </div>
          </div>

          {/* Payment Numbers */}
          <div className="border-t pt-6">
            <h2 className="font-medium mb-4">Datos de Pago</h2>
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="yape_number">Número Yape</Label>
                <Input
                  id="yape_number"
                  name="yape_number"
                  value={formData.yape_number}
                  onChange={handleChange}
                  className="mt-1 rounded-sm"
                  placeholder="999888777"
                  data-testid="brand-yape-input"
                />
              </div>
              <div>
                <Label htmlFor="plin_number">Número Plin</Label>
                <Input
                  id="plin_number"
                  name="plin_number"
                  value={formData.plin_number}
                  onChange={handleChange}
                  className="mt-1 rounded-sm"
                  placeholder="999888777"
                  data-testid="brand-plin-input"
                />
              </div>
            </div>
          </div>

          <Button
            type="submit"
            disabled={saving}
            className="btn-primary"
            data-testid="save-brand-button"
          >
            <Save className="w-4 h-4 mr-2" />
            {saving ? "Guardando..." : "Guardar Configuración"}
          </Button>
        </form>
      </motion.div>
    </AdminLayout>
  );
}
