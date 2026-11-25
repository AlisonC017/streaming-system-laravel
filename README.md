# 🎬 Aetherix — Plataforma de Streaming Híbrida

**Aetherix** es una plataforma web de streaming de vídeo (películas, series y anime). Desarrollada en **Laravel**, implementa una **arquitectura de bases de datos híbrida** que combina la integridad transaccional de **Microsoft SQL Server** para la gestión financiera y de usuarios, con la velocidad de lectura y flexibilidad de **MongoDB** para el catálogo multimedia y la búsqueda semántica.

> **Proyecto Final — IF0010 Bases de Datos II**  
> Universidad de Costa Rica · Recinto de Siquirres (II Ciclo 2026)

---

## Arquitectura del Sistema

El almacenamiento de datos se divide según la naturaleza operativa de cada módulo:

* **Microsoft SQL Server (`streamingDB`) — Motor Relacional (ACID):**
  * Gestión de cuentas (`usuario`, `cliente`, `administrador`) con herencia por especialización y contraseñas hasheadas con BCrypt
  * Catálogo comercial de `planes` (Básico, Estándar, Familiar, Premium) y control de `suscripcion` mediante índice único filtrado (`UX_suscripcion_vigente`)
  * Encriptación a nivel de columna (`VARBINARY`) para métodos de pago y trazabilidad completa en `pago` y `auditoria` mediante *Stored Procedures* y *Triggers*
  * Seguridad granular basada en roles (`rol_app`, `rol_admin_total`, `rol_soporte`, `rol_reportes`)
* **MongoDB (`streaming_catalogo`) — Motor NoSQL:**
  * Almacenamiento de más de 145,000 documentos distribuidos en 8 colecciones (`contenidos`, `temporadas`, `episodios`, `identificadores`, `fuentes`, `verificaciones`, `progresos`, `sincronizaciones`)
  * Ingesta automatizada desde APIs externas (**TMDB**, **AniList**, **Tenrai**, **TVmaze**) y generación de vectores (*embeddings* de 768 dimensiones) con **Google Gemini**
  * **Búsqueda Híbrida:** Combina coincidencia por título (regex) con similitud del coseno sobre *embeddings* utilizando el algoritmo **Reciprocal Rank Fusion (RRF)**

## Stack Tecnológico

* **Backend:** PHP 8.2+ · Laravel 12
* **Bases de Datos:** Microsoft SQL Server (`sqlsrv`) · MongoDB (`mongodb/laravel-mongodb`)
* **Frontend:** Blade Templates · CSS3 personalizado · JavaScript
* **APIs Externas:** TMDB, AniList (GraphQL), Tenrai, TVmaze, Google Gemini API (`gemini-embedding-001`)

## Requisitos Previos

* PHP >= 8.2 con las extensiones `pdo_sqlsrv`, `sqlsrv` y `mongodb` habilitadas.
* Composer
* Microsoft SQL Server (2019 o superior)
* MongoDB Server (6.0 o superior)


