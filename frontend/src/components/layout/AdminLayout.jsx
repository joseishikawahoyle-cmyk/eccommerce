import { useContext } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Palette,
  Image,
  LogOut,
} from "lucide-react";
import { AuthContext, BrandContext } from "@/App";

export const AdminLayout = ({ children }) => {
  const { brand } = useContext(BrandContext);
  const { user, logout } = useContext(AuthContext);
  const location = useLocation();
  const navigate = useNavigate();

  const navItems = [
    { href: "/admin", label: "Dashboard", icon: LayoutDashboard },
    { href: "/admin/productos", label: "Productos", icon: Package },
    { href: "/admin/pedidos", label: "Pedidos", icon: ShoppingCart },
    { href: "/admin/banners", label: "Banners", icon: Image },
    { href: "/admin/marca", label: "Marca", icon: Palette },
  ];

  const handleLogout = () => {
    logout();
    navigate("/admin/login");
  };

  return (
    <div className="flex min-h-screen bg-gray-50">
      {/* Sidebar */}
      <aside className="admin-sidebar fixed left-0 top-0 h-screen w-64">
        <div className="p-6 border-b border-white/10">
          <h1 className="text-lg font-bold tracking-tight">{brand.store_name}</h1>
          <p className="text-xs text-gray-400 mt-1">Panel de Administración</p>
        </div>

        <nav className="py-4">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = location.pathname === item.href;
            return (
              <Link
                key={item.href}
                to={item.href}
                className={`admin-nav-item ${isActive ? "active" : ""}`}
                data-testid={`admin-nav-${item.label.toLowerCase()}`}
              >
                <Icon className="w-5 h-5" />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>

        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
          <div className="flex items-center justify-between">
            <div className="text-sm">
              <p className="text-white font-medium">{user?.name}</p>
              <p className="text-gray-400 text-xs">{user?.email}</p>
            </div>
            <button
              onClick={handleLogout}
              className="p-2 hover:bg-white/10 rounded-sm transition-colors"
              data-testid="admin-logout-button"
            >
              <LogOut className="w-5 h-5 text-gray-400" />
            </button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 ml-64">
        <div className="p-8">{children}</div>
      </main>
    </div>
  );
};

export default AdminLayout;
