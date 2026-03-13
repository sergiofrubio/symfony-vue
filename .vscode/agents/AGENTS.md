# Agentes disponibles

Listado de agentes y su propósito para este repositorio (plantilla):

- **Explore**: Exploración rápida y búsqueda en el código. Uso: indicar el alcance (quick/medium/thorough).
- **Fixer**: Sugerir y aplicar parches pequeños y enfocados (correcciones, lint, tests).
- **Reviewer**: Resumen y checklist de PRs o cambios grandes.
- **Tester**: Ejecutar suites de pruebas e informar resultados.

Cómo añadir un nuevo agente:

1. Crear una carpeta en `.vscode/agents/<agent-name>/` con un `SKILL.md` describiendo su comportamiento.
2. Añadir una entrada en este archivo `AGENTS.md` con la descripción y ejemplos de uso.

Entradas de ejemplo añadidas:

- **backend-symfony**: Skills para analizar código Symfony, sugerir migraciones seguras, ejecutar/indicar comandos de `phpunit`, y proponer parches.
- **frontend-vue**: Skills para tareas de Vue/Vite: crear componentes, ejecutar `vitest`, `eslint`, y guiar builds.
