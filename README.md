# Rymarov Helper

This project consists of a Symfony (PHP) backend and a Next.js (React) frontend, designed to work together using Docker Compose.

## Project Structure

- `backend/`: Symfony application (PHP, Doctrine, API Platform)
- `frontend/`: Next.js application
- `Caddyfile`: Caddy web server configuration
- `docker-compose.yml`: Docker Compose configuration for the entire application

## Getting Started

To get the project up and running, follow these steps:

### 1. Build and Run Docker Containers

Navigate to the root of the project and run Docker Compose:

```bash
docker-compose up --build -d
```

This command will:
- Build the Docker images for both `backend` and `frontend` services.
- Start all services defined in `docker-compose.yml` in detached mode.

### 2. Backend Setup (Symfony)

Once the backend container is running, you might need to perform some initial setup:

#### Install Composer Dependencies

```bash
docker-compose exec backend composer install
```

#### Run Database Migrations

```bash
docker-compose exec backend php bin/console doctrine:migrations:migrate
```

#### Import Open Data (Example)

To run the data import command:

```bash
docker-compose exec backend php bin/console app:import-bulletin-board
```

### 3. Frontend Setup (Next.js)

The frontend should be accessible via Caddy.

#### Install NPM Dependencies

```bash
docker-compose exec frontend npm install
```

### 4. Access the Application

- **Frontend:** Accessible via Caddy, typically at `http://localhost` (or as configured in your `Caddyfile`).
- **Backend API:** Accessible via Caddy, typically at `http://localhost/api` (or as configured in your `Caddyfile`).

## Development

### Stopping the Application

To stop all running Docker containers:

```bash
docker-compose down
```

### Rebuilding Containers

If you make changes to `Dockerfile`s or `docker-compose.yml`, you might need to rebuild:

```bash
docker-compose up --build -d
```

## Troubleshooting

- If you encounter issues with PHP versions, ensure your `Dockerfile` for the backend specifies the correct PHP version and rebuild the container.
- Check Docker logs for specific service errors: `docker-compose logs <service_name>`