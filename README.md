1. Clone repository
2. Deploy it on your webserver placing public/ as root directory
3. Generate app key php artisan key:generate
4. Set MySQL/Mariadb settings in .env file
5. Create symlink for storage access (php artisan storage:link)
6. Migrate all migrations (php artisan migrate)
7. (Optional) You can also use factories and seeders to seed randomizer data to db
