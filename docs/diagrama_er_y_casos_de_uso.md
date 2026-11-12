# Diagrama entidad-relación (bd_TurnosVirtuales)

Pega este bloque en https://mermaid.live para obtener la imagen y colocarla en el informe.

```mermaid
erDiagram
    USERS ||--o{ MODULOS : "atiende"
    SERVICIOS ||--o{ MODULOS : "tiene"
    SERVICIOS ||--o{ TURNOS : "genera"
    MODULOS ||--o{ TURNOS : "atiende"

    USERS {
        bigint id PK
        string name
        string email
        string password
        enum rol "admin | asesor"
    }
    SERVICIOS {
        bigint id PK
        string nombre "Caja, Citas"
        string prefijo "C, A"
        boolean activo
    }
    MODULOS {
        bigint id PK
        string nombre
        bigint servicio_id FK
        bigint user_id FK
        boolean activo
    }
    TURNOS {
        bigint id PK
        bigint servicio_id FK
        bigint modulo_id FK
        int numero
        string codigo "A27"
        string nombre_ciudadano
        string documento
        enum estado "espera, llamado, atendiendo, finalizado, cancelado"
        date fecha
        timestamp llamado_at
        timestamp finalizado_at
    }
```

# Actores y casos de uso

| Actor | Casos de uso |
|---|---|
| Ciudadano | Seleccionar servicio, tomar turno, imprimir ticket, ver su turno en pantalla, escuchar el llamado |
| Asesor | Iniciar sesión, elegir módulo, llamar siguiente, volver a llamar, iniciar atención, finalizar, marcar "no se presentó" |
| Administrador | Iniciar sesión, consultar estadísticas por rango de fechas, exportar reporte CSV |
| Sistema | Numerar turnos por día, difundir eventos por WebSocket (Reverb), anunciar por voz en la pantalla pública |

# Estados de un turno

espera -> llamado -> atendiendo -> finalizado
espera -> llamado -> cancelado (el ciudadano no se presentó)
