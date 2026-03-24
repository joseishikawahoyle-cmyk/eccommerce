import { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import { Package, Clock, Check, X, Upload, ArrowLeft } from "lucide-react";
import { motion } from "framer-motion";
import { Navbar } from "@/components/layout/Navbar";
import { Footer } from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { API } from "@/App";
import { toast } from "sonner";

export default function OrderStatusPage() {
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);

  const fetchOrder = async () => {
    try {
      const response = await axios.get(`${API}/orders/${id}`);
      setOrder(response.data);
    } catch (e) {
      console.error("Error fetching order:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOrder();
    const interval = setInterval(fetchOrder, 30000);
    return () => clearInterval(interval);
  }, [id]);

  const handleVoucherUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setUploading(true);
    const formData = new FormData();
    formData.append("file", file);

    try {
      await axios.post(`${API}/orders/${id}/voucher`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      toast.success("Comprobante subido exitosamente");
      fetchOrder();
    } catch (error) {
      toast.error("Error al subir el comprobante");
    } finally {
      setUploading(false);
    }
  };

  const getStatusInfo = (status) => {
    const statuses = {
      pending_payment: {
        label: "Pendiente de Pago",
        color: "text-yellow-600 bg-yellow-100",
        icon: Clock,
        description: "Esperando tu comprobante de pago",
      },
      pending_validation: {
        label: "En Validación",
        color: "text-blue-600 bg-blue-100",
        icon: Clock,
        description: "Tu comprobante está siendo revisado",
      },
      confirmed: {
        label: "Confirmado",
        color: "text-green-600 bg-green-100",
        icon: Check,
        description: "Tu pago ha sido confirmado",
      },
      rejected: {
        label: "Rechazado",
        color: "text-red-600 bg-red-100",
        icon: X,
        description: "Hubo un problema con tu pago",
      },
      shipped: {
        label: "Enviado",
        color: "text-purple-600 bg-purple-100",
        icon: Package,
        description: "Tu pedido está en camino",
      },
      delivered: {
        label: "Entregado",
        color: "text-green-600 bg-green-100",
        icon: Check,
        description: "Pedido entregado exitosamente",
      },
    };
    return statuses[status] || statuses.pending_payment;
  };

  if (loading) {
    return (
      <div className="min-h-screen flex flex-col">
        <Navbar />
        <main className="flex-1 py-12 px-6 md:px-12 max-w-3xl mx-auto">
          <div className="animate-pulse space-y-4">
            <div className="h-8 bg-gray-200 rounded w-1/2" />
            <div className="h-32 bg-gray-200 rounded" />
            <div className="h-48 bg-gray-200 rounded" />
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  if (!order) {
    return (
      <div className="min-h-screen flex flex-col">
        <Navbar />
        <main className="flex-1 py-12 px-6 md:px-12 max-w-3xl mx-auto text-center">
          <h1 className="text-2xl font-bold mb-4">Pedido no encontrado</h1>
          <Link to="/" className="text-[hsl(var(--primary))] hover:underline">
            Volver al inicio
          </Link>
        </main>
        <Footer />
      </div>
    );
  }

  const statusInfo = getStatusInfo(order.status);
  const StatusIcon = statusInfo.icon;

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      <main className="flex-1 py-8 md:py-12 px-6 md:px-12 max-w-3xl mx-auto w-full">
        <Link
          to="/"
          className="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[hsl(var(--primary))] mb-8 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          Volver al inicio
        </Link>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          {/* Header */}
          <div className="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
              <span className="label-caps text-gray-500 mb-1 block">
                Pedido #{order.id.slice(0, 8)}
              </span>
              <h1 className="text-2xl md:text-3xl font-bold tracking-tight">
                Estado del Pedido
              </h1>
            </div>
            <div
              className={`inline-flex items-center gap-2 px-4 py-2 rounded-sm ${statusInfo.color}`}
              data-testid="order-status-badge"
            >
              <StatusIcon className="w-4 h-4" />
              <span className="font-medium">{statusInfo.label}</span>
            </div>
          </div>

          {/* Status Description */}
          <div className="bg-stone-50 p-6 rounded-sm mb-8">
            <p className="text-gray-600">{statusInfo.description}</p>
            {order.status === "pending_payment" && (
              <div className="mt-4">
                <label className="dropzone block cursor-pointer">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleVoucherUpload}
                    className="hidden"
                    disabled={uploading}
                  />
                  <Upload className="w-6 h-6 mx-auto mb-2 text-gray-400" />
                  <p className="font-medium">
                    {uploading ? "Subiendo..." : "Subir Comprobante de Pago"}
                  </p>
                  <p className="text-sm text-gray-500 mt-1">
                    Haz clic para subir tu captura
                  </p>
                </label>
              </div>
            )}
          </div>

          {/* Order Details */}
          <div className="border border-gray-100 rounded-sm overflow-hidden">
            <div className="bg-white p-6 border-b border-gray-100">
              <h2 className="font-bold mb-4">Productos</h2>
              <div className="space-y-4">
                {order.items.map((item, index) => (
                  <div key={index} className="flex justify-between text-sm">
                    <div>
                      <p className="font-medium">{item.product_name}</p>
                      <p className="text-gray-500">Cantidad: {item.quantity}</p>
                    </div>
                    <p className="font-medium">S/ {item.total.toFixed(2)}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-white p-6 border-b border-gray-100">
              <h2 className="font-bold mb-4">Información de Envío</h2>
              <div className="text-sm space-y-2">
                <p>
                  <span className="text-gray-500">Nombre:</span>{" "}
                  {order.customer_name}
                </p>
                <p>
                  <span className="text-gray-500">Email:</span>{" "}
                  {order.customer_email}
                </p>
                <p>
                  <span className="text-gray-500">Teléfono:</span>{" "}
                  {order.customer_phone}
                </p>
                <p>
                  <span className="text-gray-500">Dirección:</span>{" "}
                  {order.shipping_address}
                </p>
              </div>
            </div>

            <div className="bg-stone-50 p-6">
              <div className="flex justify-between items-center">
                <span className="font-bold">Total</span>
                <span className="text-xl font-bold" data-testid="order-total">
                  S/ {order.total.toFixed(2)}
                </span>
              </div>
            </div>
          </div>

          {/* Timeline */}
          <div className="mt-8">
            <h2 className="font-bold mb-4">Historial</h2>
            <div className="space-y-4">
              <div className="flex gap-4">
                <div className="w-3 h-3 mt-1.5 rounded-full bg-[hsl(var(--primary))]" />
                <div>
                  <p className="font-medium">Pedido creado</p>
                  <p className="text-sm text-gray-500">
                    {new Date(order.created_at).toLocaleString("es-PE")}
                  </p>
                </div>
              </div>
              {order.voucher_uploaded_at && (
                <div className="flex gap-4">
                  <div className="w-3 h-3 mt-1.5 rounded-full bg-blue-500" />
                  <div>
                    <p className="font-medium">Comprobante subido</p>
                    <p className="text-sm text-gray-500">
                      {new Date(order.voucher_uploaded_at).toLocaleString("es-PE")}
                    </p>
                  </div>
                </div>
              )}
              {order.validated_at && (
                <div className="flex gap-4">
                  <div
                    className={`w-3 h-3 mt-1.5 rounded-full ${
                      order.status === "confirmed" ? "bg-green-500" : "bg-red-500"
                    }`}
                  />
                  <div>
                    <p className="font-medium">
                      {order.status === "confirmed"
                        ? "Pago confirmado"
                        : "Pago rechazado"}
                    </p>
                    <p className="text-sm text-gray-500">
                      {new Date(order.validated_at).toLocaleString("es-PE")}
                    </p>
                  </div>
                </div>
              )}
            </div>
          </div>
        </motion.div>
      </main>

      <Footer />
    </div>
  );
}
