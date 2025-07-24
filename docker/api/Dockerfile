# Použijeme image s PHP-FPM
FROM php:8.2-fpm

# Argumenty, které přijímáme z docker-compose.yml
ARG UID
ARG GID

# Instalace základních závislostí
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    # 'intl' rozšíření potřebuje knihovnu libicu-dev
    libicu-dev \
    sudo \
    # OPRAVA: Přidán balíček, který obsahuje utilitu 'pdftotext'
    poppler-utils \
    && rm -rf /var/lib/apt/lists/* \
    # Přidáme 'intl' do seznamu rozšíření k instalaci
    && docker-php-ext-install pdo_mysql zip intl

# Instalace Composeru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Vytvoření uživatele `appuser` s naším UID a GID
RUN groupadd -g $GID -o appuser
RUN useradd -u $UID -g $GID -o -m -s /bin/bash appuser
# Povolíme našemu appuser spouštět příkazy přes sudo bez hesla
RUN echo "appuser ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/appuser

# Nastavení pracovního adresáře
WORKDIR /var/www/html

# Přepnutí na našeho nového uživatele pro všechny následující operace
USER appuser

# Zde by se spouštěl entrypoint, pokud bychom ho měli
# Ale prozatím necháme prázdné, instalaci uděláme ručně

# CMD není potřeba, PHP-FPM se spustí automaticky
# Ale pro jistotu ho specifikujeme
CMD ["php-fpm"]