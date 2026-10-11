# Run NMC Clinic with Docker

The multi-stage Dockerfile builds PHP/Apache with PDO MySQL and mbstring.
Compose publishes the app on all network interfaces. MySQL stays on the internal
Docker network, and phpMyAdmin is accessible only on localhost.

Use the existing `.env`, or copy `.env.example` to `.env` if it does not exist.
Set `COMPOSE_DB_PASS` and `COMPOSE_DB_ROOT_PASS` to different strong passwords.
Keep `.env` out of Git. Docker uses `COMPOSE_DB_*`; the `NMC_DB_*` settings
in `.env` remain available for XAMPP.

From this folder:

```powershell
docker compose config --quiet
docker compose up -d --build
docker compose ps
```

- Website: http://localhost:8082
- CMS: http://localhost:8082/cms/login.php
- phpMyAdmin: http://localhost:8083 (use `COMPOSE_DB_USER` and `COMPOSE_DB_PASS`)

Other devices can open `http://<server-ip>:8082`. For internet access, allow the
website port through the host firewall and configure router forwarding if needed.
Use HTTPS through a reverse proxy for a public deployment.

MySQL imports `db.sql` only when the database volume is empty.
The SQL file seeds the CMS login `admin@nmcclinic.com` / `admin123`.
Change this default password before making the app accessible beyond localhost.
This initializes the supplied schema and seed data; it does not copy your XAMPP database.

Database data and uploads persist in named volumes. Existing files in `uploads`
are copied into the upload volume when it is first created.
Rebuild after changing source files. Later image changes do not replace existing
upload-volume files; copy new media into that volume separately.

```powershell
docker compose logs --tail=100 web db
docker compose down
```

`down` preserves data. Adding `-v` deletes the database and uploads.
Changes to `db.sql` are not automatically applied to an existing database;
apply later database migrations separately. Changing `.env` does not reset
passwords in an existing database.

Set `PORT` and `PHPMYADMIN_PORT` in `.env` to change ports. Set `COMPOSE_SITE_URL`
if using a hostname other than localhost. The defaults do not conflict with
the `lubricant` stack on ports 8080 and 8081.
