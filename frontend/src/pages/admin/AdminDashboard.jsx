import { useEffect, useState, useContext } from "react";
import axios from "axios";
import { Package, ShoppingCart, DollarSign, Clock } from "lucide-react";
import { motion } from "framer-motion";
import { AdminLayout } from "@/components/layout/AdminLayout";
import { AuthContext, API } from "@/App";

export default function AdminDashboard() {
  const [stats, setStats] = useState(null);
  const [recentOrders, setRecentOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const { token } = useContext(AuthContext);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const headers = { Authorization: `Bearer ${token}` };
        const [statsRes, ordersRes] = await Promise.all([
          axios.get(`${API}/admin/stats`, { headers }),
          axios.get(`${API}/admin/orders`, { headers }),
        ]);
        setStats(statsRes.data);
        setRecentOrders(ordersRes.data.slice(0, 5));
      } catch (e) {
        console.error("Error fetching dashboard data:", e);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [token]);

  const statCards = [
    {
      title: "Productos",
      value: stats?.total_products || 0,
      icon: Package,
      color: "bg-blue-100 text-blue-600",
    },
    {
      title: "Pedidos Totales",
      value: stats?.total_orders || 0,
      icon: ShoppingCart,
      color: "bg-green-100 text-green-600",
    },
    {
      title: "Pendientes",
      value: stats?.pending_orders || 0,
      icon: Clock,
      color: "bg-yellow-100 text-yellow-600",
    },
    {
      title: "Ingresos",
      value: `S/ ${(stats?.total_revenue || 0).toFixed(2)}`,
      icon: DollarSign,
      color: "bg-purple-100 text-purple-600",
    },
  ];

  const getStatusBadge = (status) => {
    const statuses = {
      pending_payment: { label: "Pendiente Pago", class: "status-pending" },
      pending_validation: { label: "En Validación", class: "bg-blue-100 text-blue-800" },
      confirmed: { label: "Confirmado", class: "status-confirmed" },
      rejected: { label: "Rechazado", class: "status-rejected" },
    };
    return statuses[status] || statuses.pending_payment;
  };

  return (
    <AdminLayout>
      <div className="mb-8">
        <h1 className="text-2xl font-bold tracking-tight" data-testid="admin-dashboard-title">
          Dashboard
        </h1>
        <p className="text-gray-600">Resumen general de tu tienda</p>
      </div>

      {loading ? (
        <div className="grid md:grid-cols-4 gap-6">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="animate-pulse bg-gray-200 h-32 rounded-sm" />
          ))}
        </div>
      ) : (
        <>
          {/* Stats Grid */}
          <div className="grid md:grid-cols-4 gap-6 mb-8">
            {statCards.map((stat, index) => {
              const Icon = stat.icon;
              return (
                <motion.div
                  key={stat.title}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.1 }}
                  className="bg-white p-6 rounded-sm border border-gray-100"
                  data-testid={`stat-${stat.title.toLowerCase().replace(" ", "-")}`}
                >
                  <div className="flex items-center justify-between mb-4">
                    <div className={`p-2 rounded-sm ${stat.color}`}>
                      <Icon className="w-5 h-5" />
                    </div>
                  </div>
                  <p className="text-2xl font-bold">{stat.value}</p>
                  <p className="text-sm text-gray-600">{stat.title}</p>
                </motion.div>
              );
            })}
          </div>

          {/* Recent Orders */}
          <div className="bg-white rounded-sm border border-gray-100">
            <div className="p-6 border-b border-gray-100">
              <h2 className="font-bold">Pedidos Recientes</h2>
            </div>
            <div className="overflow-x-auto">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  {recentOrders.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="text-center text-gray-500 py-8">
                        No hay pedidos aún
                      </td>
                    </tr>
                  ) : (
                    recentOrders.map((order) => {
                      const status = getStatusBadge(order.status);
                      return (
                        <tr key={order.id}>
                          <td className="font-mono text-sm">
                            #{order.id.slice(0, 8)}
                          </td>
                          <td>{order.customer_name}</td>
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
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}
    </AdminLayout>
  );
}
