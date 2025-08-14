# Dockerfile.CloudRun
FROM node:20-alpine AS builder

WORKDIR /app

# Copiar solo los archivos necesarios para instalar dependencias
COPY ./admin-veterinaria/package.json ./admin-veterinaria/pnpm-lock.yaml* ./

# Activar pnpm y compilar
RUN corepack enable && corepack prepare pnpm@latest --activate
RUN pnpm install

# Copiar el resto del código fuente
COPY ./admin-veterinaria/ .

# Variables de entorno para producción
ENV NODE_ENV=production
ENV VUE_APP_API_URL=/api

RUN pnpm build

# Etapa final con Nginx
FROM nginx:stable-alpine as production

# Configuración de Nginx optimizada para Cloud Run
COPY --from=builder /app/dist /usr/share/nginx/html
COPY ./docker/nginx-cloudrun.conf /etc/nginx/conf.d/default.conf

# Salud check para Cloud Run
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 8080
CMD ["nginx", "-g", "daemon off;"]