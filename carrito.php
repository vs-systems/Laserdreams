<?php
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12">

    <div class="mb-10 text-center md:text-left">
        <h1 class="text-3xl font-black text-gray-900 mb-2">Tu Carrito de Consultas</h1>
        <p class="text-gray-500 font-medium">Revisa tus productos antes de enviar la consulta por WhatsApp.</p>
    </div>

    <div id="carrito-contenido" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <!-- El contenido se carga dinámicamente vía JavaScript -->
    </div>

    <div id="carrito-acciones" class="hidden">
        <div class="bg-gray-50 p-8 rounded-3xl border border-gray-100 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start gap-8">

                <!-- Opciones (Izquierda) -->
                <div class="flex-grow">
                    <div class="mb-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" id="chk-requiere-factura"
                                class="w-5 h-5 rounded border-gray-300 text-violet-600 focus:ring-violet-500 transition-colors cursor-pointer">
                            <span
                                class="text-sm font-bold text-gray-700 group-hover:text-violet-600 transition-colors">Requiere
                                Facturación (+21% IVA)</span>
                        </label>
                    </div>

                    <div
                        class="space-y-1 text-sm font-bold text-gray-500 uppercase tracking-widest mt-2 border-t pt-4 max-w-sm">
                        <p id="txt-incluye-iva">* Los valores no incluyen IVA.</p>
                        <p class="text-xs text-orange-500">⚠️ Los envíos corren por cuenta y orden del cliente</p>
                    </div>
                </div>

                <!-- Totales y Acción (Derecha) -->
                <div
                    class="w-full md:w-auto md:min-w-[300px] flex flex-col md:items-end bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-full text-right mb-4">
                        <div id="carrito-total" class="text-xl font-bold text-gray-700 mb-2"></div>
                        <div id="carrito-iva-info"
                            class="text-lg font-bold text-gray-500 mb-2 hidden flex flex-col items-end">
                            <span>+ IVA 21%: <span id="carrito-iva-monto" class="text-gray-900"></span></span>
                            <div class="w-full h-px bg-gray-100 my-2"></div>
                            <span class="text-3xl font-black text-gray-900">Total: <span id="carrito-total-iva"
                                    class="text-violet-600"></span></span>
                        </div>
                    </div>

                    <button id="btn-abrir-checkout"
                        class="w-full inline-flex items-center justify-center gap-3 bg-[#25D366] text-white px-8 py-4 rounded-2xl font-bold text-lg hover:bg-[#20bd5a] transition-all transform active:scale-95 shadow-lg shadow-green-200">
                        <span>💬</span> Consultar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="flex justify-between items-center px-4">
        <a href="/catalogo.php"
            class="text-gray-500 font-bold hover:text-violet-600 transition-colors flex items-center gap-2">
            <span>←</span> Volver al catálogo
        </a>
    </div>

</div>

<!-- Modal de Checkout -->
<div id="modal-checkout"
    class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300 overflow-y-auto">
    <div
        class="bg-white w-full max-w-2xl my-8 rounded-[40px] shadow-2xl overflow-hidden transform animate-in slide-in-from-bottom-8 duration-500">
        <div class="bg-gray-900 p-8 text-white relative">
            <button onclick="cerrarCheckout()"
                class="absolute top-6 right-6 text-gray-400 hover:text-white transition-colors">✕</button>
            <h2 class="text-2xl font-black tracking-tight mb-2">Último paso</h2>
            <p class="text-gray-400 text-sm font-medium">Completa tus datos para enviarte la cotización oficial.</p>
            <p id="txt-modal-facturacion"
                class="text-violet-300 text-xs font-bold mt-2 uppercase tracking-widest hidden">🗒️ Requiere Facturación
            </p>
        </div>

        <form id="form-checkout" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Nombre
                        Completo</label>
                    <input type="text" id="checkout-nombre" required placeholder="Ej: Juan Pérez"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Localidad /
                        Ciudad</label>
                    <input type="text" id="checkout-localidad" required placeholder="Ej: Mar del Plata"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
                </div>
            </div>

            <div id="campos-facturacion" class="hidden pt-4 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 lg:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Razón Social</label>
                        <input type="text" id="checkout-razon-social" placeholder="Ej: Empresa S.A."
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">CUIT</label>
                        <input type="text" id="checkout-cuit" placeholder="Ej: 30-12345678-9"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Email Facturación</label>
                        <input type="email" id="checkout-email" placeholder="Ej: admin@empresa.com"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
                    </div>
                    <div class="space-y-2 lg:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Tipo de Factura</label>
                        <select id="checkout-tipo-factura"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900 appearance-none cursor-pointer">
                            <option value="A">Factura A</option>
                            <option value="B">Factura B</option>
                            <option value="C">Factura C</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" id="btn-finalizar"
                class="w-full bg-violet-500 text-black py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-900 hover:text-white transition-all shadow-xl shadow-violet-500/20 active:scale-95 flex items-center justify-center gap-3 mt-4">
                <span>💬</span> Confirmar y enviar WhatsApp
            </button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-checkout');
    const form = document.getElementById('form-checkout');

    function abrirCheckout() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarCheckout() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('btn-abrir-checkout').addEventListener('click', abrirCheckout);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btn-finalizar');
        const nombre = document.getElementById('checkout-nombre').value.trim();
        const localidad = document.getElementById('checkout-localidad').value.trim();
        const carritoData = window.MGCarrito.get();

        const chkFactura = document.getElementById('chk-requiere-factura').checked;
        const cuit = document.getElementById('checkout-cuit').value.trim();
        const razonSocial = document.getElementById('checkout-razon-social').value.trim();
        const emailFactura = document.getElementById('checkout-email').value.trim();
        const tipoFactura = document.getElementById('checkout-tipo-factura').value;

        if (!nombre || !localidad) return alert('Por favor, completa tu nombre y localidad.');

        if (chkFactura && (!cuit || !razonSocial)) {
            return alert('Si requiere factura, debe completar el CUIT y la Razón Social.');
        }

        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Procesando...';

        try {
            // Recalcular total del carrito por seguridad
            let subtotalVal = 0;
            carritoData.items.forEach(item => {
                subtotalVal += (parseFloat(item.precio) || 0) * (parseInt(item.cantidad) || 1);
            });

            let ivaVal = 0;
            let totalVal = subtotalVal;

            if (chkFactura) {
                ivaVal = subtotalVal * 0.21;
                totalVal = subtotalVal + ivaVal;
            }

            // 1. Guardar en Base de Datos vía API
            const response = await fetch('/api/guardar_pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre,
                    localidad,
                    email: chkFactura && emailFactura ? emailFactura : 'vía WhatsApp',
                    carrito: carritoData.items,
                    total: totalVal,
                    requiere_factura: chkFactura,
                    cuit,
                    razon_social: razonSocial,
                    tipo_factura: tipoFactura,
                    iva_aplicado: ivaVal
                })
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error || 'Error al procesar el pedido');
            }

            // 2. Generar mensaje de WhatsApp - Normalizando caracteres
            let mensaje = `Hola! 👋 Soy *${nombre}* de *${localidad}* y me interesa una cotizacion por:%0A%0A`;

            if (chkFactura) {
                mensaje += `*📋 DATOS FACTURACIÓN (${tipoFactura})*%0A`;
                mensaje += `Razón Social: ${razonSocial}%0A`;
                mensaje += `CUIT: ${cuit}%0A`;
                if (emailFactura) mensaje += `Email: ${emailFactura}%0A`;
                mensaje += `%0A`;
            }

            carritoData.items.forEach(item => {
                const precioTexto = item.precio > 0 ? `$${parseFloat(item.precio).toLocaleString('es-AR')}` : 'Consultar';
                mensaje += `🛋️ *${item.titulo}*%0A`;
                mensaje += `   - Cantidad: ${item.cantidad}%0A`;
                if (item.tipo_bulto) mensaje += `   - Embalaje: ${item.tipo_bulto}%0A`;
                mensaje += `   - Precio unit: ${precioTexto}%0A`;
                mensaje += `   - Link: ${item.url}%0A%0A`;
            });

            if (chkFactura) {
                mensaje += `*Subtotal:* $${subtotalVal.toLocaleString('es-AR')}%0A`;
                mensaje += `*IVA 21%: * $${ivaVal.toLocaleString('es-AR')}%0A`;
            }

            mensaje += `💰 *TOTAL APROXIMADO: $${totalVal.toLocaleString('es-AR')}*%0A`;
            if (chkFactura) {
                mensaje += `%0A_El total incluye IVA._`;
            } else {
                mensaje += `%0A_Precios sujetos a cambios. No incluyen IVA._`;
            }

            // 3. Cerrar modal y Redirigir
            cerrarCheckout();

            const whatsappUrl = `https://wa.me/5492235772165?text=${mensaje}`;
            window.MGCarrito.clear(); // Limpiar carrito
            window.location.href = whatsappUrl;

        } catch (error) {
            alert(error.message);
            btn.disabled = false;
            btn.innerHTML = '<span>💬</span> Confirmar y enviar WhatsApp';
        }
    });

    function renderCarrito() {
        const contenedor = document.getElementById('carrito-contenido');
        const accionesEl = document.getElementById('carrito-acciones');
        const totalEl = document.getElementById('carrito-total');

        const carrito = window.MGCarrito.get();
        contenedor.innerHTML = '';

        if (!carrito.items.length) {
            contenedor.innerHTML = `
            <div class="py-20 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Tu carrito está vacío</h2>
                <p class="text-gray-500 mb-8">Aún no has agregado productos para consultar.</p>
                <a href="/catalogo.php" class="inline-block bg-gray-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-violet-500 hover:text-black transition-all">Explorar productos</a>
            </div>
        `;
            accionesEl.classList.add('hidden');
            return;
        }

        accionesEl.classList.remove('hidden');

        carrito.items.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            const embalajeText = item.tipo_bulto ? `Embalaje: ${item.tipo_bulto}` : '';

            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex flex-col p-6 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors';
            itemDiv.innerHTML = `
            <div class="flex flex-col sm:flex-row items-center gap-6 w-full text-center sm:text-left">
                <div class="w-24 h-24 flex-shrink-0 rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                    <img src="${item.imagen || 'https://via.placeholder.com/300?text=Sin+Imagen'}" class="w-full h-full object-cover">
                </div>
                
                <div class="flex-grow">
                    <h3 class="font-bold text-gray-900 text-lg mb-1">${item.titulo}</h3>
                    <p class="text-gray-400 text-sm font-medium mb-1">${embalajeText}</p>
                    <p class="text-violet-600 font-bold">$${item.precio.toLocaleString('es-AR')}</p>
                </div>

                <div class="flex items-center gap-4 bg-white p-2 rounded-2xl shadow-sm border border-gray-100 mx-auto sm:mx-0">
                    <button onclick="MGCarrito.updateCantidad(${item.id}, ${item.cantidad - 1}, '${item.color}')" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors text-xl font-bold text-gray-400">−</button>
                    <span class="w-8 text-center font-black text-gray-900 text-lg">${item.cantidad}</span>
                    <button onclick="MGCarrito.updateCantidad(${item.id}, ${item.cantidad + 1}, '${item.color}')" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors text-xl font-bold text-gray-400">+</button>
                </div>

                <div class="sm:text-right min-w-[120px]">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Subtotal</p>
                    <p class="text-xl font-black text-gray-900">$${subtotal.toLocaleString('es-AR')}</p>
                </div>

                <button onclick="MGCarrito.remove(${item.id}, '${item.color}')" class="text-gray-300 hover:text-red-500 transition-colors p-2 sm:ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        `;
            contenedor.appendChild(itemDiv);
        });

        // Calcular verdadero total
        const carritoTotal = carrito.items.reduce((sum, item) => sum + ((parseFloat(item.precio) || 0) * (parseInt(item.cantidad) || 1)), 0);

        // Actualizar visualización del IVA
        const ivaInfo = document.getElementById('carrito-iva-info');
        const txtIncluye = document.getElementById('txt-incluye-iva');
        const chkFactura = document.getElementById('chk-requiere-factura');
        const camposFac = document.getElementById('campos-facturacion');
        const modalFac = document.getElementById('txt-modal-facturacion');

        const updateIvaView = () => {
            if (chkFactura.checked) {
                const iva = carritoTotal * 0.21;
                const totalConIva = carritoTotal + iva;

                document.getElementById('carrito-iva-monto').textContent = '$' + iva.toLocaleString('es-AR');
                document.getElementById('carrito-total-iva').textContent = '$' + totalConIva.toLocaleString('es-AR');
                ivaInfo.classList.remove('hidden');
                txtIncluye.classList.add('hidden');
                camposFac.classList.remove('hidden');
                modalFac.classList.remove('hidden');
                totalEl.innerHTML = `Subtotal: <span class="text-violet-600">$${carritoTotal.toLocaleString('es-AR')}</span>`;

                // Hacer campos requeridos
                document.getElementById('checkout-cuit').required = true;
                document.getElementById('checkout-razon-social').required = true;
            } else {
                ivaInfo.classList.add('hidden');
                txtIncluye.classList.remove('hidden');
                camposFac.classList.add('hidden');
                modalFac.classList.add('hidden');
                totalEl.innerHTML = `Total: <span class="text-3xl font-black text-violet-600">$${carritoTotal.toLocaleString('es-AR')}</span>`;

                // Quitar requerido
                document.getElementById('checkout-cuit').required = false;
                document.getElementById('checkout-razon-social').required = false;
            }
        };

        chkFactura.removeEventListener('change', updateIvaView);
        chkFactura.addEventListener('change', updateIvaView);
        updateIvaView(); // init
    }

    document.addEventListener('DOMContentLoaded', renderCarrito);
    document.addEventListener('mg:carrito:update', renderCarrito);
</script>

<?php
require __DIR__ . '/includes/footer.php';
?>