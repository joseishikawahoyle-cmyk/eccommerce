import { useEffect, useState, useContext } from "react";
import axios from "axios";
import { Check, X, Eye, Clock, Filter } from "lucide-react";
import { motion } from "framer-motion";
import { AdminLayout } from "@/components/layout/AdminLayout";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { AuthContext, API } from "@/App";
import { toast } from "sonner";

export default function AdminOrders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [filter, setFilter] = useState("all");
  const { token } = useContext(AuthContext);

  const headers = { Authorization: `Bearer ${token}` };

  const fetchOrders = async () => {
    try {
      const params = filter !== "all" ? `?status=${filter}` : "";
      const response = await axios.get(`${API}/admin/orders${params}`, {
        headers,
      });
      setOrders(response.data);
    } catch (e) {
      console.error("Error fetching orders:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOrders();
  }, [token, filter]);

  const handleValidate = async (orderId, action) => {
    try {
      await axios.put(
        `${API}/admin/orders/${orderId}/validate?action=${action}`,
        {},
        { headers }
      );
      toast.success(
        action === "approve" ? "Pedido confirmado" : "Pedido rechazado"
      );
      fetchOrders();
      setSelectedOrder(null);
    } catch (error) {
      toast.error("Error al procesar");
    }
  };

  const getStatusBadge = (status) => {
    const statuses = {
      pending_payment: {
        label: "Pendiente Pago",
        class: "bg-yellow-100 text-yellow-800",
      },
      pending_validation: {
        label: "En Validación",
        class: "bg-blue-100 text-blue-800",
      },
      confirmed: {
        label: "Confirmado",
        class: "bg-green-100 text-green-800",
      },
      rejected: {
        label: "Rechazado",
        class: "bg-red-100 text-red-800",
      },
    };
    return statuses[status] || statuses.pending_payment;
  };

  const getImageUrl = (path) => {
    if (!path) return null;
    return path.startsWith("http") ? path : `${API}/files/${path}`;
  };

  return (
    <AdminLayout>
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold tracking-tight" data-testid="admin-orders-title">
            Pedidos
          </h1>
          <p className="text-gray-600">Gestiona y valida los pedidos</p>
        </div>
        <Select value={filter} onValueChange={setFilter}>
          <SelectTrigger className="w-48 rounded-sm" data-testid="order-filter">
            <Filter className="w-4 h-4 mr-2" />
            <SelectValue placeholder="Filtrar" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Todos</SelectItem>
            <SelectItem value="pending_validation">En Validación</SelectItem>
            <SelectItem value="pending_payment">Pendiente Pago</SelectItem>
            <SelectItem value="confirmed">Confirmados</SelectItem>
            <SelectItem value="rejected">Rechazados</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {loading ? (
        <div className="animate-pulse space-y-4">
          {[...Array(5)].map((_, i) => (
            <div key={i} className="h-20 bg-gray-200 rounded-sm" />
          ))}
        </div>
      ) : orders.length === 0 ? (
        <div className="text-center py-16 bg-white rounded-sm border border-gray-100">
          <Clock className="w-12 h-12 mx-auto text-gray-300 mb-4" />
          <p className="text-gray-500">No hay pedidos</p>
        </div>
      ) : (
        <div className="bg-white rounded-sm border border-gray-100 overflow-hidden">
          <div className="overflow-x-auto">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Productos</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {orders.map((order, index) => {
                  const status = getStatusBadge(order.status);
                  return (
                    <motion.tr
                      key={order.id}
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      transition={{ delay: index * 0.05 }}
                    >
                      <td className="font-mono text-sm">
                        #{order.id.slice(0, 8)}
                      </td>
                      <td>
                        <p className="font-medium">{order.customer_name}</p>
                        <p className="text-xs text-gray-500">
                          {order.customer_email}
                        </p>
                      </td>
                      <td>{order.items.length} items</td>
                      <td className="font-medium">
                        S/ {order.total.toFixed(2)}
                      </td>
                      <td>
                        <span
                          className={`px-2 py-1 rounded-sm text-xs font-medium ${status.class}`}
                        >
                          {status.label}
                        </span>
                      </td>
                      <td className="text-sm text-gray-500">
                        {new Date(order.created_at).toLocaleDateString("es-PE")}
                      </td>
                      <td>
                        <div className="flex gap-2">
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setSelectedOrder(order)}
                            className="rounded-sm"
                            data-testid={`view-order-${order.id}`}
                          >
                            <Eye className="w-4 h-4" />
                          </Button>
                          {order.status === "pending_validation" && (
                            <>
                              <Button
                                size="sm"
                                onClick={() => handleValidate(order.id, "approve")}
                                className="bg-green-600 hover:bg-green-700 rounded-sm"
                                data-testid={`approve-order-${order.id}`}
                              >
                                <Check className="w-4 h-4" />
                              </Button>
                              <Button
                                size="sm"
                                variant="destructive"
                                onClick={() => handleValidate(order.id, "reject")}
                                className="rounded-sm"
                                data-testid={`reject-order-${order.id}`}
                              >
                                <X className="w-4 h-4" />
                              </Button>
                            </>
                          )}
                        </div>
                      </td>
                    </motion.tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Order Detail Dialog */}
      <Dialog open={!!selectedOrder} onOpenChange={() => setSelectedOrder(null)}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              Pedido #{selectedOrder?.id.slice(0, 8)}
            </DialogTitle>
          </DialogHeader>

          {selectedOrder && (
            <div className="space-y-6">
              {/* Status */}
              <div className="flex items-center gap-2">
                <span className="text-sm text-gray-500">Estado:</span>
                <span
                  className={`px-2 py-1 rounded-sm text-xs font-medium ${
                    getStatusBadge(selectedOrder.status).class
                  }`}
                >
                  {getStatusBadge(selectedOrder.status).label}
                </span>
              </div>

              {/* Customer Info */}
              <div className="bg-stone-50 p-4 rounded-sm">
                <h3 className="font-medium mb-2">Cliente</h3>
                <div className="text-sm space-y-1">
                  <p>
                    <span className="text-gray-500">Nombre:</span>{" "}
                    {selectedOrder.customer_name}
                  </p>
                  <p>
                    <span className="text-gray-500">Email:</span>{" "}
                    {selectedOrder.customer_email}
                  </p>
                  <p>
                    <span className="text-gray-500">Teléfono:</span>{" "}
                    {selectedOrder.customer_phone}
                  </p>
                  <p>
                    <span className="text-gray-500">Dirección:</span>{" "}
                    {selectedOrder.shipping_address}
                  </p>
                </div>
              </div>

              {/* Products */}
              <div>
                <h3 className="font-medium mb-2">Productos</h3>
                <div className="space-y-2">
                  {selectedOrder.items.map((item, index) => (
                    <div
                      key={index}
                      className="flex justify-between text-sm p-2 bg-gray-50 rounded-sm"
                    >
                      <div>
                        <p className="font-medium">{item.product_name}</p>
                        <p className="text-gray-500">
                          {item.quantity} x S/ {item.unit_price.toFixed(2)}
                        </p>
                      </div>
                      <p className="font-medium">S/ {item.total.toFixed(2)}</p>
                    </div>
                  ))}
                  <div className="flex justify-between pt-2 border-t font-bold">
                    <span>Total</span>
                    <span>S/ {selectedOrder.total.toFixed(2)}</span>
                  </div>
                </div>
              </div>

              {/* Voucher */}
              {selectedOrder.voucher_url && (
                <div>
                  <h3 className="font-medium mb-2">Comprobante de Pago</h3>
                  <div className="bg-gray-100 rounded-sm overflow-hidden">
                    <img
                      src={getImageUrl(selectedOrder.voucher_url)}
                      alt="Comprobante"
                      className="w-full max-h-96 object-contain"
                      data-testid="order-voucher-image"
                    />
                  </div>
                  {selectedOrder.voucher_uploaded_at && (
                    <p className="text-xs text-gray-500 mt-2">
                      Subido el{" "}
                      {new Date(selectedOrder.voucher_uploaded_at).toLocaleString(
                        "es-PE"
                      )}
                    </p>
                  )}
                </div>
              )}

              {/* Actions */}
              {selectedOrder.status === "pending_validation" && (
                <div className="flex gap-4 pt-4 border-t">
                  <Button
                    onClick={() => handleValidate(selectedOrder.id, "reject")}
                    variant="outline"
                    className="flex-1 rounded-sm text-red-600 hover:text-red-700"
                  >
                    <X className="w-4 h-4 mr-2" />
                    Rechazar
                  </Button>
                  <Button
                    onClick={() => handleValidate(selectedOrder.id, "approve")}
                    className="flex-1 bg-green-600 hover:bg-green-700 rounded-sm"
                    data-testid="confirm-order-button"
                  >
                    <Check className="w-4 h-4 mr-2" />
                    Confirmar Pago
                  </Button>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </AdminLayout>
  );
}
