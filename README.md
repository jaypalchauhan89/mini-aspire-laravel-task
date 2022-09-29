## Installation Steps

```bash
composer install
```

```bash
cp .env.example .env
```

```bash
php artisan key:generate
```
setup database credentials in .env file

```bash
php artisan migrate
```

```bash
php artisan db:seed
```

```bash
php artisan serve
```

## Postman environment file (attached to email)

Default Base URL : example {{base_url}} = http://127.0.0.1:8000  use your url

## Postman collection link
https://www.getpostman.com/collections/2f16f2bf4524171721a8




### Test credential for admin
<b>Username:</b>admin@yopmail.com <br />
<b>Password:</b>1234567890

### Test credential for client 1
<b>Username:</b>customer1@yopmail.com <br />
<b>Password:</b>1234567890

### Test credential for client 2
<b>Username:</b>customer2@yopmail.com <br />
<b>Password:</b>1234567890

## For test cases

```bash
php artisan test
```
