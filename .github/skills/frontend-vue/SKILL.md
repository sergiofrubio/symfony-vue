---
name: frontend-vue
applyTo:
  - "frontend/**"
permissions:
  - read
  - suggest
  - write-on-confirmation
description: |
  Skills para el frontend (Vue 3 + Vite): analizar componentes, proponer templates,
  guiar la ejecución de tests (`vitest`), linting (`eslint`), y builds.
invocationExamples:
  - "create component ButtonPrimary with props label:string"
  - "run tests (suggest commands)"
  - "lint: fix issues suggestions"
limits: |
  No ejecutar `npm`/`pnpm`/`yarn` que modifiquen el entorno sin confirmación explícita del usuario.
---

Propósito
-------

Facilitar tareas habituales en el frontend:

- Generar plantillas de componentes Vue con props y tests asociados.
- Señalar problemas comunes de lint y proponer fixes.
- Indicar comandos a ejecutar para desarrollo (`npm run dev`), test (`npm run test`/`vitest`), y build (`npm run build`).

Ejemplos de uso
--------------

- "Genera un componente `UserCard.vue` con props `user: User` y prueba unitaria básica" → devuelve archivos sugeridos (.vue, .spec.ts).
- "Describe cómo ejecutar y depurar fallos de `vitest` en este repo" → pasos concretos y comandos.

Comportamiento de escritura
--------------------------

El agente generará parches (diffs) para añadir/actualizar archivos y esperará confirmación antes de aplicar cambios.
