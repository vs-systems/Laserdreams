<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin | Laserdreams</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
  </style>
</head>

<body class="bg-[#fbfbfb] antialiased">

  <header class="bg-gray-900 text-white sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <span class="text-xl font-black tracking-tight">LASER <span class="text-violet-500">DREAMS</span> Admin</span>
        <span
          class="hidden md:inline text-xs font-bold uppercase tracking-widest text-gray-400 bg-white/5 px-3 py-1 rounded-full">Panel
          de Gestión</span>
      </div>
      <a href="/admin/logout.php"
        class="bg-white/10 hover:bg-white/20 px-5 py-2 rounded-xl text-sm font-bold transition-all">Cerrar Sesión</a>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-12">

    <main class="max-w-7xl mx-auto px-4 py-12">

      <div class="mb-12 flex flex-col md:flex-row justify-between items-end gap-6">
        <div>
          <h1 class="text-4xl font-black text-gray-900 mb-2 tracking-tight">Dashboard</h1>
          <p class="text-gray-500 font-medium">Panel de control integral de <span
              class="text-violet-600 font-bold">LASER
              DREAMS</span>.</p>
        </div>
        <div class="bg-violet-500/10 border border-violet-500/20 px-6 py-3 rounded-2xl flex items-center gap-3">
          <span class="animate-pulse w-2 h-2 bg-violet-500 rounded-full"></span>
          <span class="text-[10px] font-black uppercase tracking-widest text-violet-900">Sistema Activo y
            Sincronizado</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

        <!-- Informes -->
        <a href="/admin/informes/index.php"
          class="group bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 relative overflow-hidden">
          <div
            class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700">
          </div>
          <div class="relative">
            <div class="text-4xl mb-6">📊</div>
            <h3 class="text-2xl font-black text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">Informes</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6 font-medium">Analiza el rendimiento del catálogo,
              visitas y métricas clave de negocio.</p>
            <span
              class="inline-flex items-center text-blue-600 font-black text-[10px] uppercase tracking-widest gap-2 bg-blue-50 px-4 py-2 rounded-xl">Ver
              Estadísticas <span>→</span></span>
          </div>
        </a>

        <!-- Productos -->
        <a href="/admin/productos/index.php"
          class="group bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-violet-500/10 transition-all duration-500 relative overflow-hidden">
          <div
            class="absolute top-0 right-0 w-32 h-32 bg-violet-50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700">
          </div>
          <div class="relative">
            <div class="text-4xl mb-6">🪑</div>
            <h3 class="text-2xl font-black text-gray-900 mb-3 group-hover:text-violet-600 transition-colors">Productos
            </h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6 font-medium">Administra el inventario, multimedia y
              precios de forma ágil y profesional.</p>
            <span
              class="inline-flex items-center text-violet-600 font-black text-[10px] uppercase tracking-widest gap-2 bg-violet-50 px-4 py-2 rounded-xl">Gestionar
              Stock <span>→</span></span>
          </div>
        </a>

        <!-- Pedidos -->
        <a href="/admin/pedidos/index.php"
          class="group bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-green-500/10 transition-all duration-500 relative overflow-hidden">
          <div
            class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700">
          </div>
          <div class="relative">
            <div class="text-4xl mb-6">📦</div>
            <h3 class="text-2xl font-black text-gray-900 mb-3 group-hover:text-green-600 transition-colors">Consultas
            </h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6 font-medium">Gestión integral de consultas entrantes y
              seguimiento de WhatsApp.</p>
            <span
              class="inline-flex items-center text-green-600 font-black text-[10px] uppercase tracking-widest gap-2 bg-green-50 px-4 py-2 rounded-xl">Atender
              Pedidos <span>→</span></span>
          </div>
        </a>

        <!-- Usuarios -->
        <a href="/admin/usuarios/index.php"
          class="group bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
          <div class="flex items-center gap-6">
            <div
              class="w-16 h-16 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
              👥</div>
            <div>
              <h3 class="font-black text-gray-900 group-hover:text-purple-600 transition-colors">ABM Usuarios</h3>
              <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Roles y Permisos</p>
            </div>
          </div>
        </a>

        <!-- Ajustes -->
        <a href="/admin/configuraciones/index.php"
          class="group bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
          <div class="flex items-center gap-6">
            <div
              class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
              ⚙️</div>
            <div>
              <h3 class="font-black text-gray-900 group-hover:text-blue-600 transition-colors">Configuraciones</h3>
              <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Textos Dinámicos</p>
            </div>
          </div>
        </a>

        <!-- Categorías -->
        <a href="/admin/configuraciones/categorias.php"
          class="group bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
          <div class="flex items-center gap-6">
            <div
              class="w-16 h-16 bg-violet-50 text-violet-600 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-violet-600 group-hover:text-white transition-all duration-300">
              🏷️</div>
            <div>
              <h3 class="font-black text-gray-900 group-hover:text-violet-600 transition-colors">Categorías</h3>
              <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Organización</p>
            </div>
          </div>
        </a>

      </div>

    </main>

    <footer
      class="max-w-7xl mx-auto px-4 py-12 mt-12 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 text-gray-400 text-sm font-medium">
      <p class="font-bold">© <?= date('Y') ?> Laserdreams <span class="text-gray-200 mx-2">|</span> Gestión Profesional
      </p>
      <div class="flex gap-6 items-center">
        <a href="/" class="hover:text-gray-900 transition-colors font-black text-[10px] uppercase tracking-[0.2em]">Ver
          Sitio Web</a>
        <span class="w-1.5 h-1.5 bg-gray-200 rounded-full"></span>
        <span class="text-[9px] uppercase font-black tracking-widest text-gray-300">VS System v3.0 Elite</span>
      </div>
    </footer>

</body>

</html>

</body>

</html>