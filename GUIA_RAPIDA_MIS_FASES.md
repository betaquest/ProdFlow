# 🚀 Guía Rápida - Módulo "Mis Fases"

## ¿Qué es "Mis Fases"?

Es una interfaz amigable y dedicada para que los **responsables de fases** puedan:
- ✅ Ver solo **sus** fases asignadas
- ✅ Iniciar, completar y liberar fases con **1-2 clicks**
- ✅ Ver estadísticas de su trabajo en tiempo real
- ✅ Recibir notificaciones automáticas

---

## 📍 Ubicación

**URL**: `/admin/mis-fases`

**Menú**: Aparece como **"Mis Fases"** (primer elemento del menú principal)

---

## 🎯 Pantalla Principal

```
┌─────────────────────────────────────────────────────────────┐
│  🔔 Instrucciones                                           │
│  Gestiona tus fases asignadas aquí                          │
│  • Iniciar: Marca la fase como "En Progreso"              │
│  • Finalizar: Marca la fase como completada                │
│  • Liberar Siguiente: Notifica al siguiente responsable    │
└─────────────────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────┬──────────────┐
│ Total: 15    │ Pendientes:3 │ En Progreso:5│ Completadas:7│
│ [Chart]      │ [Chart]      │ [Chart]      │ [Chart]      │
└──────────────┴──────────────┴──────────────┴──────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Cliente    │ Proyecto │ Programa │ Fase  │ Estado │ Acciones│
├────────────┼──────────┼──────────┼───────┼────────┼─────────┤
│ ACME Corp  │ Prod-001 │ P-2024-A │ Corte │ 🕐 Pend│ [▶ Iniciar]│
│ TechPro    │ Maq-002  │ P-2024-B │ Ensam │ ⚙️ Prog│ [✓ Finalizar]│
│ GlobalMfg  │ Line-003 │ P-2024-C │ Instal│ ✓ Done │ [➡ Liberar] │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Trabajo (3 Pasos)

### Paso 1️⃣: Iniciar Fase
```
Estado: 🕐 Pendiente
↓ Click en "▶ Iniciar"
↓ Confirmar
Estado: ⚙️ En Progreso ✅
+ Se registra fecha/hora de inicio
```

### Paso 2️⃣: Finalizar Fase
```
Estado: ⚙️ En Progreso
↓ Click en "✓ Finalizar"
↓ (Opcional) Agregar notas finales
↓ Confirmar
Estado: ✓ Finalizado ✅
+ Se registra fecha/hora de fin
+ Se guardan notas
```

### Paso 3️⃣: Liberar Siguiente Fase
```
Estado: ✓ Finalizado
↓ Click en "➡ Liberar Siguiente"
↓ Confirmar
Sistema notifica automáticamente:
📧 Email al siguiente responsable
🔔 Notificación in-app
✅ Siguiente fase lista para trabajar
```

---

## 🎨 Acciones Disponibles

| Acción | Icono | Cuándo aparece | Qué hace |
|--------|-------|----------------|----------|
| **Iniciar** | ▶️ | Estado: Pendiente | Cambia a "En Progreso" + registra fecha inicio |
| **Finalizar** | ✓ | Estado: Pendiente/Progreso | Cambia a "Finalizado" + permite agregar notas |
| **Liberar Siguiente** | ➡️ | Estado: Finalizado | Notifica a siguiente rol en secuencia |
| **Editar Notas** | ✏️ | Siempre | Permite agregar/editar comentarios |

---

## 📊 Estadísticas en Tiempo Real

Las 4 tarjetas superiores muestran:

1. **Total Asignadas** (Azul)
   - Todas las fases asignadas a ti
   - Con gráfico de tendencia

2. **Pendientes** (Gris)
   - Fases que aún no has iniciado
   - Por comenzar

3. **En Progreso** (Amarillo)
   - Fases en las que estás trabajando actualmente
   - Acción requerida

4. **Completadas** (Verde)
   - Fases que has finalizado
   - Tu progreso

---

## 🔍 Filtros y Búsqueda

### Filtro por Estado (Predeterminado: "En Progreso")
- 🕐 Pendiente
- ⚙️ En Progreso
- ✓ Finalizado

### Búsqueda
Puedes buscar por:
- Nombre del cliente
- Nombre del proyecto
- Nombre del programa
- Nombre de la fase

---

## 💡 Ejemplo Práctico

### Escenario: Eres operario de "Corte"

**Lunes 9:00 AM**
```
1. Login → Ir a "Mis Fases"
2. Ves: Programa "P-2024-A" | Fase "Corte" | Estado: Pendiente
3. Click "Iniciar" → Confirmar
   ✅ Estado cambia a "En Progreso"
```

**Lunes 3:00 PM (Trabajo terminado)**
```
4. Click "Finalizar"
5. Agrego nota: "Piezas cortadas: 150 unidades. Todo OK"
6. Confirmar
   ✅ Estado cambia a "Finalizado"
   ✅ Aparece botón "Liberar Siguiente"
```

**Lunes 3:05 PM**
```
7. Click "Liberar Siguiente"
8. Confirmar
   ✅ Sistema identifica siguiente fase: "Ensamblado"
   ✅ Notifica a usuarios con rol "Ensamblado"
   📧 Email: "Nueva Fase Liberada - Ensamblado"
   🔔 Notificación en panel
```

**Resultado:**
- Tu trabajo está documentado
- Siguiente equipo fue notificado automáticamente
- No necesitas reportar manualmente

---

## 🆚 Diferencias con "Avances de Fase"

| Característica | Mis Fases | Avances de Fase |
|----------------|-----------|-----------------|
| **Quién lo ve** | Solo responsables | Administradores principalmente |
| **Qué muestra** | Solo tus fases asignadas | Todas las fases del sistema |
| **Propósito** | Gestionar tu trabajo | Administrar todo el sistema |
| **Acciones** | Rápidas (1-2 clicks) | Completas (edición total) |
| **Navegación** | Primer elemento | Dentro de gestión |

**Ambos módulos tienen las mismas acciones**, pero "Mis Fases" es más específico y amigable para operarios.

---

## ✅ Ventajas del Módulo

### Para Ti (Responsable):
✅ No te pierdes entre cientos de registros
✅ Ves solo lo que te corresponde
✅ Acciones rápidas sin complicaciones
✅ Sabes exactamente qué hacer
✅ Estadísticas de tu rendimiento

### Para el Equipo:
✅ No necesitas avisar al siguiente manualmente
✅ Sistema notifica automáticamente
✅ Menos errores de comunicación
✅ Proceso más rápido

### Para la Empresa:
✅ Trazabilidad completa
✅ Auditoría automática
✅ Menos tiempo administrativo
✅ Mayor eficiencia

---

## 🔔 Notificaciones que Recibirás

### 1. Email
```
Asunto: Nueva Fase Liberada - [Nombre de Fase]

Hola [Tu Nombre],

La fase "[Fase Anterior]" ha sido completada.
Programa: [Nombre del Programa]

Ahora puedes trabajar en: "[Tu Fase]"

[Botón: Ver Programa]

¡Es tu turno de trabajar en esta fase!
```

### 2. Notificación In-App (Campana 🔔)
```
Nueva Fase Liberada
Fase: [Nombre]
Programa: [Nombre del Programa]
[Ver Detalles]
```

---

## 🛠️ Casos de Uso

### ✅ Operario de Producción
```
- Inicio de turno → "Mis Fases"
- Ver tareas pendientes
- Iniciar → Trabajar → Finalizar
- Liberar siguiente
- Fin de turno
```

### ✅ Supervisor de Área
```
- Revisar fases asignadas a su área
- Completar tareas del equipo
- Agregar notas de calidad
- Monitorear estadísticas del día
```

### ✅ Coordinador de Línea
```
- Vista global de su responsabilidad
- Asegurar flujo continuo
- Verificar completitud
- Reportar con datos reales
```

---

## ❓ Preguntas Frecuentes

### ¿Puedo ver fases de otros?
No. Solo ves fases donde eres el **responsable_id**.

### ¿Cómo me asignan una fase?
Un administrador o supervisor asigna el responsable al crear el avance de fase.

### ¿Qué pasa si no libero la siguiente fase?
El siguiente responsable NO será notificado. Debes hacerlo manualmente.

### ¿Puedo cambiar una fase finalizada?
Sí, puedes editar notas. Para cambiar estado, contacta a un administrador.

### ¿Se actualizan los datos automáticamente?
Sí, cada **30 segundos** la página se actualiza sola.

### ¿Qué pasa si no hay siguiente fase?
El sistema te avisa: "Esta es la última fase del proceso".

### ¿Qué pasa si no existe el rol de la siguiente fase?
El sistema notifica a los **Administradores** en su lugar.

---

## 🎯 Tips para Usar el Módulo Efectivamente

1. **Inicia tu turno revisando "Mis Fases"**
   - Ve qué tienes pendiente
   - Prioriza según fechas

2. **Agrega notas útiles**
   - Cantidad producida
   - Problemas encontrados
   - Tiempo estimado

3. **Libera inmediatamente después de finalizar**
   - No hagas esperar al siguiente equipo
   - Mantén el flujo de trabajo

4. **Revisa las estadísticas**
   - Ve tu progreso del día/semana
   - Identifica cuellos de botella

5. **Usa los filtros**
   - "En Progreso" para tareas actuales
   - "Pendiente" para planificar

---

## 📱 Acceso Móvil

El módulo es **100% responsivo**:
- ✅ Funciona en celulares
- ✅ Tabletas
- ✅ Computadoras

Puedes gestionar tus fases desde cualquier dispositivo.

---

## 🔐 Seguridad

- ✅ Solo ves tus fases (filtro automático)
- ✅ No puedes editar fases de otros
- ✅ Todos los cambios quedan registrados
- ✅ Auditoría completa de acciones

---

## 📞 Soporte

**¿Tienes dudas?**
1. Consulta esta guía
2. Prueba el módulo con datos de prueba
3. Contacta a tu supervisor
4. Escala a soporte técnico

---

**¡Listo para comenzar!**

1. Inicia sesión en `/admin`
2. Click en "Mis Fases"
3. Empieza a gestionar tu trabajo

**Fecha:** Octubre 2025
**Versión:** 1.0.0
