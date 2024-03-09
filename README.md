1. <b>Install project</b>

```bash
composer install
```

2. <b>Create .env</b>

```bash
cp .env.example .env
```

3. <b>Key generate</b>

```bash
php artisan key:generate
```

4. <b>Setup database in .env</b>

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

5. <b>Migrate database</b>

```bash
php artisan migrate
```

6. <b>Install npm packages</b>

```bash
npm install
```

7. <b>Run laravel server</b>

```bash
php artisan serve
```

8. <b>Run vite server</b>

```bash
npm run dev
```

9. <b>Open in your browser</b>

```
    http://127.0.0.1:8000
```
