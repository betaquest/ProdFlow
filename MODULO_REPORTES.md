# Módulo de Reportes Generales

## Descripción

Sistema completo de reportes con filtros avanzados y exportación a Excel para analizar el avance de fases por cliente, proyecto y programa.

## Características

### 🔍 Filtros Disponibles

1. **Cliente**: Filtra por cliente específico
2. **Proyecto**: Filtra por proyecto (se actualiza según el cliente seleccionado)
3. **Programa**: Filtra por programa (se actualiza según el proyecto seleccionado)
4. **Fase**: Filtra por fase específica (se actualiza según el programa seleccionado)
5. **Rango de Fechas**:
   - Fecha de Inicio (Desde)
   - Fecha de Fin (Hasta)

### 📊 Datos Mostrados

El reporte muestra la siguiente información:

- **Cliente**: Nombre del cliente
- **Proyecto**: Nombre del proyecto
- **Programa**: Nombre del programa
- **Fase**: Nombre de la fase
- **Fecha Inicio**: Fecha de inicio de la fase
- **Fecha Fin**: Fecha de finalización de la fase
- **Días Duración**: Cantidad de días entre fecha inicio y fin
- **Avance**: Porcentaje de completitud (con barra de progreso visual)
- **Observaciones**: Notas adicionales sobre el avance

### 📥 Exportación a Excel

- Exporta todos los resultados filtrados a formato Excel (.xlsx)
- Incluye encabezados con formato y colores
- Anchos de columna automáticos para mejor visualización
- Nombre de archivo con timestamp: `reporte_general_YYYY-MM-DD_HHMMSS.xlsx`

## Cómo Usar

### 1. Acceder al Módulo

Ve al menú lateral y busca la sección **"Reportes"** → **"Reportes Generales"**

### 2. Configurar Filtros

1. Selecciona los filtros que desees aplicar:
   - Puedes usar uno, varios o todos los filtros
   - Los filtros son opcionales (dejar vacío = mostrar todos)
   - Los filtros en cascada se actualizan automáticamente

2. Haz clic en el botón **"Generar Reporte"**

### 3. Visualizar Resultados

- Los resultados se mostrarán en una tabla con todas las columnas
- La tabla incluye:
  - Barras de progreso visuales para el porcentaje
  - Badges con los días de duración
  - Scroll horizontal para pantallas pequeñas

### 4. Exportar a Excel

1. Una vez generado el reporte, aparecerá el botón **"Exportar a Excel"**
2. Haz clic en el botón para descargar el archivo
3. El archivo se descargará automáticamente con todos los datos filtrados

## Ejemplos de Uso

### Ejemplo 1: Reporte de un Cliente Específico

```
Filtros:
- Cliente: "Acme Corp"
- Resto: Vacío

Resultado: Muestra todas las fases de todos los proyectos de Acme Corp
```

### Ejemplo 2: Reporte por Rango de Fechas

```
Filtros:
- Fecha Inicio (Desde): 01/01/2024
- Fecha Fin (Hasta): 31/03/2024

Resultado: Muestra todas las fases que iniciaron o finalizaron en Q1 2024
```

### Ejemplo 3: Reporte Detallado de un Programa

```
Filtros:
- Cliente: "Acme Corp"
- Proyecto: "Sistema ERP"
- Programa: "Módulo de Ventas"

Resultado: Muestra todas las fases del Módulo de Ventas del Sistema ERP
```

## Permisos

**Acceso**: Solo usuarios con rol **"Administrador"** pueden acceder a este módulo.

Para modificar los permisos, edita el método `canViewAny()` en:
```
app/Filament/Resources/ReporteGeneralResource.php
```

## Estructura de Archivos

```
app/
├── Filament/
│   └── Resources/
│       ├── ReporteGeneralResource.php              # Resource principal
│       └── ReporteGeneralResource/
│           └── Pages/
│               └── ReporteGeneral.php              # Lógica de filtros y reportes
├── Exports/
│   └── ReporteGeneralExport.php                    # Clase de exportación a Excel
│
resources/
└── views/
    └── filament/
        └── resources/
            └── reporte-general-resource/
                └── pages/
                    └── reporte-general.blade.php   # Vista del reporte
```

## Dependencias

- **Laravel Excel**: Para exportación a Excel
  ```bash
  composer require maatwebsite/excel
  ```

## Notas Técnicas

### Cálculo de Días de Duración

```php
$diasDuracion = Carbon::parse($fecha_inicio)->diffInDays(Carbon::parse($fecha_fin));
```

### Query Optimizada

El reporte utiliza `eager loading` para evitar el problema N+1:

```php
AvanceFase::with(['fase.programa.proyecto.cliente', 'fase.programa', 'fase'])
```

### Filtros en Cascada

Los filtros están configurados con `reactive()` y `afterStateUpdated()` para actualizar las opciones automáticamente según la selección previa.

## Mejoras Futuras (Opcional)

- [ ] Gráficos de barras y líneas
- [ ] Exportación a PDF
- [ ] Filtro por estado de fase (activo/completado)
- [ ] Comparativa entre proyectos
- [ ] Dashboard de métricas agregadas
- [ ] Guardado de filtros favoritos
- [ ] Programación de reportes automáticos por email

## Soporte

Para dudas o problemas con el módulo de reportes, contacta al equipo de desarrollo.
