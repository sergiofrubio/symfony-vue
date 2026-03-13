---
name: backend-symfony
applyTo:
  - "backend/**"
permissions:
  - read
  - suggest
  - write-on-confirmation
description: |
  Skills específicas para el backend Symfony del repo: explorar la estructura, resumir endpoints,
  revisar fixtures/migrations, guiar la ejecución de pruebas y proponer parches seguros.
invocationExamples:
  - "explore endpoints thoroughness: quick"
  - "suggest: fix phpstan issues"
  - "migrations:status (suggest only)"
limits: |
  El agente NO debe ejecutar comandos destructivos por su cuenta (p. ej. migraciones que alteren datos) sin confirmación explícita del usuario.
---

Propósito
-------

Proveer comprobaciones y sugerencias para tareas comunes del backend Symfony:

- Resumen de rutas y controladores.
- Comprobar estado de migraciones y sugerir comandos exactos.
- Proponer parches para problemas de estilo, tipado o errores detectados por `phpstan`/`ecs`.
- Indicar comandos para ejecutar tests (`php bin/phpunit`) y cómo interpretarlos.

Ejemplos de invocación y comportamiento seguro
----------------------------------------------

- "Resume los endpoints y sus controladores en `src/Controller/` (thoroughness: medium)" → devuelve lista de rutas, métodos y controladores.
- "Migrations: show status" → muestra estado y sugiere `php bin/console doctrine:migrations:migrate` pero NO lo ejecuta; pide confirmación.
- "Propón un parche para resolver error X encontrado por phpstan" → devuelve diff sugerido y pasos para aplicar.

Comportamiento de escritura
--------------------------

Cuando se requiera escritura (parches), el agente generará un diff en formato unified y esperará confirmación explícita antes de aplicar cambios.
