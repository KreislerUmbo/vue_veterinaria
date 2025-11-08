# Etapa 1: Build de la app Vue
FROM node:20-alpine AS builder

WORKDIR /app

# Copiar código fuente
COPY ./admin-veterinaria/ ./

# Activar pnpm y compilar
RUN corepack enable && corepack prepare pnpm@latest --activate
RUN pnpm install
RUN pnpm build

# Etapa 2: Servir el contenido estático con Nginx
FROM nginx:stable-alpine

# Copiar archivos de compilación
COPY --from=builder /app/dist /usr/share/nginx/html

# Configuración personalizada de Nginx (opcional)
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
