# Copilot / Agent Skills — Convenciones del repositorio

Dónde guardar skills:

- Colocar skills en `.vscode/agents/<skill-name>/SKILL.md`.
- Mantener `AGENTS.md` actualizado con nuevas entradas.

Plantilla de `SKILL.md` mínima:

- Nombre
- Propósito breve
- `applyTo` (patrones glob) para limitar ámbito
- Ejemplos de comandos
- Requisitos y permisos

Ejemplo de uso práctico para este repo:

- Skill `explore`: lee `src/`, `templates/`, y `frontend/src/` y responde con un resumen.
- Skill `migrate-db`: instrucciones seguras para ejecutar migraciones de Doctrine (solo sugiere, no ejecuta sin confirmación).

Sugerencia: cuando crees un skill nuevo, añade primero el `SKILL.md` y prueba en una rama separada.
