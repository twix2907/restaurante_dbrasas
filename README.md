# D Brasas y Carbon - Sistema de E-commerce

Sistema completo de comercio electrónico para restaurante desarrollado en Laravel 11.

## 🚀 Nuevas Funcionalidades Implementadas

### 1. ✅ Sistema de Pagos Online (Stripe)
- Integración con Stripe para procesamiento de pagos
- Múltiples métodos de pago (tarjeta, PayPal)
- Proceso de checkout seguro
- Confirmación de pagos

### 2. ✅ Notificaciones por Email
- Confirmación automática de pedidos
- Actualizaciones de estado de pedidos
- Plantillas de email personalizadas
- Sistema de notificaciones en tiempo real

### 3. ✅ Sistema de Reseñas y Calificaciones
- Reseñas de productos con calificación 1-5 estrellas
- Sistema de aprobación de reseñas por administradores
- Promedio de calificaciones por producto
- Comentarios de usuarios

### 4. ✅ Gestión de Inventario
- Control de stock en tiempo real
- Alertas de bajo stock
- Logs de movimientos de inventario
- SKU único para productos
- Estados activo/inactivo

### 5. ✅ Reportes y Analytics
- Dashboard con estadísticas en tiempo real
- Reportes de ventas por período
- Análisis de productos más vendidos
- Gráficos interactivos con Chart.js
- Exportación de datos

### 6. ✅ API REST para Móvil
- Autenticación con Sanctum
- Endpoints para productos, pedidos, categorías
- Gestión de reseñas vía API
- Documentación completa de endpoints

### 7. ✅ Optimización de Imágenes
- Servicio de optimización automática
- Redimensionamiento automático
- Múltiples tamaños de imagen
- Compresión inteligente

### 8. ✅ Cache y Performance
- Sistema de cache inteligente
- Cache de consultas frecuentes
- Optimización de consultas
- Warm-up de cache

## 🛠️ Tecnologías Utilizadas

- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Bootstrap 5, Blade Templates
- **Base de Datos:** SQLite/MySQL
- **Pagos:** Stripe
- **Imágenes:** Intervention Image
- **Cache:** Redis/File Cache
- **API:** Laravel Sanctum

## 📦 Instalación

1. **Clonar el repositorio:**
```bash
git clone [url-del-repositorio]
cd "D Brasas y Carbon"
```

2. **Instalar dependencias:**
```bash
composer install
npm install
```

3. **Configurar variables de entorno:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos:**
```bash
php artisan migrate
php artisan db:seed
```

5. **Configurar Stripe (opcional):**
```bash
# Agregar en .env
STRIPE_KEY=tu_stripe_public_key
STRIPE_SECRET=tu_stripe_secret_key
```

6. **Ejecutar el servidor:**
```bash
php artisan serve
npm run dev
```

## 🗄️ Estructura de Base de Datos

### Tablas Principales:
- `users` - Usuarios del sistema
- `categories` - Categorías de productos
- `products` - Productos con inventario
- `orders` - Pedidos con información de pago
- `items` - Items de pedidos
- `reviews` - Reseñas de productos
- `inventory_logs` - Logs de movimientos de inventario
- `sliders` - Carrusel de imágenes

## 🔧 Configuración

### Variables de Entorno Importantes:
```env
APP_NAME="D Brasas y Carbon"
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbrasasycarbon
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="info@dbrasasycarbon.com"
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_KEY=
STRIPE_SECRET=
```

## 📱 API Endpoints

### Autenticación:
- `POST /api/login` - Iniciar sesión
- `POST /api/register` - Registrarse
- `POST /api/logout` - Cerrar sesión

### Productos:
- `GET /api/products` - Listar productos
- `GET /api/products/{id}` - Ver producto
- `GET /api/products/category/{id}` - Productos por categoría

### Pedidos:
- `GET /api/orders` - Mis pedidos
- `POST /api/orders` - Crear pedido
- `GET /api/orders/{id}` - Ver pedido

### Reseñas:
- `GET /api/products/{id}/reviews` - Reseñas de producto
- `POST /api/products/{id}/reviews` - Crear reseña

## 🎨 Características del Frontend

- Diseño responsivo con Bootstrap 5
- Carrusel de imágenes dinámico
- Carrito de compras flotante
- Sistema de reseñas con estrellas
- Dashboard administrativo completo
- Gráficos interactivos

## 🔒 Seguridad

- Autenticación Laravel
- Middleware de administrador
- Validación de formularios
- Protección CSRF
- Sanitización de datos
- Logs de seguridad

## 📊 Reportes Disponibles

- Dashboard general con estadísticas
- Reporte de ventas por período
- Análisis de inventario
- Productos más vendidos
- Gráficos de tendencias

## 🚀 Optimizaciones

- Cache de consultas frecuentes
- Optimización de imágenes
- Lazy loading de componentes
- Compresión de assets
- CDN para archivos estáticos

## 🤝 Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para detalles.

## 📞 Soporte

Para soporte técnico, contacta a:
- Email: info@dbrasasycarbon.com
- Teléfono: +1 718-999-3939

## 🔄 Actualizaciones

### v2.0.0 - Nuevas Funcionalidades
- ✅ Sistema de pagos completo
- ✅ Notificaciones por email
- ✅ Sistema de reseñas
- ✅ Gestión de inventario
- ✅ Reportes avanzados
- ✅ API REST
- ✅ Optimización de imágenes
- ✅ Sistema de cache

---

**Desarrollado con ❤️ para D Brasas y Carbon**
