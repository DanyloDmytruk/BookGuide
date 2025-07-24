1. Clone repository
2. Deploy it on your webserver placing public/ as root directory
3. Generate app key php artisan key:generate
4. Set MySQL/Mariadb settings in .env file
5. Create symlink for storage access (php artisan storage:link)
6. Migrate all migrations (php artisan migrate)
7. (Optional) You can also use factories and seeders to seed randomizer data to db
8. Now you can access your website! The default Laravel Auth system was chosen, so firstly you must register and log in.

Demo screenshots:
<img width="1354" height="685" alt="image" src="https://github.com/user-attachments/assets/f6cc8d42-d8aa-4c24-b3ef-698da4318eca" />
<img width="1341" height="631" alt="image" src="https://github.com/user-attachments/assets/eca6314d-4e1a-4aa9-9841-698dfd9c72c1" />
<img width="1356" height="678" alt="image" src="https://github.com/user-attachments/assets/5bf66458-3e94-4c46-82df-125182a470eb" />
<img width="1344" height="632" alt="image" src="https://github.com/user-attachments/assets/71ee6d41-099a-4b69-89fb-2d4b307d3b29" />


