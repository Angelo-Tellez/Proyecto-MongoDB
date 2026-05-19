# ProyectoNOSQL — Migrado a MongoDB

## Requisitos
- PHP 8.1+
- Extensión PHP `mongodb` instalada (`pecl install mongodb`)
- MongoDB 6+ corriendo en `localhost:27017`
- Composer

## Instalación

### 1. Instalar la librería PHP de MongoDB
Desde la raíz del proyecto (`ProyectoNOSQL/`):
```bash
composer install
```
Esto descarga `mongodb/mongodb` y crea `vendor/autoload.php`.

### 2. Cargar los datos iniciales
```bash
php bd/seed_mongo.php
```
Esto crea y llena las colecciones: `categorias`, `niveles`, `instructores`, `cursos`.

### 3. Verificar la conexión
Abre en tu navegador:
```
http://localhost/ProyectoNOSQL/orm/test_conexion.php
```
Debe mostrar: **Conexión exitosa a MongoDB. Base de datos: cursos**

### 4. Acceder al proyecto
```
http://localhost/ProyectoNOSQL/modules/dashboard/index.php
```

---

## Cambios respecto a la versión MySQL

| Antes (MySQLi)             | Ahora (MongoDB)                              |
|----------------------------|----------------------------------------------|
| `conexion.php` con mysqli  | `conexion.php` retorna `MongoDB\Database`    |
| `scrip.sql`                | `bd/seed_mongo.php` (PHP)                    |
| `mysqli_query()`           | `find()`, `insertOne()`, `updateOne()`, etc. |
| IDs numéricos (`INT`)      | ObjectIds (string en PHP)                    |
| JOINs en SQL               | Datos embebidos en el documento curso        |
| Triggers para sincronizar  | `updateMany()` explícito en `editarCategoria()` |

## Estructura de colecciones

### `cursos`
```json
{
  "_id": ObjectId,
  "categoria": { "id": "string", "nombre": "string" },
  "nivel": "Principiante|Intermedio|Avanzado|Experto",
  "instructor_id": "string",
  "instructor": { "nombre": "...", "apellido": "...", "email": "..." },
  "nombre": "string",
  "descripcion": "string|null",
  "precio": 299.00,
  "duracion_minutos": 1200,
  "fecha_publicacion": "2024-01-15",
  "estatus": "activo|inactivo",
  "fecha_creacion": ISODate
}
```

### `categorias`
```json
{
  "_id": ObjectId,
  "nombre": "string",
  "descripcion": "string|null",
  "estatus": "activo|inactivo",
  "fecha_creacion": ISODate
}
```

### `niveles`
```json
{ "_id": ObjectId, "nombre": "string", "orden": 1, "estatus": "activo" }
```

### `instructores`
```json
{
  "_id": ObjectId,
  "nombre": "string", "apellido": "string",
  "email": "string", "estatus": "activo|inactivo"
}
```

## Cambiar a MongoDB Atlas (nube)
En `orm/conexion.php`, cambia:
```php
define('MONGO_URI', 'mongodb+srv://usuario:password@cluster.mongodb.net');
```
