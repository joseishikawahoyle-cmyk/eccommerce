import { useState, useContext } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import { motion } from "framer-motion";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { AuthContext, API } from "@/App";
import { toast } from "sonner";

export default function AdminLoginPage() {
  const [isRegister, setIsRegister] = useState(false);
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    email: "",
    password: "",
    name: "",
  });

  const { login } = useContext(AuthContext);
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const endpoint = isRegister ? "/admin/register" : "/admin/login";
      const data = isRegister
        ? formData
        : { email: formData.email, password: formData.password };

      const response = await axios.post(`${API}${endpoint}`, data);
      login(response.data.token, response.data.user);
      toast.success(isRegister ? "Cuenta creada exitosamente" : "Bienvenido");
      navigate("/admin");
    } catch (error) {
      toast.error(
        error.response?.data?.detail || "Error de autenticación"
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-stone-50 px-6">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="w-full max-w-md"
      >
        <div className="bg-white p-8 rounded-sm shadow-sm border border-gray-100">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold tracking-tight mb-2">
              {isRegister ? "Crear Cuenta Admin" : "Panel de Administración"}
            </h1>
            <p className="text-gray-600 text-sm">
              {isRegister
                ? "Registra una cuenta de administrador"
                : "Ingresa tus credenciales"}
            </p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            {isRegister && (
              <div>
                <Label htmlFor="name">Nombre</Label>
                <Input
                  id="name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  required
                  className="mt-1 rounded-sm"
                  data-testid="admin-name-input"
                />
              </div>
            )}

            <div>
              <Label htmlFor="email">Correo Electrónico</Label>
              <Input
                id="email"
                name="email"
                type="email"
                value={formData.email}
                onChange={handleChange}
                required
                className="mt-1 rounded-sm"
                data-testid="admin-email-input"
              />
            </div>

            <div>
              <Label htmlFor="password">Contraseña</Label>
              <Input
                id="password"
                name="password"
                type="password"
                value={formData.password}
                onChange={handleChange}
                required
                className="mt-1 rounded-sm"
                data-testid="admin-password-input"
              />
            </div>

            <Button
              type="submit"
              disabled={loading}
              className="w-full btn-primary"
              data-testid="admin-submit-button"
            >
              {loading
                ? "Cargando..."
                : isRegister
                ? "Crear Cuenta"
                : "Ingresar"}
            </Button>
          </form>

          <div className="mt-6 text-center">
            <button
              type="button"
              onClick={() => setIsRegister(!isRegister)}
              className="text-sm text-gray-600 hover:text-[hsl(var(--primary))] transition-colors"
              data-testid="toggle-auth-mode"
            >
              {isRegister
                ? "¿Ya tienes cuenta? Ingresar"
                : "¿No tienes cuenta? Registrarse"}
            </button>
          </div>
        </div>
      </motion.div>
    </div>
  );
}
