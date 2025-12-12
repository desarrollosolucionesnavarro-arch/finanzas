# Guía de Prueba - Sistema Finanzas Mejorado

## Cambios Realizados (Opción C + Optimizaciones)

Este documento proporciona instrucciones para probar todas las nuevas características de seguridad.

---

## 1. Protección CSRF (Cross-Site Request Forgery)

### Cómo Probar:
1. Abre las DevTools del navegador (F12)
2. Abre la pestaña "Network"
3. Navega a cualquier formulario (registro, login, agregar pago, etc.)
4. Intenta enviar un POST manualmente alterando el token CSRF:

#### Test 1: Token Válido (Debería Funcionar)
- El sistema debe aceptar el POST y procesar correctamente

#### Test 2: Token Inválido (Debería Fallar)
- Abre DevTools → Consola
- Modifica el valor del campo `_csrf_token` antes de enviar
- Resultado esperado: Error "Token de seguridad inválido"

#### Test 3: Token Ausente (Debería Fallar)
- Usa `curl` para POST sin incluir el token:
```bash
curl -X POST http://localhost/finanzas/public/register_action.php \
  -d "nombre=Test&email=test@example.com&password=123456&password_confirm=123456"
```
- Resultado esperado: Error "Token de seguridad inválido"

---

## 2. Validación de Email

### Dónde Aplica:
- Registro (`register_action.php`)
- Login (`login_action.php`)

### Cómo Probar:

#### Test 1: Email Válido
1. Ir a `/register.php`
2. Ingresar: `usuario@ejemplo.com` ✅ Funciona

#### Test 2: Email Inválido
1. Ir a `/register.php`
2. Ingresar: `usuariosinpuntocomom` ❌ Error: "El correo no es válido"
3. Ingresar: `usuario@` ❌ Error: "El correo no es válido"
4. Ingresar: `usuario.ejemplo.com` ❌ Error: "El correo no es válido"

---

## 3. Validación de Monto (Decimal)

### Dónde Aplica:
- Agregar pago (`add_payment_action.php`)
- Editar pago (`edit_payment_action.php`)
- Agregar gasto (`add_expense_action.php`)
- Editar gasto (`edit_expense_action.php`)

### Rango Válido: 0.01 - 999,999.99

### Cómo Probar:

#### Test 1: Monto Válido
1. Agregar pago con monto: `1500.50` ✅ Funciona

#### Test 2: Monto Demasiado Bajo
1. Agregar pago con monto: `0.00` ❌ Error: "El monto debe estar entre 0.01 y 999999.99"
2. Agregar pago con monto: `0.001` ❌ Error (fuera de rango)

#### Test 3: Monto Demasiado Alto
1. Agregar pago con monto: `1000000` ❌ Error: "El monto debe estar entre 0.01 y 999999.99"

#### Test 4: Monto No Numérico
1. Agregar pago con monto: `abc123` ❌ Error: "El monto debe estar entre 0.01 y 999999.99"

#### Test 5: Monto Negativo
1. Agregar pago con monto: `-500` ❌ Error: "El monto debe estar entre 0.01 y 999999.99"

---

## 4. Validación de Fecha

### Dónde Aplica:
- Agregar pago (`add_payment_action.php`)
- Editar pago (`edit_payment_action.php`)

### Formato Requerido: `YYYY-MM-DD` (ISO 8601)

### Cómo Probar:

#### Test 1: Fecha Válida
1. Agregar pago con fecha: `2024-01-15` ✅ Funciona

#### Test 2: Formato Incorrecto
1. Agregar pago con fecha: `15/01/2024` ❌ Error: "La fecha no es válida. Usa formato YYYY-MM-DD"
2. Agregar pago con fecha: `2024-13-01` ❌ Error (mes inválido)
3. Agregar pago con fecha: `2024-01-32` ❌ Error (día inválido)

#### Test 3: Fecha No Válida
1. Agregar pago con fecha: `2024-02-30` ❌ Error: "La fecha no es válida..."
2. Agregar pago con fecha: `abcd-ef-gh` ❌ Error: "La fecha no es válida..."

---

## 5. Validación de Tipo de Pago

### Dónde Aplica:
- Agregar pago (`add_payment_action.php`)
- Editar pago (`edit_payment_action.php`)

### Valores Válidos: `quincenal` o `mensual`

### Cómo Probar:

#### Test 1: Tipo Válido
1. Agregar pago con tipo: `quincenal` ✅ Funciona
2. Agregar pago con tipo: `mensual` ✅ Funciona

#### Test 2: Tipo Inválido
1. Usar DevTools para cambiar tipo a: `semanal` ❌ Error: "Tipo de pago no válido"
2. Usar DevTools para cambiar tipo a: `diario` ❌ Error: "Tipo de pago no válido"

---

## 6. Mejora en Eliminaciones (DELETE)

### Cambio: De GET directo a POST con confirmación

### Cómo Funcionaba Antes:
- Click en "Eliminar" → Eliminación inmediata
- Vulnerable a ataques CSRF

### Cómo Funciona Ahora:
1. Click en "Eliminar" → Página de confirmación
2. Se muestran detalles del elemento a eliminar
3. Botones de confirmación y cancelación
4. POST a `delete_*_action.php` con token CSRF

### Flujo de Prueba:

#### Eliminar Pago:
1. Dashboard → Click en "Ver detalles" de un pago
2. Click en botón "🗑️ Eliminar"
3. Debería mostrar: "Confirmar eliminación" con detalles
4. Click en "Eliminar" → Confirmación y redirección a dashboard

#### Eliminar Gasto:
1. Ir a "Gastos"
2. Click en botón 🗑️ en la fila de un gasto
3. Debería mostrar: "Confirmar eliminación" con detalles
4. Click en "Eliminar" → Confirmación y redirección

#### Prueba de Seguridad:
- Enviar POST a `delete_payment_action.php` sin token CSRF
- Resultado: Error "Token de seguridad inválido"

---

## 7. Flujo Completo de Seguridad

### Caso de Uso: Nuevo Usuario

1. **Registro Seguro:**
   ```
   - Click en "Registro"
   - Ingresar datos (email debe ser válido)
   - Token CSRF se valida automáticamente
   - Si email inválido → Error y recarga del formulario
   - Si token inválido → Error de seguridad
   ```

2. **Login Seguro:**
   ```
   - Click en "Iniciar Sesión"
   - Ingresar credenciales (email se valida)
   - Token CSRF se valida automáticamente
   - Sesión iniciada correctamente
   ```

3. **Agregar Pago Seguro:**
   ```
   - Click en "Agregar pago"
   - Ingresar datos (validaciones en tiempo real):
     * Monto entre 0.01 y 999999.99
     * Fecha en formato YYYY-MM-DD
     * Tipo: quincenal o mensual
   - Token CSRF se valida automáticamente
   - Pago creado correctamente
   ```

4. **Agregar Gasto Seguro:**
   ```
   - Click en "Registrar gasto" o "Agregar gasto"
   - Ingresar datos (validaciones):
     * Monto entre 0.01 y 999999.99
   - Token CSRF se valida automáticamente
   - Gasto creado correctamente
   ```

5. **Eliminar con Confirmación:**
   ```
   - Click en "🗑️ Eliminar"
   - Se muestra página de confirmación
   - Detalles del elemento se muestran
   - Click en "Eliminar" → Confirmación
   - Token CSRF se valida automáticamente
   - Elemento eliminado
   ```

---

## 8. Comprobaciones de Rendimiento (Optimizaciones Anteriores)

El sistema ya incluye:

### Dashboard Optimizado:
- ✅ 4 queries simples en lugar de JOINs complejos
- ✅ LIMIT 6 en pagos recientes
- ✅ LIMIT 5 en gastos recientes
- ✅ Precarga de gastos por pago en una sola query

### Tiempo de Carga Esperado:
- Dashboard: < 200ms
- Listados: < 150ms
- Operaciones CRUD: < 100ms

### Para Medir Rendimiento:
1. DevTools → Network
2. Recargar página
3. Buscar tiempos de respuesta en PHP (columna "Time")

---

## 9. Comandos de Prueba con cURL

### Prueba de CSRF:
```bash
# Sin token (debería fallar)
curl -X POST http://localhost/finanzas/public/register_action.php \
  -d "nombre=Test&email=test@example.com&password=123456&password_confirm=123456"

# Con token válido (debería funcionar si los datos son válidos)
curl -X POST http://localhost/finanzas/public/login_action.php \
  -d "email=usuario@example.com&password=password&_csrf_token=TOKEN_AQUI"
```

### Prueba de Validación de Monto:
```bash
curl -X POST http://localhost/finanzas/public/add_payment_action.php \
  -d "tipo=quincenal&monto=0&fecha_pago=2024-01-15&_csrf_token=TOKEN" \
  -H "Cookie: PHPSESSID=SESSION_ID"
# Resultado esperado: Error "El monto debe estar entre 0.01..."
```

---

## 10. Verificación de Logs

Aunque el sistema no tiene logs de auditoría implementados, se pueden agregar para rastrear:

- Intentos fallidos de validación CSRF
- Intentos fallidos de validación de datos
- Cambios sensibles (eliminaciones, cambios de datos)

### Próxima Fase:
Agregar logs a `data/logs.txt` para auditoría.

---

## Conclusión

El sistema ahora está significativamente más seguro con:

✅ Protección CSRF en todos los formularios
✅ Validación robusta de entrada
✅ Eliminaciones con confirmación
✅ Comparación de tokens resistente a ataques de tiempo
✅ Prepared statements en todas las queries (protección SQL injection)

**Estado:** Listo para producción (con HTTPS en servidor real)
