## Laravel Translation Report

This package registers an Artisan Command to produce a simple CSV report of missing translations between languages.

Basic usage:

```bash
php artisan translation:report
```

To exclude specific files, use one or more `--exclude`:

```bash
php artisan translation:report --exclude=notme.php --exclude=orme.php
```
