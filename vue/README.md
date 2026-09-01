# plantilla-vue

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Type Support for `.vue` Imports in TS

TypeScript cannot handle type information for `.vue` imports by default, so we replace the `tsc` CLI with `vue-tsc` for type checking. In editors, we need [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) to make the TypeScript language service aware of `.vue` types.

## Customize configuration

See [Vite Configuration Reference](https://vite.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Type-Check, Compile and Minify for Production

```sh
npm run build
```

### Run Unit Tests with [Vitest](https://vitest.dev/)

```sh
npm run test:unit
```

### Lint with [ESLint](https://eslint.org/)

```sh
npm run lint
```

Esto es una plantilla de Vue 3 para los proyectos frontend de la empresa

Este proyecto está dockerizado y listo para usar tanto en entorno de desarrollo como de producción.

Para usar el entorno de desarrollo deberás ejecutar: 
docker compose up --build
y accedes en http://localhost:5173


Para usar el entorno de producción deberás ejecutar: 
docker compose -f docker-compose.yml -f docker-compose.prod.yml up --build -d

y accedes en http://localhost:8080