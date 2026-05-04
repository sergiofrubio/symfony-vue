# --- 1. Etapa Base (Común para desarrollo y compilación) ---
FROM node:lts-alpine AS base

WORKDIR /app

# Copiamos solo los archivos de dependencias primero para aprovechar la caché de Docker
COPY package*.json ./
RUN npm install --legacy-peer-deps

# --- 2. Etapa de Desarrollo (Dev) ---
FROM base AS dev

# Copiamos el resto del código
COPY . .

# Exponemos el puerto de Vite/Vue
EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host"]

# --- 3. Etapa de Compilación (Builder para Prod) ---
FROM base AS builder

# Copiamos el código y generamos los archivos estáticos
COPY . .
RUN npm run build

# --- 4. Etapa de Producción (Prod) ---
FROM nginx:alpine AS prod

# Copiamos los archivos estáticos generados en la etapa anterior a Nginx
COPY --from=builder /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]