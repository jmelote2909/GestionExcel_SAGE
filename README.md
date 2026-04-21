# GestionExcel_SAGE

Aplicacion desarrollada para importar, visualizar, analizar y exportar balances contables procedentes de SAGE, con soporte tanto para entorno web como para aplicacion de escritorio en Windows.

## Resumen

`GestionExcel_SAGE` centraliza el tratamiento de balances exportados desde SAGE en una unica herramienta. La aplicacion permite:

- importar archivos Excel con informacion contable,
- almacenar los datos en base de datos para su reutilizacion,
- filtrar por empresa, mes y ano,
- consultar indicadores financieros desde un dashboard,
- gestionar presupuestos de ventas,
- comparar valores reales frente a presupuesto,
- exportar la informacion tratada nuevamente a formato `xlsx`,
- distribuir la aplicacion como ejecutable autoactualizable.

## Caracteristicas principales

- Importacion de balances desde archivos `.xlsx` y `.xls`
- Extraccion automatica de metadatos: titulo, NIF, ejercicio, empresa y moneda
- Almacenamiento estructurado de movimientos y filas resumen
- Dashboard con resumen de ventas, compras, salarios, otros gastos y resultado
- Filtros dinamicos por ano, mes y empresa
- Vista de detalle por categoria contable
- Gestion de presupuestos mensuales de ventas
- Exportacion a Excel compatible con web y NativePHP
- Version de escritorio construida con NativePHP y Electron
- Sistema de actualizacion mediante releases de GitHub

## Stack tecnologico

- PHP 8.2+
- Laravel 11
- Livewire 4
- Blade
- SQLite
- PhpSpreadsheet
- OpenSpout
- NativePHP / Electron
- Vite

## Arquitectura

La aplicacion sigue una arquitectura basada en Laravel con componentes reactivos en Livewire.

- `app/Livewire/`
  Componentes principales de la aplicacion:
  - `Dashboard.php`
  - `ExcelViewer.php`
  - `BudgetManager.php`
  - `CategoryViewer.php`
- `resources/views/livewire/`
  Vistas Blade asociadas a cada modulo funcional
- `app/Models/Balance.php`
  Modelo principal para almacenamiento y consulta de datos contables
- `routes/web.php`
  Definicion de rutas principales de navegacion
- `config/nativephp.php`
  Configuracion de la aplicacion de escritorio y del updater

## Modulos de la aplicacion

### 1. Dashboard

Pantalla principal con indicadores agregados del balance:

- ventas,
- compras,
- salarios,
- otros gastos,
- financieros,
- resultado.

Permite analizar rapidamente la situacion general y navegar a vistas de detalle.

### 2. Gestor Excel

Modulo orientado a la importacion y explotacion del balance:

- carga de archivos Excel exportados desde SAGE,
- deteccion de metadatos,
- almacenamiento de lineas contables,
- ordenacion y filtrado,
- edicion de algunos campos operativos,
- exportacion final a `.xlsx`.

### 3. Presupuestos

Seccion para registrar y actualizar presupuestos mensuales de ventas por linea o descripcion, comparando esos importes con los valores reales ya importados.

### 4. Vista por categoria

Permite profundizar en categorias concretas del balance y revisar la informacion por periodo y empresa, con enfoque de analisis.

## Flujo funcional

1. El usuario importa un Excel procedente de SAGE.
2. La aplicacion procesa el archivo y extrae cabeceras, metadatos y movimientos.
3. Los datos se almacenan en la base de datos.
4. El usuario consulta el dashboard y las vistas filtradas.
5. Puede ajustar presupuestos mensuales si procede.
6. Finalmente puede exportar el resultado a un nuevo archivo Excel.

## Requisitos

Antes de ejecutar el proyecto en local, asegurese de disponer de:

- PHP 8.2 o superior
- Composer
- Node.js y npm
- Extensiones PHP habituales de Laravel
- `xmlwriter` recomendado para exportacion Excel completa en entorno PHP web

## Instalacion

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

Si se usa SQLite, asegurese de que exista el archivo:

```bash
database/database.sqlite
```

## Ejecucion en entorno web

```bash
npm run dev
php artisan serve
```

Acceso habitual en desarrollo:

- `http://127.0.0.1:8000`
- o la URL local configurada en el entorno, por ejemplo `http://gestionexcel_sage.test/`

## Ejecucion como app nativa

Para desarrollo con NativePHP:

```bash
composer run native:dev
```

O directamente:

```bash
php artisan native:serve
```

## Build de escritorio

Build local para Windows x64:

```bash
php artisan native:build win x64
```

Publicacion de una nueva version para el sistema de actualizacion:

```bash
php artisan native:build win x64 --publish
```

## Actualizaciones de la app

La aplicacion de escritorio utiliza el updater de NativePHP/Electron con proveedor GitHub. Para que los clientes reciban una nueva version sin reinstalar manualmente:

1. Incrementar la version en `.env`:

```env
NATIVEPHP_APP_VERSION=1.0.4
```

2. Generar y publicar la build:

```bash
php artisan native:build win x64 --publish
```

3. Verificar que se haya creado la release correspondiente y que `latest.yml` apunte al nuevo instalador.

## Exportacion Excel

La exportacion genera archivos `.xlsx`. En entorno web se usa `PhpSpreadsheet` y en entorno nativo existe un fallback para mantener la exportacion en Excel incluso si el runtime empaquetado no incluye `XMLWriter`.

## Estructura de datos

La aplicacion trabaja principalmente con registros de balance que contienen, entre otros, los siguientes campos:

- `Cuenta`
- `Descripcion`
- `DebeP`
- `HaberP`
- `SaldoP`
- `DebeA`
- `HaberA`
- `SaldoA`
- `Grupo`
- `presupuesto`
- `correccion`
- `empresa`
- `mes`
- `ano`
- `is_summary`

## Estado del proyecto

El proyecto cubre el flujo principal de:

- importacion,
- consulta,
- analisis,
- presupuestacion,
- exportacion,
- despliegue nativo.

Se recomienda seguir evolucionando la documentacion tecnica, la cobertura de tests y el endurecimiento del empaquetado nativo para builds mas seguras.

## Repositorio

- GitHub: [jmelote2909/GestionExcel_SAGE](https://github.com/jmelote2909/GestionExcel_SAGE)

## Autor

- Jesus Melendez Oteros

