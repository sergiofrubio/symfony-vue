## agent-customization — Guía rápida

Propósito: describir convenciones para crear, actualizar y publicar agent skills en este repositorio.

Uso recomendado: colocar skills relacionadas con flujos de trabajo del repo bajo `.vscode/agents/<skill-name>/SKILL.md`.

Contenido mínimo de un `SKILL.md`:

- Título y propósito breve.
- Frontmatter (opcional) con `applyTo` patterns y `permissions`.
- Instrucciones de invocación (ej.: "describe: "fix lint errors"; thoroughness: quick").
- Ejemplos y límites (qué puede y qué no puede hacer el agente).

Ejemplo de frontmatter (YAML):

---
name: agent-customization
applyTo:
  - "backend/**"
  - "frontend/**"
permissions:
  - read
  - write
---

Buenas prácticas para este repo:

- Separar skills por responsabilidad (explorar, reparar, testear, revisar).
- Mantener cada SKILL.md conciso (< 300 líneas) y con ejemplos de uso.
- Evitar comandos destructivos sin confirmación.
