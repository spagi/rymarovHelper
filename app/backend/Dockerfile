FROM php:8.2-fpm

# Argumenty, které přijímáme z docker-compose.yml
ARG UID
ARG GID

# Instalace základních závislostí
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    sudo \
    # OPRAVA: Přidán balíček, který obsahuje utilitu 'pdftotext'
    poppler-utils \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql zip intl

# Instalace Composeru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Vytvoření uživatele `app_user` s naším UID a GID
RUN groupadd -g $GID -o app_group
RUN useradd -u $UID -g $GID -o -m -s /bin/bash app_user

# Povolíme našemu app_user spouštět příkazy přes sudo bez hesla
RUN echo "app_user ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/app_user

# Nastavení pracovního adresáře
WORKDIR /app

# Přepnutí na našeho nového uživatele pro všechny následující operace
USER app_user

CMD ["php-fpm"]
