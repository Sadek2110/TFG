# 🔒 Auditoría de Seguridad y Autenticación — FastPlay

> Fecha: Mayo 2026 | Versión auditada: 1.0.0

---

## 🔴 FALLOS CRÍTICOS

---

### 1. Inyección SQL (SQL Injection) en `core/Model.php`

**Archivo:** `core/Model.php`  
**Líneas:** 24, 31, 69, 82-90

**Descripción:** Los métodos `findAll()`, `findWhere()`, `count()` y `paginate()` interpolan directamente la variable `$orderBy` en las consultas SQL sin ninguna validación, sanitización ni uso de prepared statements para este parámetro.

**Código afectado:**

```php
// findAll() - línea 24
public function findAll(string $orderBy = 'id DESC', int $limit = 100): array
{
    $st = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT {$limit}");
    return $st->fetchAll();
}

// findWhere() - línea 31
$st = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$limit}");

// paginate() - línea 89-90
$st = $this->db->prepare(
    "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}"
);
```

**Impacto:** Un atacante que pueda controlar el valor de `$orderBy` (por ejemplo, a través de un parámetro GET) puede ejecutar consultas SQL arbitrarias como `ORDER BY id; DROP TABLE users; --`. Afecta a cualquier controlador que acepte parámetros de ordenación del usuario.

**Controladores que usan estos métodos con valores fijos (seguros por ahora, pero frágiles):**
- `AdminController::users()` → `findAll('created_at DESC', 200)`
- `AdminController::teams()` → `findAll('created_at DESC', 100)`
- `AdminController::leagues()` → `findAll('start_date DESC', 100)`
- `AdminController::fields()` → `findAll('name ASC', 100)`

**Corrección propuesta:**

```php
// Witelist de columnas permitidas para ordenación
private array $allowedSortColumns = ['id', 'created_at', 'name', 'match_date', 'start_date'];

private function validateOrderBy(string $orderBy): string
{
    $parts = explode(' ', trim($orderBy));
    $column = $parts[0];
    $direction = strtoupper($parts[1] ?? 'DESC');

    if (!in_array($column, $this->allowedSortColumns)) {
        $column = $this->pk;
    }
    if (!in_array($direction, ['ASC', 'DESC'])) {
        $direction = 'DESC';
    }
    return "{$column} {$direction}";
}

public function findAll(string $orderBy = 'id DESC', int $limit = 100): array
{
    $orderBy = $this->validateOrderBy($orderBy);
    $st = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT " . (int)$limit);
    return $st->fetchAll();
}

public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id DESC', array $conditions = []): array
{
    $orderBy = $this->validateOrderBy($orderBy);
    // ... resto del código con $orderBy validado
}
```

---

### 2. Omisión de verificación CSRF en Login y Registro

**Archivo:** `app/controllers/AuthController.php`  
**Líneas:** 12-41 (login), 48-96 (register)

**Descripción:** Los métodos `login()` y `register()` no llaman a `$this->requireCsrf()`. Cualquier POST a estos endpoints se procesa sin validar el token CSRF.

**Código afectado:**

```php
public function login(): void {
    // <-- No hay requireCsrf()
    $credential = trim($_POST['credential'] ?? '');
    $password   = $_POST['password'] ?? '';
    // ...
}

public function register(): void {
    // <-- No hay requireCsrf()
    $name = trim($_POST['name'] ?? '');
    // ...
}
```

**Factor agravante:** Los formularios HTML de login y registro no incluyen el campo `csrf_token` de forma nativa en el HTML. Dependen exclusivamente de JavaScript (`public/js/forms.js`) para inyectarlo mediante DOM. Si un usuario tiene JavaScript desactivado, el formulario se envía sin token. Como el backend no lo valida, funciona igual, pero la protección CSRF queda completamente anulada.

```javascript
// forms.js - Dependencia frágil en JS
document.querySelectorAll('form[method="POST"]').forEach(function (form) {
    if (form.querySelector('input[name="csrf_token"]')) return;
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'csrf_token';
    input.value = token;
    form.insertBefore(input, form.firstChild);
});
```

**Impacto:** Un atacante puede:
1. Crear una página maliciosa que fuerce el login de una víctima con credenciales controladas por el atacante (login CSRF), llevando a actions realizadas en la cuenta del atacante (historial de acciones atribuidas a la víctima).
2. Registrar cuentas masivamente sin consentimiento del usuario legítimo.
3. Realizar ataques de fuerza bruta distribuida sin tokens CSRF.

**Corrección propuesta:**

```php
public function login(): void {
    $this->requireCsrf();  // AÑADIR
    $credential = trim($_POST['credential'] ?? '');
    // ...
}

public function register(): void {
    $this->requireCsrf();  // AÑADIR
    $name = trim($_POST['name'] ?? '');
    // ...
}
```

Además, añadir el campo `csrf_token` directamente en los formularios HTML (sin depender de JS):

```html
<!-- En login.php y register.php -->
<form method="POST" action="...">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <!-- resto del formulario -->
</form>
```

---

### 3. Persistencia de datos escapados con `htmlspecialchars()` en la Base de Datos

**Archivos:**
- `app/controllers/AuthController.php` (líneas 78, 84)
- `app/controllers/UserController.php` (líneas 35, 37)
- `app/controllers/TeamController.php` (líneas 34-36)

**Descripción:** Se aplica `htmlspecialchars()` a los datos ANTES de insertarlos/actualizarlos en la base de datos. El escape debe aplicarse ÚNICAMENTE al renderizar en vistas HTML, no al almacenar.

**Código afectado:**

```php
// AuthController::register() - líneas 78, 84
'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
'city' => htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8'),

// UserController::update() - líneas 35, 37
'name' => htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
'city' => htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8'),

// TeamController::create() - líneas 34-36
$name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
$city = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8'),
$desc = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8'),
```

**Impacto:**
1. Los datos quedan permanentemente corrompidos: `<script>` se convierte en `&lt;script&gt;` en la BD.
2. Si se usan para API JSON, correos electrónicos, notificaciones, o exportaciones CSV, contendrán entidades HTML ilegibles.
3. Doble-escapado en vistas: si una vista vuelve a aplicar `htmlspecialchars()`, el usuario verá texto como `M&amp;eacute;xico`.
4. No protege contra todos los contextos de inyección (por ejemplo, en atributos sin comillas o en CSS/JavaScript).

**Corrección propuesta:**

```php
// AuthController::register() - GUARDAR DATOS EN CRUDO
'name' => $name,          // Solo trim, sin htmlspecialchars
'city' => trim($_POST['city'] ?? ''),

// UserController::update() - GUARDAR DATOS EN CRUDO
'name' => trim($_POST['name'] ?? ''),
'city' => trim($_POST['city'] ?? ''),

// TeamController::create() - GUARDAR DATOS EN CRUDO
$name = trim($_POST['name'] ?? ''),
$city = trim($_POST['city'] ?? ''),
$desc = trim($_POST['description'] ?? ''),
```

Y asegurarse de que TODAS las vistas escapenen los datos al renderizar con `htmlspecialchars($dato, ENT_QUOTES, 'UTF-8')`, lo cual ya se hace en la mayoría de vistas.

---

### 4. Subida de archivos con validación insuficiente y riesgo de Race Condition

**Archivo:** `app/controllers/UserController.php`  
**Líneas:** 44-55

**Descripción:** La subida de fotos de perfil tiene múltiples deficiencias de seguridad.

**Código afectado:**

```php
if (!empty($_FILES['photo']['tmp_name'])) {
    $allowed = ['image/jpeg','image/png','image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($_FILES['photo']['tmp_name']);
    if (in_array($mime, $allowed) && $_FILES['photo']['size'] <= UPLOAD_MAX_SIZE) {
        $ext      = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime];
        $filename = bin2hex(random_bytes(12)) . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH . '/profiles/' . $filename);
        $data['photo'] = $filename;
        $_SESSION['user_photo'] = $filename;
    }
}
```

**Fallos detectados:**

1. **No se verifica que el directorio de subida exista:** `UPLOAD_PATH . '/profiles/'` podría no estar creado.
2. **No se valida la extensión del nombre de archivo original:** `finfo` puede ser engañado con archivos polyglot (válidos como imagen pero que contienen código ejecutable).
3. **No se verifica la integridad de la imagen:** No se usa `getimagesize()` o `imagecreatefrom*()` para confirmar que es una imagen válida.
4. **Race Condition:** Entre la validación del MIME type y el `move_uploaded_file()`, el archivo temporal podría ser manipulado si se comparte el directorio temporal.
5. **No se establecen permisos restrictivos:** El archivo subido hereda los permisos por defecto del servidor.
6. **No se borran archivos antiguos:** La foto de perfil anterior se acumula en disco.

**Impacto:**
- Un atacante podría subir un script PHP disfrazado de imagen si `finfo` es engañado.
- Aunque el nombre aleatorio (`bin2hex(random_bytes(12))`) dificulta la ejecución directa, si el directorio de uploads es accesible públicamente, un archivo malicioso podría ser accedido y ejecutado en ciertas configuraciones del servidor.

**Corrección propuesta:**

```php
if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg','image/png','image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($_FILES['photo']['tmp_name']);

    if (in_array($mime, $allowed) && $_FILES['photo']['size'] <= UPLOAD_MAX_SIZE) {
        // Validar que sea una imagen real
        $imgInfo = getimagesize($_FILES['photo']['tmp_name']);
        if ($imgInfo === false) {
            $this->flash('error', 'El archivo no es una imagen válida.');
            $this->redirect('/profile');
        }

        // Crear directorio si no existe
        $uploadDir = UPLOAD_PATH . '/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Extensión segura basada en MIME validado
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        // Borrar foto anterior
        $user = (new User())->findById($userId);
        if ($user && $user['photo'] !== 'default.png' && file_exists($uploadDir . $user['photo'])) {
            unlink($uploadDir . $user['photo']);
        }

        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);
        chmod($uploadDir . $filename, 0644);
        $data['photo'] = $filename;
        $_SESSION['user_photo'] = $filename;
    } else {
        $this->flash('error', 'Archivo no permitido. Usa JPG, PNG o WebP (máx. 5MB).');
        $this->redirect('/profile');
    }
}
```

---

### 5. Validación de contraseña débil en registro

**Archivo:** `app/controllers/AuthController.php`  
**Líneas:** 62-65

**Descripción:** Solo se valida la longitud mínima de la contraseña. No se aplican requisitos de complejidad.

**Código afectado:**

```php
if (strlen($password) < 8) {
    $this->flash('error', 'La contraseña debe tener mínimo 8 caracteres.');
    $this->redirect('/register');
}
```

**Impacto:** Contraseñas triviales como `12345678`, `password`, `aaaaaaaa` son aceptadas, facilitando ataques de fuerza bruta, credential stuffing y account takeover.

**Corrección propuesta:**

```php
if (strlen($password) < 8) {
    $this->flash('error', 'La contraseña debe tener mínimo 8 caracteres.');
    $this->redirect('/register');
}
if (!preg_match('/[A-Z]/', $password)) {
    $this->flash('error', 'La contraseña debe contener al menos una mayúscula.');
    $this->redirect('/register');
}
if (!preg_match('/[0-9]/', $password)) {
    $this->flash('error', 'La contraseña debe contener al menos un número.');
    $this->redirect('/register');
}
if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $this->flash('error', 'La contraseña debe contener al menos un carácter especial.');
    $this->redirect('/register');
}
```

---

### 6. API JavaScript sin token CSRF en peticiones AJAX

**Archivo:** `public/js/app.js`  
**Líneas:** 12-19

**Descripción:** Las funciones `api.post()` y `api.delete()` no incluyen el token CSRF en las peticiones. Aunque usan `x-www-form-urlencoded`, no añaden el campo `csrf_token` ni el header `X-CSRF-TOKEN`.

**Código afectado:**

```javascript
post: async function (url, data = {}) {
    const res = await fetch(`${APP_URL}${url}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data)
    });
    return res.json();
},
```

**Impacto:** Si algún endpoint protegido con `requireCsrf()` se consume a través de esta API (que recibe el token por header `HTTP_X_CSRF_TOKEN` según `Controller::verifyCsrf()`), la petición fallará con 403. Sin embargo, el riesgo real es que si se decide relajar la validación CSRF en esos endpoints para que funcione, quedarán expuestos.

**Corrección propuesta:**

```javascript
post: async function (url, data = {}) {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = tokenMeta ? tokenMeta.content : '';

    const res = await fetch(`${APP_URL}${url}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: new URLSearchParams({ ...data, csrf_token: csrfToken })
    });
    return res.json();
},

delete: async function (url, data = {}) {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = tokenMeta ? tokenMeta.content : '';

    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('csrf_token', csrfToken);
    for (const [key, val] of Object.entries(data)) {
        formData.append(key, val);
    }
    const res = await fetch(`${APP_URL}${url}`, {
        method: 'POST',
        body: formData
    });
    return res.json();
}
```

---

## 🟠 FALLOS ALTOS

---

### 7. Modo debug activo (exposición de errores)

**Archivo:** `config/config.php`  
**Líneas:** 24-25

**Descripción:** `display_errors` está activado y `error_reporting` al máximo, lo cual es apropiado para desarrollo pero catastrófico en producción.

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Impacto:**
- Expone rutas absolutas del servidor (`C:\xampp\htdocs\Proyectos\FastPlay\...`).
- Muestra estructura de carpetas y archivos.
- Revela consultas SQL fallidas con nombres de tablas y columnas.
- Muestra stack traces completos con parámetros.

**Corrección propuesta:**

```php
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}
```

---

### 8. Credenciales de base de datos expuestas en texto plano

**Archivo:** `config/config.php`  
**Líneas:** 7-12

**Descripción:** Las credenciales de base de datos están hardcodeadas con el usuario `root` sin contraseña.

```php
define('DB_USER',    'root');
define('DB_PASS',    '');
```

**Impacto:** Si el archivo de configuración es accedido por cualquier vector (LFI, path traversal, error de configuración del servidor, backup expuesto), el atacante obtiene acceso total a la base de datos.

**Corrección propuesta:**

```php
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
```

Y crear el archivo `.env`:

```
DB_USER=fastplay_user
DB_PASS=contraseña_segura_y_compleja
```

**IMPORTANTE:** Añadir `.env` a `.gitignore` para no commitear las credenciales.

---

### 9. Falta de Rate Limiting (fuerza bruta en login)

**Archivo:** `app/controllers/AuthController.php`  
**Método:** `login()`

**Descripción:** No existe ningún mecanismo de limitación de intentos de login. Un atacante puede probar miles de combinaciones de credenciales sin restricción.

**Impacto:**
- Ataque de fuerza bruta contra contraseñas.
- Enumeración de usuarios válidos.
- Ataque de credenciales filtradas (credential stuffing).
- Denegación de servicio indirecta sobre la base de datos.

**Corrección propuesta (implementación simple con sesiones):**

```php
public function login(): void {
    $this->requireCsrf();

    // Rate limiting basado en IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $attemptKey = 'login_attempts_' . $ip;
    $lockKey = 'login_locked_' . $ip;

    // Si está bloqueado
    if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
        $this->flash('error', 'Demasiados intentos. Espera ' . ceil(($_SESSION[$lockKey] - time()) / 60) . ' minutos.');
        $this->redirect('/login');
    }

    // Máximo 5 intentos cada 15 minutos
    $maxAttempts = 5;
    $windowTime = 900; // 15 minutos

    if (!isset($_SESSION[$attemptKey])) {
        $_SESSION[$attemptKey] = ['count' => 0, 'first' => time()];
    }

    $attempts = &$_SESSION[$attemptKey];

    // Resetear si la ventana expiró
    if (time() - $attempts['first'] > $windowTime) {
        $attempts = ['count' => 0, 'first' => time()];
    }

    if ($attempts['count'] >= $maxAttempts) {
        $_SESSION[$lockKey] = time() + 900; // Bloquear 15 min
        $this->flash('error', 'Demasiados intentos. Intenta de nuevo en 15 minutos.');
        $this->redirect('/login');
    }

    $credential = trim($_POST['credential'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($credential) || empty($password)) {
        $this->flash('error', 'Completa todos los campos.');
        $this->redirect('/login');
    }

    $user = (new User())->findByCredential($credential);

    if (!$user || !password_verify($password, $user['password'])) {
        $attempts['count']++;
        $this->flash('error', 'Credenciales incorrectas.');
        $this->redirect('/login');
    }

    // Login exitoso: limpiar intentos
    unset($_SESSION[$attemptKey], $_SESSION[$lockKey]);

    if ($user['is_banned']) {
        $this->flash('error', 'Tu cuenta está suspendida.');
        $this->redirect('/login');
    }

    // ... resto del login exitoso
}
```

---

### 10. Registro sin verificación de email

**Archivo:** `app/controllers/AuthController.php`  
**Líneas:** 87-95

**Descripción:** Al registrarse, el usuario queda inmediatamente logueado sin verificar la propiedad del correo electrónico.

```php
$id = $userModel->insert([...]);
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = 'player';
session_regenerate_id(true);
$this->flash('success', '¡Cuenta creada! Bienvenido a FastPlay.');
$this->redirect('/dashboard');
```

**Impacto:**
- Cualquiera puede registrar cuentas con emails ajenos.
- Suplantación de identidad (registrar con el email de otra persona).
- Cuentas falsas masivas sin control.

**Corrección propuesta:**

Añadir campos a la tabla `users`:
```sql
ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL;
```

Modificar el registro:
```php
$token = bin2hex(random_bytes(32));
$id = $userModel->insert([
    'name'               => $name,
    'email'              => $email,
    'password'           => password_hash($password, HASH_ALGO, ['cost' => HASH_COST]),
    'role'               => 'player',
    'email_verified'     => 0,
    'verification_token' => $token,
]);

// Enviar email de verificación con enlace: APP_URL . '/verify-email?token=' . $token

$this->flash('success', 'Cuenta creada. Revisa tu email para verificarla.');
$this->redirect('/login');
```

---

### 11. Cookies de sesión sin atributos de seguridad

**Archivo:** `index.php`  
**Línea:** 16

**Descripción:** Las cookies de sesión PHP se configuran con valores por defecto, sin `Secure`, `HttpOnly` ni `SameSite`.

```php
session_start();
```

**Impacto:**
- Sin `HttpOnly`: la cookie es accesible via JavaScript (`document.cookie`), permitiendo robo de sesión via XSS.
- Sin `Secure`: la cookie se transmite por HTTP sin cifrar, permitiendo sniffing en redes no seguras.
- Sin `SameSite`: vulnerable a CSRF desde otros sitios.

**Corrección propuesta:**

```php
// Antes de session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);   // Solo si usas HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();
```

O usando `session_set_cookie_params()`:

```php
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),  // true si HTTPS
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();
```

---

### 12. HTTPS no forzado

**Archivo:** `.htaccess`  
**Líneas:** 6-8

**Descripción:** La redirección de HTTP a HTTPS está comentada.

```apache
# Redirect HTTP to HTTPS (uncomment when SSL is configured)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Impacto:** Todo el tráfico viaja sin cifrar, exponiendo contraseñas, tokens de sesión y datos personales en redes no seguras.

**Corrección propuesta:**

Descomentar las líneas cuando se configure SSL:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

Añadir también HSTS:

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

---

### 13. XSS en respuestas JSON del Chat

**Archivo:** `app/controllers/ChatController.php`  
**Líneas:** 36-40

**Descripción:** `sendMessage()` devuelve el mensaje en la respuesta JSON sin escapar el contenido HTML. Aunque el frontend (`room.php`) usa `utils.escapeHtml()` para renderizar los mensajes nuevos, si otro cliente o servicio consume esta API directamente, el contenido HTML/JavaScript inyectado se ejecutará.

```php
$msg = $chat->sendMessage((int)$id, $_SESSION['user_id'], $content);
if ($msg) {
    $this->json(['success' => true, 'message' => $msg]);
}
```

**Impacto:** Un ataque XSS almacenado donde el payload se guarda en la base de datos y se sirve sin sanitizar a cualquier cliente que consuma la API JSON directamente.

**Corrección propuesta:**

Escapear los campos sensibles en la respuesta API:

```php
if ($msg) {
    $msg['content'] = htmlspecialchars($msg['content'], ENT_QUOTES, 'UTF-8');
    $msg['author_name'] = htmlspecialchars($msg['author_name'], ENT_QUOTES, 'UTF-8');
    $this->json(['success' => true, 'message' => $msg]);
}
```

---

### 14. Information Disclosure en mensajes de error

**Archivos:**
- `core/Router.php` (líneas 60-66)
- `core/Controller.php` (líneas 10-13)

**Descripción:** Los errores exponen nombres de archivos y rutas internas.

```php
// Router.php
$file = APP_PATH . '/controllers/' . $controller . '.php';
if (!file_exists($file)) {
    http_response_code(500);
    die("Controller not found: {$controller}");
}

// Controller.php
$file = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
if (!file_exists($file)) {
    http_response_code(404);
    die("View not found: {$view}");
}
```

**Impacto:** Revela estructura interna de carpetas y naming conventions que facilitan ataques dirigidos.

**Corrección propuesta:**

```php
// Router.php
if (!file_exists($file)) {
    error_log("Controller not found: {$controller}");
    http_response_code(404);
    echo $this->renderError(404, 'Página no encontrada');
    return;
}

// Controller.php
if (!file_exists($file)) {
    error_log("View not found: {$view}");
    http_response_code(404);
    die($this->renderError(404, 'Página no encontrada'));
}
```

---

### 15. Token CSRF persistente durante toda la sesión

**Archivo:** `index.php` (líneas 18-21) y `core/Controller.php` (líneas 83-88)

**Descripción:** El token CSRF se genera una sola vez al inicio de la sesión y nunca se regenera. Esto significa que el mismo token es válido durante toda la sesión del usuario, incluso entre múltiples peticiones exitosas.

```php
// index.php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Controller.php
protected function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
```

**Impacto:**
- Si un token se filtra (por ejemplo, en logs o via Referrer header), el atacante tiene una ventana de ataque de toda la sesión.
- No hay protección contra token replay para operaciones críticas.

**Corrección propuesta:**

Regenerar el token después de cada uso exitoso:

```php
protected function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $valid = !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerar tras uso
    }
    return $valid;
}
```

---

## 🟡 FALLOS MEDIOS

---

### 16. Content Security Policy (CSP) débil

**Archivo:** `.htaccess`  
**Línea:** 23

**Descripción:** El CSP permite `unsafe-inline` y `unsafe-eval`, lo cual anula la mayor parte de la protección contra XSS que proporciona.

```apache
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com
```

**Impacto:** Un ataque XSS que logre inyectarse puede ejecutar `eval()`, `setTimeout(string)`, y scripts inline sin restricción, haciendo el CSP inefectivo.

**Corrección propuesta:**

Usar nonces para TailwindCSS en lugar de `unsafe-inline`:

```apache
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' https://cdn.tailwindcss.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';"
```

---

### 17. Sesión no regenerada completamente en logout

**Archivo:** `app/controllers/AuthController.php`  
**Líneas:** 98-105

**Descripción:** En `logout()` se destruye la sesión pero no se regenera el ID antes, lo que podría dejar la cookie de sesión anterior vulnerable si hay otro vector de session fixation.

```php
public function logout(): void {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
    $this->redirect('/');
}
```

**Corrección propuesta:**

```php
public function logout(): void {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }
    session_regenerate_id(true);
    session_destroy();
    $this->redirect('/');
}
```

---

### 18. `SESSION_LIFETIME` definido pero nunca aplicado

**Archivo:** `config/config.php` (línea 14) y `index.php`

**Descripción:** La constante `SESSION_LIFETIME` está definida como 3600 segundos pero no se usa en ninguna parte del código para forzar la expiración de sesiones.

```php
define('SESSION_LIFETIME', 3600);
```

**Impacto:** Las sesiones duran hasta que el navegador se cierra (por defecto de PHP), sin timeout por inactividad. Si un usuario cierra sesión, su cookie de sesión podría ser reutilizada si es robada.

**Corrección propuesta:**

Aplicar el lifetime en `index.php`:

```php
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);

ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

session_start();

// Timeout por inactividad
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header('Location: ' . APP_URL . '/login');
    exit;
}
$_SESSION['last_activity'] = time();
```

---

### 19. Falta de validación de email único en actualización de perfil

**Archivo:** `app/controllers/UserController.php`  
**Líneas:** 31-61

**Descripción:** `update()` no verifica si el email que se quiere asignar ya pertenece a otro usuario.

**Impacto:** Un usuario podría cambiar su email al de otra persona registrada, causando conflictos de cuenta (aunque la tabla tiene UNIQUE en email, el error de BD no se maneja apropiadamente).

**Corrección propuesta:**

```php
public function update(): void {
    $this->requireLogin();
    $userId = $_SESSION['user_id'];

    // Validar email único
    $email = trim($_POST['email'] ?? '');
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->flash('error', 'Email no válido.');
        $this->redirect('/profile');
    }
    if (!empty($email)) {
        $existing = (new User())->findOneWhere(['email' => $email]);
        if ($existing && (int)$existing['id'] !== $userId) {
            $this->flash('error', 'Ese email ya está registrado por otro usuario.');
            $this->redirect('/profile');
        }
    }

    $data = [
        'name'     => trim($_POST['name'] ?? ''),
        'phone'    => trim($_POST['phone'] ?? ''),
        'city'     => trim($_POST['city'] ?? ''),
        'position' => $_POST['position'] ?? '',
        'age'      => (int)($_POST['age'] ?? 0) ?: null,
        'height'   => (int)($_POST['height'] ?? 0) ?: null,
    ];
    // ... resto del código
}
```

---

### 20. Control de autorización ausente en `ChatController::room()`

**Archivo:** `app/controllers/ChatController.php`  
**Líneas:** 13-19

**Descripción:** El método `room()` verifica que la sala existe, pero no verifica que el usuario autenticado sea miembro de esa sala. Cualquier usuario logueado puede ver cualquier sala de chat si conoce su ID.

```php
public function room(string $id): void {
    $this->requireLogin();
    $chat = new Chat();
    $room = $chat->findById((int)$id);
    if (!$room) { $this->redirect('/chat'); }
    $messages = $chat->getMessages((int)$id);
    $this->render('chat/room', compact('room', 'messages'));
}
```

**Impacto:** Vulnerabilidad IDOR (Insecure Direct Object Reference) que permite a cualquier usuario leer mensajes de salas de chat a las que no pertenece.

**Corrección propuesta:**

```php
public function room(string $id): void {
    $this->requireLogin();
    $chat = new Chat();
    $room = $chat->findById((int)$id);
    if (!$room) { $this->redirect('/chat'); }
    if (!$chat->isMember((int)$id, $_SESSION['user_id'])) {
        $this->redirect('/chat');
    }
    $messages = $chat->getMessages((int)$id);
    $this->render('chat/room', compact('room', 'messages'));
}
```

---

### 21. Archivos de base de datos y SQLite potencialmente expuestos

**Archivo:** `database/fastplay.sql`

**Descripción:** El archivo SQL de la base de datos está en un directorio público dentro del proyecto. Dependiendo de la configuración del servidor, podría ser accesible via web.

**Corrección propuesta:**

Añadir al `.htaccess`:

```apache
<FilesMatch "\.sql$">
    Require all denied
</FilesMatch>
```

---

### 22. Falta de validación de tamaño en campo `age` y `height` en registro

**Archivo:** `app/controllers/AuthController.php`  
**Línea:** 85

**Descripción:** El campo `age` acepta cualquier valor entero positivo sin límite.

```php
'age' => (int)($_POST['age'] ?? 0) ?: null,
```

**Impacto:** Se pueden insertar valores absurdos como edad 150 o altura 300 que la aplicación no manejará correctamente en la UI.

**Corrección propuesta:**

```php
$age = (int)($_POST['age'] ?? 0);
$age = ($age >= 16 && $age <= 99) ? $age : null;
// ...
'age' => $age,
```

---

### 23. Nombres de archivo generados con 12 bytes de entropía (96 bits)

**Archivo:** `app/controllers/UserController.php`  
**Línea:** 50

**Descripción:** Aunque 96 bits es decente, se recomienda al menos 128 bits (16 bytes) para nombres de archivo impredecibles.

```php
$filename = bin2hex(random_bytes(12)) . '.' . $ext;
```

**Corrección propuesta:**

```php
$filename = bin2hex(random_bytes(16)) . '.' . $ext;
```

---

### 24. Potencial Path Traversal en Router (bajo riesgo)

**Archivo:** `core/Router.php`  
**Línea:** 62

**Descripción:** El nombre del controlador se concatena directamente en la ruta sin sanitización. Aunque viene de las rutas registradas en `index.php` (que son fijas), si el enrutamiento se hiciera dinámico en el futuro, sería vulnerable a path traversal.

```php
$file = APP_PATH . '/controllers/' . $controller . '.php';
```

**Corrección propuesta:**

```php
$controller = basename($controller);  // Previene path traversal
$file = APP_PATH . '/controllers/' . $controller . '.php';
```

---

### 25. Sin límite en el tamaño del campo `content` del chat a nivel de base de datos

**Archivo:** `database/fastplay.sql` (línea 218)

**Descripción:** El campo `content` en `chat_messages` es de tipo `TEXT` sin restricción adicional, aunque en el frontend hay un `maxlength="1000"`.

```sql
content TEXT NOT NULL,
```

**Impacto:** Si el límite del frontend es bypasseado (modificando el HTML o enviando la petición manualmente), se pueden insertar mensajes de hasta 65KB que no serán rechazados por la BD.

**Corrección propuesta (ya implementada en el controlador pero vulnerable a bypass):**

Añadir validación en el modelo:

```php
public function sendMessage(int $roomId, int $userId, string $content): array|false
{
    if (empty(trim($content)) || strlen($content) > 1000) {
        return false;
    }
    // ... resto del código
}
```

---

## 📊 RESUMEN

| Nivel | Cantidad | IDs |
|-------|----------|-----|
| **Crítico** 🔴 | 6 | #1 (SQLi), #2 (CSRF login/register), #3 (Datos escapados), #4 (Upload inseguro), #5 (Contraseñas débiles), #6 (API sin CSRF) |
| **Alto** 🟠 | 9 | #7 al #15 |
| **Medio** 🟡 | 10 | #16 al #25 |

---

## 🛡️ RECOMENDACIONES GENERALES

1. **Implementar un WAF o firewall de aplicación** para filtrar peticiones maliciosas.
2. **Usar HTTPS en producción** con certificados SSL/TLS válidos.
3. **Separar configuración por entorno** (development, staging, production) con variables de entorno.
4. **Añadir logging de seguridad** para registrar intentos de login fallidos, CSRF rechazados, etc.
5. **Configurar backups automáticos** de la base de datos.
6. **Mantener PHP actualizado** a la última versión estable.
7. **Ejecutar un escaneo de dependencias** regularmente (aunque este proyecto no usa Composer).
8. **Considerar migrar a un framework** (Laravel, Symfony) que incluya protecciones integradas contra estos vectores de ataque.
9. **Añadir encabezado `Permissions-Policy`** en `.htaccess`.
10. **Considerar HSTS preload** para forzar HTTPS en todos los navegadores.
