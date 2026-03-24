import { useState, useContext, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import { QRCodeSVG } from "qrcode.react";
import { Upload, Check, AlertCircle, ArrowLeft } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { Navbar } from "@/components/layout/Navbar";
import { Footer } from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { CartContext, BrandContext, API } from "@/App";
import { toast } from "sonner";

export default function CheckoutPage() {
  const { cart, getTotal, clearCart } = useContext(CartContext);
  const { brand } = useContext(BrandContext);
  const navigate = useNavigate();

  const [step, setStep] = useState(1);
  const [loading, setLoading] = useState(false);
  const [orderId, setOrderId] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [voucherUploaded, setVoucherUploaded] = useState(false);

  const [formData, setFormData] = useState({
    customer_name: "",
    customer_email: "",
    customer_phone: "",
    shipping_address: "",
    payment_method: "yape",
  });

  useEffect(() => {
    if (cart.length === 0 && !orderId) {
      navigate("/carrito");
    }
  }, [cart, orderId, navigate]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmitOrder = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const orderData = {
        items: cart.map((item) => ({
          product_id: item.id,
          quantity: item.quantity,
        })),
        ...formData,
      };

      const response = await axios.post(`${API}/orders`, orderData);
      setOrderId(response.data.id);
      setStep(2);
      toast.success("Pedido creado. Ahora realiza el pago.");
    } catch (error) {
      console.error("Error creating order:", error);
      toast.error(
        error.response?.data?.detail || "Error al crear el pedido"
      );
    } finally {
      setLoading(false);
    }
  };

  const handleVoucherUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setUploading(true);
    const formDataUpload = new FormData();
    formDataUpload.append("file", file);

    try {
      await axios.post(`${API}/orders/${orderId}/voucher`, formDataUpload, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      setVoucherUploaded(true);
      clearCart();
      setStep(3);
      toast.success("Comprobante subido exitosamente");
    } catch (error) {
      console.error("Error uploading voucher:", error);
      toast.error("Error al subir el comprobante");
    } finally {
      setUploading(false);
    }
  };

  const yapeNumber = brand.yape_number || "999888777";
  const plinNumber = brand.plin_number || "999888777";

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      <main className="flex-1 py-8 md:py-12 px-6 md:px-12 max-w-4xl mx-auto w-full">
        {/* Progress Steps */}
        <div className="flex items-center justify-center gap-4 mb-12">
          {[1, 2, 3].map((s) => (
            <div key={s} className="flex items-center gap-2">
              <div
                className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${
                  step >= s
                    ? "bg-[hsl(var(--primary))] text-white"
                    : "bg-gray-200 text-gray-500"
                }`}
              >
                {step > s ? <Check className="w-4 h-4" /> : s}
              </div>
              <span
                className={`hidden sm:block text-sm ${
                  step >= s ? "text-[hsl(var(--primary))]" : "text-gray-400"
                }`}
              >
                {s === 1 ? "Datos" : s === 2 ? "Pago" : "Confirmación"}
              </span>
              {s < 3 && <div className="w-8 h-px bg-gray-200" />}
            </div>
          ))}
        </div>

        <AnimatePresence mode="wait">
          {/* Step 1: Customer Data */}
          {step === 1 && (
            <motion.div
              key="step1"
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
            >
              <h1 className="text-2xl md:text-3xl font-bold tracking-tight mb-8">
                Información de Envío
              </h1>

              <form onSubmit={handleSubmitOrder} className="space-y-6">
                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="customer_name">Nombre Completo</Label>
                    <Input
                      id="customer_name"
                      name="customer_name"
                      value={formData.customer_name}
                      onChange={handleChange}
                      required
                      className="mt-1 rounded-sm"
                      data-testid="customer-name-input"
                    />
                  </div>
                  <div>
                    <Label htmlFor="customer_email">Correo Electrónico</Label>
                    <Input
                      id="customer_email"
                      name="customer_email"
                      type="email"
                      value={formData.customer_email}
                      onChange={handleChange}
                      required
                      className="mt-1 rounded-sm"
                      data-testid="customer-email-input"
                    />
                  </div>
                </div>

                <div>
                  <Label htmlFor="customer_phone">Teléfono / WhatsApp</Label>
                  <Input
                    id="customer_phone"
                    name="customer_phone"
                    value={formData.customer_phone}
                    onChange={handleChange}
                    required
                    className="mt-1 rounded-sm"
                    placeholder="987654321"
                    data-testid="customer-phone-input"
                  />
                </div>

                <div>
                  <Label htmlFor="shipping_address">Dirección de Envío</Label>
                  <Textarea
                    id="shipping_address"
                    name="shipping_address"
                    value={formData.shipping_address}
                    onChange={handleChange}
                    required
                    className="mt-1 rounded-sm"
                    rows={3}
                    placeholder="Av. Principal 123, Distrito, Ciudad"
                    data-testid="shipping-address-input"
                  />
                </div>

                <div>
                  <Label className="mb-3 block">Método de Pago</Label>
                  <RadioGroup
                    value={formData.payment_method}
                    onValueChange={(value) =>
                      setFormData({ ...formData, payment_method: value })
                    }
                    className="grid grid-cols-2 gap-4"
                  >
                    <div>
                      <RadioGroupItem
                        value="yape"
                        id="yape"
                        className="peer sr-only"
                      />
                      <Label
                        htmlFor="yape"
                        className="flex flex-col items-center justify-center p-4 border-2 rounded-sm cursor-pointer peer-data-[state=checked]:border-[hsl(var(--primary))] peer-data-[state=checked]:bg-stone-50 hover:bg-gray-50 transition-colors"
                        data-testid="payment-yape"
                      >
                        <span className="text-2xl mb-1">💜</span>
                        <span className="font-medium">Yape</span>
                      </Label>
                    </div>
                    <div>
                      <RadioGroupItem
                        value="plin"
                        id="plin"
                        className="peer sr-only"
                      />
                      <Label
                        htmlFor="plin"
                        className="flex flex-col items-center justify-center p-4 border-2 rounded-sm cursor-pointer peer-data-[state=checked]:border-[hsl(var(--primary))] peer-data-[state=checked]:bg-stone-50 hover:bg-gray-50 transition-colors"
                        data-testid="payment-plin"
                      >
                        <span className="text-2xl mb-1">💚</span>
                        <span className="font-medium">Plin</span>
                      </Label>
                    </div>
                  </RadioGroup>
                </div>

                {/* Order Summary */}
                <div className="bg-stone-50 p-6 rounded-sm">
                  <h3 className="font-bold mb-4">Resumen del Pedido</h3>
                  <div className="space-y-2 text-sm">
                    {cart.map((item) => (
                      <div key={item.id} className="flex justify-between">
                        <span>
                          {item.name} x {item.quantity}
                        </span>
                        <span>
                          S/ {((item.current_price || item.price) * item.quantity).toFixed(2)}
                        </span>
                      </div>
                    ))}
                    <div className="border-t pt-2 mt-2 flex justify-between font-bold">
                      <span>Total</span>
                      <span>S/ {getTotal().toFixed(2)}</span>
                    </div>
                  </div>
                </div>

                <div className="flex gap-4">
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => navigate("/carrito")}
                    className="flex-1 rounded-sm"
                  >
                    <ArrowLeft className="w-4 h-4 mr-2" />
                    Volver
                  </Button>
                  <Button
                    type="submit"
                    disabled={loading}
                    className="flex-1 btn-primary"
                    data-testid="submit-order-button"
                  >
                    {loading ? "Procesando..." : "Continuar al Pago"}
                  </Button>
                </div>
              </form>
            </motion.div>
          )}

          {/* Step 2: Payment */}
          {step === 2 && (
            <motion.div
              key="step2"
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
              className="text-center"
            >
              <h1 className="text-2xl md:text-3xl font-bold tracking-tight mb-4">
                Realiza el Pago
              </h1>
              <p className="text-gray-600 mb-8">
                Escanea el código QR o transfiere al número indicado
              </p>

              <div className="qr-container max-w-sm mx-auto mb-8">
                <div className="mb-6">
                  <span className="label-caps text-gray-500 mb-2 block">
                    {formData.payment_method === "yape" ? "Yape" : "Plin"}
                  </span>
                  <p className="text-2xl font-bold">
                    {formData.payment_method === "yape" ? yapeNumber : plinNumber}
                  </p>
                </div>

                <div className="bg-white p-4 inline-block rounded-sm border">
                  <QRCodeSVG
                    value={`tel:${
                      formData.payment_method === "yape" ? yapeNumber : plinNumber
                    }`}
                    size={180}
                    data-testid="payment-qr-code"
                  />
                </div>

                <div className="mt-6 p-4 bg-yellow-50 rounded-sm text-left">
                  <div className="flex gap-2">
                    <AlertCircle className="w-5 h-5 text-yellow-600 flex-shrink-0" />
                    <div className="text-sm">
                      <p className="font-medium text-yellow-800 mb-1">
                        Total a pagar: S/ {getTotal().toFixed(2)}
                      </p>
                      <p className="text-yellow-700">
                        Después de realizar el pago, sube la captura del comprobante
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Upload Voucher */}
              <div className="max-w-sm mx-auto">
                <label className="dropzone block" data-testid="voucher-dropzone">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleVoucherUpload}
                    className="hidden"
                    disabled={uploading}
                  />
                  <Upload className="w-8 h-8 mx-auto mb-3 text-gray-400" />
                  <p className="font-medium">
                    {uploading ? "Subiendo..." : "Subir Comprobante de Pago"}
                  </p>
                  <p className="text-sm text-gray-500 mt-1">
                    Haz clic o arrastra tu captura aquí
                  </p>
                </label>
              </div>
            </motion.div>
          )}

          {/* Step 3: Confirmation */}
          {step === 3 && (
            <motion.div
              key="step3"
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              className="text-center py-12"
            >
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <Check className="w-8 h-8 text-green-600" />
              </div>

              <h1 className="text-2xl md:text-3xl font-bold tracking-tight mb-4">
                ¡Pedido Recibido!
              </h1>
              <p className="text-gray-600 mb-2">
                Tu comprobante ha sido enviado para validación
              </p>
              <p className="text-sm text-gray-500 mb-8">
                Número de pedido:{" "}
                <span className="font-mono font-medium">{orderId?.slice(0, 8)}</span>
              </p>

              <div className="bg-stone-50 p-6 rounded-sm max-w-md mx-auto mb-8">
                <h3 className="font-medium mb-2">¿Qué sigue?</h3>
                <ol className="text-sm text-gray-600 text-left space-y-2">
                  <li>1. Validaremos tu comprobante de pago</li>
                  <li>2. Te notificaremos por correo cuando sea confirmado</li>
                  <li>3. Prepararemos tu pedido para envío</li>
                </ol>
              </div>

              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Button
                  variant="outline"
                  onClick={() => navigate(`/pedido/${orderId}`)}
                  className="rounded-sm"
                  data-testid="view-order-status"
                >
                  Ver Estado del Pedido
                </Button>
                <Button
                  onClick={() => navigate("/")}
                  className="btn-primary"
                  data-testid="continue-shopping"
                >
                  Seguir Comprando
                </Button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </main>

      <Footer />
    </div>
  );
}
