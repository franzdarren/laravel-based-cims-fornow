# CIMS — Clinic Inventory Management System

Laravel 11 + MySQL app for XAMPP. Login is by **email**, not username.

## How to run

1. Start Apache + MySQL in the XAMPP Control Panel.
2. Make sure a `cims` database exists (phpMyAdmin, or `mysql -u root -e "CREATE DATABASE cims"`).
3. From this folder:
   ```bash
   composer install        # only needed if vendor/ is missing
   php artisan migrate:fresh --seed   # rebuilds all tables + sample data
   php artisan serve
   ```
4. Open `http://127.0.0.1:8000`.

Re-run `php artisan migrate:fresh --seed` any time you want to wipe and reseed the database (e.g. after a schema change). It **drops all data** — only use it in dev.

## Demo accounts

All password `password`:

| Role          | Email                     |
|---------------|---------------------------|
| Administrator | avillanueva@clinic.local  |
| Nurse         | ncruz@clinic.local        |
| Supervisor    | mlim@clinic.local         |

## Notes

- Schema follows the team's ERD (singular table names: `user`, `role`, `supplier`, etc.), with a few small additions where the ERD was missing something an existing feature needed (e.g. `supplier.status` for soft-delete, a `CANCELLED` receiving status, a `receiving_transaction_line` table for draft/returned request lines). See inline migration comments for each.
- Disposals aren't a separate table — they're `transaction_log` rows with `transaction_type = DISPOSAL`.
- If `php artisan serve` port 8000 is already in use, add `--port=8001` (or any free port).
