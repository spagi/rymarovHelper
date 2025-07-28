# docker/frontend/Dockerfile

# Použijeme oficiální Node.js image
FROM node:18-alpine

# Nainstalujeme pnpm globálně
RUN npm install -g pnpm

# Nastavíme pracovní adresář
WORKDIR /app/frontend

# Zkopírujeme soubory definující závislosti
COPY ./app/frontend/package.json ./

# Nainstalujeme závislosti pomocí pnpm
RUN pnpm install

# Zkopírujeme zbytek aplikace
COPY ./app/frontend/ .

# Vystavíme port
EXPOSE 3000

# Spustíme vývojový server pomocí pnpm
CMD ["pnpm", "run", "dev"]