# 🎬 Aetherix — Plataforma de Streaming Híbrida

**Aetherix** es una plataforma web de streaming de vídeo (películas, series y anime) inspirada en modelos como Netflix[cite: 1]. Desarrollada en **Laravel**, implementa una **arquitectura de bases de datos híbrida** que combina la integridad transaccional de **Microsoft SQL Server** para la gestión financiera y de usuarios, con la velocidad de lectura y flexibilidad de **MongoDB** para el catálogo multimedia y la búsqueda semántica[cite: 1].

> **Proyecto Final — IF0010 Bases de Datos II**  
> Universidad de Costa Rica · Recinto de Siquirres (II Ciclo 2026)[cite: 1]

---

## 🏗️ Arquitectura del Sistema

El almacenamiento de datos se divide según la naturaleza operativa de cada módulo:

* **Microsoft SQL Server (`streamingDB`) — Motor Relacional (ACID):**
  * Gestión de cuentas (`usuario`, `cliente`, `administrador`) con herencia por especialización y contraseñas hasheadas con BCrypt[cite: 1].
  * Catálogo comercial de `planes` (Básico, Estándar, Familiar, Premium) y control de `suscripcion` mediante índice único filtrado (`UX_suscripcion_vigente`)[cite: 1].
  * Encriptación a nivel de columna (`VARBINARY`) para métodos de pago y trazabilidad completa en `pago` y `auditoria` mediante *Stored Procedures* y *Triggers*[cite: 1].
  * Seguridad granular basada en roles (`rol_app`, `rol_admin_total`, `rol_soporte`, `rol_reportes`)[cite: 1].
* **MongoDB (`streaming_catalogo`) — Motor NoSQL:**
  * Almacenamiento de más de 145,000 documentos distribuidos en 8 colecciones (`contenidos`, `temporadas`, `episodios`, `identificadores`, `fuentes`, `verificaciones`, `progresos`, `sincronizaciones`)[cite: 1].
  * Ingesta automatizada desde APIs externas (**TMDB**, **AniList**, **Tenrai**, **TVmaze**) y generación de vectores (*embeddings* de 768 dimensiones) con **Google Gemini**[cite: 1].
  * **Búsqueda Híbrida:** Combina coincidencia por título (regex) con similitud del coseno sobre *embeddings* utilizando el algoritmo **Reciprocal Rank Fusion (RRF)**[cite: 1].

## Stack Tecnológico

* **Backend:** PHP 8.2+ · Laravel 12
* **Bases de Datos:** Microsoft SQL Server (`sqlsrv`) · MongoDB (`mongodb/laravel-mongodb`)[cite: 1]
* **Frontend:** Blade Templates · CSS3 personalizado · JavaScript
* **APIs Externas:** TMDB, AniList (GraphQL), Tenrai, TVmaze, Google Gemini API (`gemini-embedding-001`)[cite: 1]

## Requisitos Previos

* PHP >= 8.2 con las extensiones `pdo_sqlsrv`, `sqlsrv` y `mongodb` habilitadas.
* Composer
* Microsoft SQL Server (2019 o superior)
* MongoDB Server (6.0 o superior)


