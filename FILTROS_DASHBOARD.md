# Filtros Mejorados para Dashboards

## 🎯 Resumen

Se ha mejorado el sistema de filtros de dashboards, eliminando la necesidad de escribir JSON manualmente y proporcionando una interfaz amigable para seleccionar clientes.

## ✨ Nuevas Funcionalidades

### 1. **Toggle "Mostrar Todos los Clientes"**
- **Campo:** `todos_clientes` (boolean)
- **Por defecto:** `true` ✅
- **Función:**
  - Si está activado → Muestra programas de **todos** los clientes
  - Si está desactivado → Solo muestra programas de los clientes seleccionados

### 2. **Selector Múltiple de Clientes**
- **Campo:** `clientes_ids` (array)
- **Función:**
  - Permite seleccionar uno o más clientes específicos
  - Solo visible cuando "Mostrar Todos los Clientes" está desactivado
  - Búsqueda y selección múltiple
  - Muestra solo programas de los clientes seleccionados

### 3. **Criterios Adicionales (Avanzado)**
- **Campo:** `criterios` (JSON)
- **Función:**
  - Mantiene la funcionalidad original para filtros avanzados
  - Ahora etiquetado como "Criterios Adicionales"
  - Sección colapsada por defecto

## 📝 Estructura de Base de Datos

### Campos Agregados:
```sql
clientes_ids JSON NULL       -- Array de IDs de clientes
todos_clientes BOOLEAN DEFAULT 1  -- Si muestra todos los clientes
```

### Tabla Pivot (Opcional):
Se creó `dashboard_cliente` para relación muchos a muchos, pero actualmente se usa el campo JSON `clientes_ids` por simplicidad.

## 🔄 Lógica de Filtrado

### En el DashboardView:

```php
// 1. Si NO se muestran todos los clientes Y hay clientes seleccionados
if (!$this->dashboard->todos_clientes && $this->dashboard->clientes_ids) {
    $query->whereHas('proyecto.cliente', function ($q) {
        $q->whereIn('clientes.id', $this->dashboard->clientes_ids);
    });
}

// 2. Aplicar criterios adicionales JSON (si existen)
if ($this->dashboard->criterios) {
    foreach ($this->dashboard->criterios as $campo => $valor) {
        $query->where($campo, $valor);
    }
}
```

## 🎨 Interfaz de Usuario

### Formulario de Dashboard:

**Sección "Filtros por Cliente":**
```
┌─────────────────────────────────────────┐
│ Filtros por Cliente                     │
│ ─────────────────────────────────────── │
│                                         │
│ [✓] Mostrar Todos los Clientes         │
│     Si está activado, se mostrarán      │
│     programas de todos los clientes     │
│                                         │
│ (Campo oculto cuando está activado)     │
│ Clientes Específicos:                   │
│ [ Seleccionar... ▼]                     │
│                                         │
└─────────────────────────────────────────┘
```

**Sección "Configuración Avanzada" (colapsada):**
```
┌─────────────────────────────────────────┐
│ ▶ Configuración Avanzada                │
└─────────────────────────────────────────┘
```

## 📖 Ejemplos de Uso

### Ejemplo 1: Dashboard para Todos los Clientes
```
✅ Mostrar Todos los Clientes: SÍ
Clientes Específicos: (oculto)

→ Resultado: Muestra programas de TODOS los clientes
```

### Ejemplo 2: Dashboard Solo para Cliente "ACME Corp"
```
❌ Mostrar Todos los Clientes: NO
Clientes Específicos:
  • ACME Corp

→ Resultado: Solo muestra programas de ACME Corp
```

### Ejemplo 3: Dashboard para Múltiples Clientes
```
❌ Mostrar Todos los Clientes: NO
Clientes Específicos:
  • ACME Corp
  • Tech Solutions
  • Global Industries

→ Resultado: Muestra programas solo de estos 3 clientes
```

### Ejemplo 4: Con Filtros Adicionales
```
❌ Mostrar Todos los Clientes: NO
Clientes Específicos:
  • ACME Corp

Criterios Adicionales (JSON):
{
  "activo": true
}

→ Resultado: Muestra solo programas activos de ACME Corp
```

## 🔍 Verificación

Para verificar que los filtros funcionan correctamente:

1. **Crear un Dashboard de Prueba:**
   - Ve a Configuración → Dashboards → Crear
   - Desactiva "Mostrar Todos los Clientes"
   - Selecciona 1 o 2 clientes
   - Guarda

2. **Visualizar el Dashboard:**
   - Abre el dashboard en nueva pestaña
   - Verifica que solo aparezcan programas de los clientes seleccionados

3. **Cambiar a "Todos":**
   - Edita el dashboard
   - Activa "Mostrar Todos los Clientes"
   - Guarda
   - Refresca el dashboard
   - Deberían aparecer todos los programas

## 🎉 Beneficios

✅ **No más JSON manual** - Interfaz visual intuitiva
✅ **Búsqueda rápida** - Busca clientes por nombre
✅ **Selección múltiple** - Selecciona varios clientes a la vez
✅ **Visualización clara** - Toggle fácil de entender
✅ **Por defecto muestra todos** - Comportamiento seguro
✅ **Backward compatible** - Mantiene criterios JSON para casos avanzados

## 📚 Archivos Modificados

1. **Migración:** `2025_10_11_111614_update_criterios_structure_in_dashboards_table.php`
2. **Modelo:** `app/Models/Dashboard.php`
3. **Resource:** `app/Filament/Resources/DashboardResource.php`
4. **Livewire:** `app/Livewire/DashboardView.php`

---

**Fecha:** 2025-10-11
**Versión:** 2.0 - Sistema de Filtros Mejorado
