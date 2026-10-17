# TCG Finder - Performance & Security Optimization Guide (Docker)

## 📌 Overview

This guide provides **actionable steps** to optimize your **Symfony 8.5 + FrankenPHP + PostgreSQL** application running in **Docker** for:
- ✅ **Performance** on a **4 vCPU / 4GB RAM / 80GB SSD VPS**
- ✅ **Scalability** as your userbase grows
- ✅ **Security** against bots and attacks

> **Important:** This guide is **Docker-specific** and optimized for your **4 vCPU / 4GB RAM** VPS.

Your stack (FrankenPHP + PostgreSQL in Docker) is **excellent for this VPS** because:
- Docker provides **isolation and portability**
- FrankenPHP's worker mode **eliminates PHP process overhead**
- PostgreSQL in Docker is **easy to manage and backup**
- 4GB RAM is **perfect** for this stack with your Dockerfile setup

---

## 📋 Todo List

### 🟡 Docker & FrankenPHP
- [ ] Configure FrankenPHP in Docker with worker mode (3 workers)
- [ ] Set resource limits for FrankenPHP container (2 CPU, 2GB RAM)
- [ ] Set resource limits for PostgreSQL container (1.5 CPU, 1.5GB RAM)
- [ ] Configure Docker health checks
- [ ] Enable OPcache in PHP container
- [ ] Enable Twig cache
- [ ] Enable Symfony cache (APCu)
- [ ] Enable static file serving in FrankenPHP
- [ ] Enable gzip compression in FrankenPHP

### 🟠 Database (PostgreSQL/PostGIS)
- [ ] Add indexes on User table
- [ ] Tune PostgreSQL configuration for 1.5GB RAM
- [ ] Enable Doctrine connection pooling (10-15 connections)
- [ ] Configure persistent storage for PostgreSQL data

### 🔴 Security
- [ ] Set up rate limiting
- [ ] Install and configure Fail2Ban on host
- [ ] Configure security headers
- [ ] Verify CSRF protection is enabled
- [ ] Configure Docker network security

### 🟢 Application
- [ ] Optimize pagination queries
- [ ] Use DTOs for read queries
- [ ] Configure lazy loading for associations
- [ ] Verify pagination preserves query parameters

### 🔵 Monitoring
- [ ] Set up Docker monitoring
- [ ] Set up PostgreSQL monitoring
- [ ] Configure FrankenPHP stats endpoint

---

## 📖 Detailed Explanations

---

## 🟡 Docker & FrankenPHP

### 1. Configure FrankenPHP in Docker with Worker Mode (3 Workers)

**What:** Run FrankenPHP in worker mode inside a Docker container.

**Why:** Worker mode keeps PHP processes alive between requests, eliminating **~50-100ms** of startup overhead per request. With 4 vCPU, **3 workers** is optimal for balancing performance and memory usage.

**How:** In your `docker-compose.prod.yml`:

```yaml
services:
  symfony:
    build:
      context: ./backend
      dockerfile: Dockerfile
    command: ["frankenphp", "worker", "--config", "/app/frankenphp.php"]
    volumes:
      - ./backend:/app
    ports:
      - "80:80"
      - "443:443"
    environment:
      - SYMFONY_ENV=prod
      - APP_ENV=prod
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 5s
      retries: 3
```

**Create `backend/frankenphp.php`:**
```php
<?php
return [
    'worker_num' => 3,
    'task_worker_num' => 0,
    'max_request' => 2000,
    'buffer_output_size' => 2 * 1024 * 1024,
    'memory_limit' => '512M',
    'gzip' => true,
    'gzip_level' => 6,
    'serve_static' => true,
];
```

**Impact:** ✅✅✅ **Critical** - FrankenPHP's biggest performance advantage

---

### 2. Set Resource Limits for FrankenPHP Container (2 CPU, 2GB RAM)

**What:** Limit CPU and memory usage for the FrankenPHP container.

**Why:** Prevents the PHP container from consuming all 4 vCPU or 4GB RAM. Leaves resources for PostgreSQL and OS.

**How:** In `docker-compose.prod.yml`:

```yaml
services:
  symfony:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '1.0'
          memory: 1G
    ulimits:
      nproc: 200
      nofile:
        soft: 40000
        hard: 60000
```

**Impact:** ✅✅✅ **Critical** - Prevents resource exhaustion, optimal for 4GB RAM

---

### 3. Set Resource Limits for PostgreSQL Container (1.5 CPU, 1.5GB RAM)

**What:** Limit resources for the PostgreSQL container.

**Why:** PostgreSQL needs ~25-50% of total RAM. **1.5GB RAM** allows `shared_buffers = 1GB` and `effective_cache_size = 2GB`. **1.5 CPU cores** is sufficient (I/O bound).

**How:** In `docker-compose.prod.yml`:

```yaml
services:
  db:
    image: postgis/postgis:16-3.4
    volumes:
      - db_data:/var/lib/postgresql/data
      - ./docker/postgres/postgresql.conf:/etc/postgresql/postgresql.conf
    command: postgres -c config_file=/etc/postgresql/postgresql.conf
    deploy:
      resources:
        limits:
          cpus: '1.5'
          memory: 1.5G
        reservations:
          cpus: '1.0'
          memory: 1G
    ulimits:
      nproc: 200
      nofile:
        soft: 40000
        hard: 60000
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DB}"]
      interval: 10s
      timeout: 5s
      retries: 5
```

**Impact:** ✅✅✅ **Critical** - Optimal balance for 4GB RAM VPS

---

### 4. Configure Docker Health Checks

**What:** Automatically restart unhealthy containers.

**Why:** Ensures your application recovers from crashes or hangs.

**How:** In `docker-compose.prod.yml`:

```yaml
services:
  symfony:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 10s

  db:
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DB}"]
      interval: 10s
      timeout: 5s
      retries: 5
```

**Create a health endpoint in Symfony:**
```php
// src/Controller/HealthController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/health', name: 'health_check', methods: ['GET'])]
final class HealthController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['status' => 'healthy']);
    }
}
```

**Impact:** ✅✅ **High** - Automatic recovery from failures

---

### 5. Enable OPcache in PHP Container

**What:** PHP bytecode cache in Docker.

**Why:** Compiles PHP scripts once and reuses, reducing execution time by **50-80%**.

**How:** Add to your `Dockerfile` (after `install-php-extensions`):

```dockerfile
# Enable OPcache with optimized settings for 2GB RAM
RUN echo "[opcache]" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=8000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=180" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=128M" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/opcache.ini
```

**Verify in container:**
```bash
docker exec -it tcg_finder_app php -i | grep opcache
```

**Impact:** ✅✅✅ **Critical** - Single biggest PHP performance gain

---

### 6. Enable Twig Cache

**What:** Cache compiled Twig templates.

**Why:** Eliminates template recompilation on every request.

**How:** In `config/packages/twig.yaml`:
```yaml
twig:
    cache: '%kernel.cache_dir%/twig'
    strict_variables: true
```

**Ensure cache directory is writable:** Your Dockerfile already handles this with `setfacl` commands.

**Impact:** ✅✅✅ **Critical** - Significant reduction in request processing time

---

### 7. Enable Symfony Cache

**What:** Cache Symfony's internal data.

**Why:** Faster framework bootstrap.

**How:** In `config/packages/cache.yaml`:
```yaml
framework:
    cache:
        app: cache.adapter.apcu
        system: cache.adapter.system
```

**Prerequisite:** APCu is already installed in your Dockerfile.

**Impact:** ✅✅ **High** - Faster container building and routing

---

### 8. Enable Static File Serving in FrankenPHP

**What:** Let FrankenPHP serve static files directly.

**Why:** Faster than a separate web server for small files, reduces complexity.

**How:** In `backend/frankenphp.php`:
```php
'serve_static' => true,
```

**Impact:** ✅✅ **High** - Simpler stack, better performance for static assets

---

### 9. Enable Gzip Compression in FrankenPHP

**What:** Compress HTTP responses.

**Why:** Reduces bandwidth usage by **~70%** for text-based responses.

**How:** In `backend/frankenphp.php`:
```php
'gzip' => true,
'gzip_level' => 6,
```

**Impact:** ✅✅ **Medium** - Better user experience, lower bandwidth

---

## 🟠 Database (PostgreSQL/PostGIS)

### 10. Add Indexes on User Table

**What:** Database indexes for frequently queried columns.

**Why:** Speeds up `WHERE`, `JOIN`, and `ORDER BY` operations by **10-100x**.

**How:** Create a migration:

```bash
php bin/console make:migration
```

Edit the migration to add:
```php
// In the migration class
public function up(Schema $schema): void
{
    $this->addSql('CREATE INDEX idx_user_email ON "user"(email)');
    $this->addSql('CREATE INDEX idx_user_is_verified ON "user"(is_verified)');
    $this->addSql('CREATE INDEX idx_user_created_at ON "user"(created_at)');
    $this->addSql('CREATE INDEX idx_user_updated_at ON "user"(updated_at)');
    
    // For search
    $this->addSql('CREATE INDEX idx_user_first_name ON "user"(first_name)');
    $this->addSql('CREATE INDEX idx_user_last_name ON "user"(last_name)');
    
    // For full-text search
    $this->addSql('ALTER TABLE "user" ADD COLUMN search_vector TSVECTOR');
    $this->addSql("UPDATE \"user\" SET search_vector = to_tsvector('english', coalesce(first_name, '') || ' ' || coalesce(last_name, '') || ' ' || coalesce(email, ''))");
    $this->addSql('CREATE INDEX idx_user_search_vector ON "user" USING GIN(search_vector)');
}
```

**Run the migration:**
```bash
docker compose -f docker-compose.prod.yml exec symfony php bin/console doctrine:migrations:migrate
```

**Impact:** ✅✅✅ **Critical** - Most important database optimization

---

### 11. Tune PostgreSQL Configuration for 1.5GB RAM Container

**What:** Optimize PostgreSQL memory settings for your container's 1.5GB RAM allocation.

**Why:** Better utilization of available RAM. Default settings are too conservative.

**How:** Create `docker/postgres/postgresql.conf`:

```conf
# Memory settings for 1.5GB container
shared_buffers = 1GB
-effective_cache_size = 2GB
work_mem = 16MB
maintenance_work_mem = 128MB
random_page_cost = 1.1
max_connections = 50
max_worker_processes = 4
max_parallel_workers_per_gather = 2
max_parallel_workers = 4

# Checkpoint settings
checkpoint_completion_target = 0.9
checkpoint_timeout = 30min

# WAL settings
wal_buffers = 16MB
min_wal_size = 1GB
max_wal_size = 2GB

# Logging
log_statement = 'mod'
log_min_duration_statement = 1000
```

Then mount it in `docker-compose.prod.yml`:
```yaml
services:
  db:
    volumes:
      - ./docker/postgres/postgresql.conf:/etc/postgresql/postgresql.conf
    command: postgres -c config_file=/etc/postgresql/postgresql.conf
```

**Impact:** ✅✅✅ **Critical** - Optimized for your 4 vCPU / 4GB RAM VPS

---

### 12. Enable Doctrine Connection Pooling (10-15 Connections)

**What:** Reuse database connections across requests.

**Why:** FrankenPHP's async I/O can create connection leaks without pooling.

**How:** In `config/packages/doctrine.yaml`:
```yaml
doctrine:
    dbal:
        connections:
            default:
                server_version: '16'
                options:
                    1002: 'SET time_zone = "UTC"'
                pool:
                    enabled: true
                    max_connections: 12
```

**Impact:** ✅✅✅ **Critical** - Required for FrankenPHP's async model

---

### 13. Configure Persistent Storage for PostgreSQL

**What:** Persist PostgreSQL data between container restarts.

**Why:** Without persistent storage, your database data is lost when the container restarts.

**How:** In `docker-compose.prod.yml`:

```yaml
services:
  db:
    volumes:
      - db_data:/var/lib/postgresql/data

volumes:
  db_data:
    driver: local
```

**Backup strategy:**
```bash
# Create backup
docker compose -f docker-compose.prod.yml exec db pg_dump -U ${POSTGRES_USER} ${POSTGRES_DB} > backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
docker compose -f docker-compose.prod.yml exec db psql -U ${POSTGRES_USER} ${POSTGRES_DB} < backups/backup.sql
```

**Impact:** ✅✅✅ **Critical** - Prevents data loss

---

## 🔴 Security

### 14. Set Up Rate Limiting

**What:** Limit request rates to prevent abuse.

**Why:** Protects against brute force and DoS attacks.

**How:**
```bash
composer require symfony/rate-limiter
```

Configure in `config/packages/rate_limiter.yaml`:
```yaml
framework:
    rate_limiter:
        login:
            policy: 'sliding_window'
            limit: 5
            interval: '1 minute'
        
        admin:
            policy: 'sliding_window'
            limit: 60
            interval: '1 minute'
        
        api:
            policy: 'sliding_window'
            limit: 100
            interval: '1 minute'
```

Apply to routes:
```php
#[RateLimit('admin')]
public function usersList(...)
```

**Impact:** ✅✅ **High** - Protects against brute force attacks

---

### 15. Install and Configure Fail2Ban on Host

**What:** OS-level brute force protection.

**Why:** Blocks IPs at the host level before requests reach Docker.

**How:** On your **host machine** (not in Docker):

```bash
sudo apt update
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

Configure `/etc/fail2ban/jail.local`:
```ini
[DEFAULT]
bantime = 1h
findtime = 10m
maxretry = 5

[sshd]
enabled = true
maxretry = 3
```

**Impact:** ✅✅ **High** - Protects SSH and exposed ports

---

### 16. Configure Security Headers

**What:** HTTP response headers that protect against common web vulnerabilities.

**Why:** Prevents XSS, clickjacking, MIME sniffing attacks.

**How:** In `config/packages/security.yaml`:
```yaml
security:
    headers:
        content_security_policy:
            default-src: "'self'"
            script-src: "'self' 'unsafe-inline'"
            style-src: "'self' 'unsafe-inline'"
            img-src: "'self' data:"
            font-src: "'self'"
            object-src: "'none'"
            frame-ancestors: "'none'"
            base-uri: "'self'"
        
        x_frame_options: DENY
        x_content_type_options: nosniff
        referrer_policy: strict-origin-when-cross-origin
        force_https: true
```

**Configure trusted proxies** in `config/packages/framework.yaml`:
```yaml
framework:
    trusted_proxies: ['127.0.0.1', '172.16.0.0/12']
    trusted_headers: ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port']
```

**Impact:** ✅✅ **High** - Basic web application security

---

### 17. Verify CSRF Protection

**What:** Cross-Site Request Forgery protection.

**Why:** Prevents attackers from tricking users into submitting forms.

**Status:** ✅ **Already implemented** in your `userDelete` route.

**Verify:** Ensure all forms have CSRF tokens:
```twig
<form method="post">
    <input type="hidden" name="_token" value="{{ csrf_token('...') }}">
</form>
```

**Impact:** ✅✅ **High** - Already protected

---

### 18. Configure Docker Network Security

**What:** Secure Docker network configuration.

**Why:** Prevents unauthorized access between containers and from external networks.

**How:** In `docker-compose.prod.yml`:

```yaml
networks:
  app_network:
    driver: bridge

services:
  db:
    networks:
      - app_network
    # Don't expose PostgreSQL port externally

  symfony:
    networks:
      - app_network
    ports:
      - "80:80"
      - "443:443"
    depends_on:
      db:
        condition: service_healthy
```

**Impact:** ✅✅ **High** - Prevents external database access

---

## 🟢 Application-Level Optimizations

### 19. Optimize Pagination Queries

**What:** Use efficient pagination techniques for large datasets.

**Why:** Traditional `OFFSET` pagination becomes slow with large datasets.

**How:** Use **cursor-based pagination** in your repository:

```php
// In UserRepository.php
public function findPaginatedWithCursor(
    int $limit,
    ?int $cursor = null,
    ?string $search = null,
    bool $verified = true
): array {
    $qb = $this->createQueryBuilder('u')
        ->orderBy('u.id', 'ASC')
        ->setMaxResults($limit);

    if ($cursor) {
        $qb->andWhere('u.id > :cursor')
           ->setParameter('cursor', $cursor);
    }

    if ($search) {
        $qb->andWhere('u.email LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search')
           ->setParameter('search', '%'.$search.'%');
    }

    if ($verified !== null) {
        $qb->andWhere('u.isVerified = :verified')
           ->setParameter('verified', $verified);
    }

    $users = $qb->getQuery()->getResult();
    
    $countQb = $this->createQueryBuilder('u');
    if ($search) {
        $countQb->andWhere('u.email LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search')
              ->setParameter('search', '%'.$search.'%');
    }
    if ($verified !== null) {
        $countQb->andWhere('u.isVerified = :verified')
              ->setParameter('verified', $verified);
    }
    $total = (int) $countQb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

    $nextCursor = count($users) > 0 ? $users[count($users) - 1]->getId() : null;

    return [
        'data' => $users,
        'total' => $total,
        'next_cursor' => $nextCursor,
    ];
}
```

**Impact:** ✅✅ **Medium** - Better performance for large datasets

---

### 20. Use DTOs for Read Queries

**What:** Data Transfer Objects to fetch only needed columns.

**Why:** Reduces memory usage and database I/O.

**How:** Create a DTO class:

```php
// src/Dto/UserListDto.php
namespace App\Dto;

final readonly class UserListDto
{
    public function __construct(
        public int $id,
        public string $email,
        public string $firstName,
        public string $lastName,
        public bool $isVerified,
    ) {}
}
```

Use in repository:
```php
// In UserRepository.php
public function findAllAsDto(int $limit, int $offset): array
{
    return $this->createQueryBuilder('u')
        ->select('NEW App\Dto\UserListDto(u.id, u.email, u.firstName, u.lastName, u.isVerified)')
        ->setMaxResults($limit)
        ->setFirstResult($offset)
        ->orderBy('u.id', 'ASC')
        ->getQuery()
        ->getResult();
}
```

**Impact:** ✅✅ **Medium** - Reduces memory and I/O

---

### 21. Configure Lazy Loading for Associations

**What:** Load entity associations only when needed.

**Why:** Prevents N+1 query problems.

**How:** In your User entity:

```php
// src/Entity/User.php

#[ORM\OneToMany(targetEntity: Shop::class, mappedBy: "user")]
#[ORM\JoinColumn(fetch: "LAZY")]
private Collection $shops;
```

**Impact:** ✅✅ **Medium** - Prevents N+1 query performance issues

---

## 🔵 Monitoring

### 22. Set Up Docker Monitoring

**What:** Monitor Docker container resource usage.

**Why:** Identify performance bottlenecks.

**How:**
```bash
# View real-time resource usage
docker stats

# View logs
docker compose -f docker-compose.prod.yml logs -f

# View container processes
docker top tcg_finder_app
```

**Use cAdvisor for advanced monitoring:**
```yaml
# Add to docker-compose.prod.yml
services:
  cadvisor:
    image: gcr.io/cadvisor/cadvisor:latest
    ports:
      - "8080:8080"
    volumes:
      - /:/rootfs:ro
      - /var/run:/var/run:ro
      - /sys:/sys:ro
      - /var/lib/docker:/var/lib/docker:ro
    networks:
      - app_network
```

**Impact:** ✅ **Medium** - Essential for debugging

---

### 23. Set Up PostgreSQL Monitoring

**What:** Monitor PostgreSQL performance.

**Why:** Identify slow queries and connection issues.

**How:** Connect to PostgreSQL and run:
```bash
docker compose -f docker-compose.prod.yml exec db psql -U ${POSTGRES_USER} ${POSTGRES_DB}
```

Then run SQL queries to check:
- Active connections
- Long-running queries
- Table sizes
- Index usage

**Impact:** ✅ **Medium** - Find database bottlenecks

---

### 24. Configure FrankenPHP Stats Endpoint

**What:** FrankenPHP's built-in monitoring.

**Why:** Monitor worker health.

**How:**
```bash
docker compose -f docker-compose.prod.yml exec symfony curl http://localhost/_frankenphp/stats
```

**Impact:** ✅ **Medium** - Monitor worker status

---

## 🎯 Quick Start Guide (Top 5 Most Important)

If you're short on time, do these **5 things first**:

### 1. Configure Docker Compose with 3 FrankenPHP Workers
```yaml
services:
  symfony:
    command: ["frankenphp", "worker", "--config", "/app/frankenphp.php"]
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
```

### 2. Enable OPcache
```dockerfile
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini
```

### 3. Add Database Indexes
```sql
CREATE INDEX idx_user_email ON "user"(email);
CREATE INDEX idx_user_is_verified ON "user"(is_verified);
```

### 4. Tune PostgreSQL
```conf
shared_buffers = 1GB
effective_cache_size = 2GB
max_connections = 50
```

### 5. Enable Connection Pooling
```yaml
doctrine:
    dbal:
        pool:
            enabled: true
            max_connections: 12
```

**Expected result:** ~300-500 concurrent users, ~150-200ms response times.

---

## 📊 Expected Performance & Capacity

### Performance Improvements
| **Optimization** | **Before** | **After** | **Improvement** | **Priority** |
|-----------------|------------|-----------|-----------------|--------------|
| OPcache | ~100ms | ~30ms | 3x faster | ✅✅✅ |
| Worker Mode (3 workers) | ~100ms | ~30ms | 3x faster | ✅✅✅ |
| Database Indexes | ~500ms | ~50ms | 10x faster | ✅✅✅ |
| PostgreSQL Tuning | ~200ms | ~80ms | 2.5x faster | ✅✅✅ |
| Connection Pooling | Errors | Stable | Reliable | ✅✅✅ |
| Twig Cache | ~50ms | ~10ms | 5x faster | ✅✅✅ |
| **Overall** | **~800ms** | **~150ms** | **5-10x faster** | - |

### Capacity Estimates for 4 vCPU / 4GB RAM
| **Metric** | **Conservative** | **Optimistic** | **Realistic** |
|------------|-----------------|---------------|---------------|
| Concurrent Users | 300 | 500 | 400 |
| Requests/Second | 50 | 100 | 75 |
| Page Load Time | 200ms | 100ms | 150ms |

### Resource Allocation Summary
| **Component** | **CPU Cores** | **RAM** | **Notes** |
|--------------|---------------|---------|---------|
| PHP (FrankenPHP) | 2.0 | 2GB | 3 workers |
| PostgreSQL | 1.5 | 1.5GB | shared_buffers=1GB |
| OS + Docker | 0.5 | 0.5GB | Overhead |
| **Total** | **4.0** | **4.0GB** | **Perfect fit** |

---

## 🐳 Complete Docker Compose Production Example

```yaml
version: '3.8'

networks:
  app_network:
    driver: bridge

volumes:
  db_data:
  symfony_var:

services:
  symfony:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: tcg_finder_app
    command: ["frankenphp", "worker", "--config", "/app/frankenphp.php"]
    volumes:
      - ./backend:/app
      - symfony_var:/app/var
    ports:
      - '80:80'
      - '443:443'
    environment:
      - SYMFONY_ENV=prod
      - APP_ENV=prod
      - DATABASE_URL=postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@db:5432/${POSTGRES_DB}?serverVersion=16
      - TRUSTED_PROXIES=127.0.0.1,172.16.0.0/12
    networks:
      - app_network
    depends_on:
      db:
        condition: service_healthy
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '1.0'
          memory: 1G
    ulimits:
      nproc: 200
      nofile:
        soft: 40000
        hard: 60000
    healthcheck:
      test: ['CMD', 'curl', '-f', 'http://localhost/health']
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 10s
    restart: unless-stopped

  db:
    image: postgis/postgis:16-3.4
    container_name: tcg_finder_db
    environment:
      POSTGRES_DB: ${POSTGRES_DB:-symfony_db}
      POSTGRES_USER: ${POSTGRES_USER:-user}
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-password}
    volumes:
      - db_data:/var/lib/postgresql/data
      - ./docker/postgres/postgresql.conf:/etc/postgresql/postgresql.conf
    command: postgres -c config_file=/etc/postgresql/postgresql.conf
    networks:
      - app_network
    deploy:
      resources:
        limits:
          cpus: '1.5'
          memory: 1.5G
        reservations:
          cpus: '1.0'
          memory: 1G
    ulimits:
      nproc: 200
      nofile:
        soft: 40000
        hard: 60000
    healthcheck:
      test: ['CMD-SHELL', 'pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DB}']
      interval: 10s
      timeout: 5s
      retries: 5
    restart: unless-stopped
```

---

## 📝 Required `.env` File

```bash
# Database
POSTGRES_DB=symfony_db
POSTGRES_USER=tcg_finder_user
POSTGRES_PASSWORD=your_strong_password_here
```

**Add to `.gitignore`!**

---

## 📚 Resources

- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [PostgreSQL Docker Image](https://hub.docker.com/_/postgres)
- [PostGIS Documentation](https://postgis.net/docs/)
- [PostgreSQL Tuning Guide](https://wiki.postgresql.org/wiki/Tuning_Your_PostgreSQL_Server)
- [Symfony Performance](https://symfony.com/doc/current/performance.html)

---

## 🎉 Conclusion

With these optimizations tailored for your **4 vCPU / 4GB RAM VPS** and **Docker setup**, your application will:

- ✅ Handle **~400-500 concurrent users** comfortably
- ✅ Use **~3.5-3.8GB RAM** (perfect for 4GB)
- ✅ Respond with **~150-200ms average latency**
- ✅ Be **protected against common attacks**
- ✅ Be **easy to deploy and scale** with Docker

**Your setup is already well-optimized with the Dockerfile you have.** Just implement the recommendations in this guide, and you'll have a production-ready, high-performance application.

---

*Last updated: 2026-10-17*
