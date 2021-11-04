# Blog website | Laravel 8

This is an update to: https://github.com/Ruslan-Aliyev/Laravel_CRUD_API (Laravel 5)

The mobile app to this: https://github.com/atabegruslan/Flutter_CRUD_API

# Dummy accounts

| Username | Password |
| --- | --- |
| ruslanaliyev1849@gmail.com | 12345678 |

---

# API

## Users

### Get access token

POST `http://ruslan-website.com/laravel5/travel_blog/oauth/token`

| Post Form Data Name | Post Form Data Value |
| --- | --- |
| client_id | (from oauth_clients table. 1 is Personal, 2 is password) |
| client_secret | (from oauth_clients table) |
| grant_type | (personal or password) |
| username | (user email) |
| password | (user password) |
| type | 'normal' or 'facebook' or 'google' |

Return access token

### Get user data

GET `http://ruslan-website.com/laravel5/travel_blog/api/user`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

Return user data

### Insert new user

POST `http://ruslan-website.com/laravel5/travel_blog/api/user`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

| Post Form Data Name | Post Form Data Value |
| --- | --- |
| name | Name |
| email | name@email.com |
| password | abcdef |
| type | 'normal' or 'facebook' or 'google' |
| social_id | (optional) |

Return OK or Error response

## Entries

### Get all entries

GET `http://ruslan-website.com/laravel5/travel_blog/api/entry`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

Return all entries

### Get one entry

GET `http://ruslan-website.com/laravel5/travel_blog/api/entry/{id}`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

Return one entry

### Create entry

POST `http://ruslan-website.com/laravel5/travel_blog/api/entry`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

| Post Form Data Name | Post Form Data Value |
| --- | --- |
| user_id | (user id, int) |
| place | (place name, string) |
| comments | (comments, string) |
| image | (image, file, optional) |

Return OK or Error response

### Update entry

POST `http://ruslan-website.com/laravel5/travel_blog/api/entry/{id}`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

| Post Form Data Name | Post Form Data Value |
| --- | --- |
| _method | PUT |
| user_id | (user id, int, optional) |
| place | (place name, string, optional) |
| comments | (comments, string, optional) |
| image | (image, file, optional) |

Return OK or Error response

### Delete entry

POST `http://ruslan-website.com/laravel5/travel_blog/api/entry/{id}`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

| Post Form Data Name | Post Form Data Value |
| --- | --- |
| _method | DELETE |

Return OK or Error response

---

# How to make this app

## Beginnings

`composer create-project --prefer-dist laravel/laravel travel_blog`

1. New Controller for Entry: `php artisan make:controller Web/EntryController --resource`

2. New Entry Model: `php artisan make:model Models/Entry`

3. `routes/web.php`: `Route::resource('/entry', EntryController::class);`

4. Complete EntryController:

5. Complete `resources/views/entry/*.blade.php` views.

For text and image multipart upload form - You can use LaravelCollective: `composer require laravelcollective/html`

## Auth (Web)

~~Create default database tables for user: `php artisan migrate`~~

~~Make route, controller and model for user: `php artisan make:auth`~~

```
composer require laravel/ui
php artisan ui vue --auth
php artisan migrate
```

### Ensure login before accessing route

Either add this to controller
```php
public function __construct(){
	$this->middleware('auth');
}
```

Or add this to route
`Route::middleware('auth')->resource('/entry', 'EntryController')`

## Auth (API)

```
composer require laravel/passport
php artisan migrate
php artisan passport:install
```

https://laravel.com/docs/8.x/passport#installation

Now you can get access token: POST `.../oauth/token` https://github.com/atabegruslan/Laravel_CRUD_API#get-access-token

`routes/api.php`: 
```php
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
```

Now you can get user data: GET `.../api/user` https://github.com/atabegruslan/Laravel_CRUD_API#get-user-data

`routes/api.php`: 
```php
Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('/entry', 'EntryController');
});
```

Now you must do Entry CRUDs with access token https://github.com/atabegruslan/Laravel_CRUD_API#entries

## Permissions (Spatie library)

1. `composer require spatie/laravel-permission`
2. Create migration and config files: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
    - Or create seperately by:
        - Migration: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"`
        - Config: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"`
3. `php artisan migrate`
4. Make User model use `Spatie\Permission\Traits\HasRoles`
5. Add `features` to `config/permission.php`: https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/config/permission.php#L162
6. `php artisan make:seed SyncPermissionTableSeeder` and do like: https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/database/seeds/SyncPermissionTableSeeder.php
7. Do MVC for User, Role and Permission
6. Run seeder: `php artisan db:seed --class=SyncPermissionTableSeeder` to populate `permissions` table from `config/permission.php`'s `features` part.
8. Make use of:
```php
//A permission can be assigned to a role using 1 of these methods:
$role->givePermissionTo($permission);
$permission->assignRole($role);
//Multiple permissions can be synced to a role using 1 of these methods:
$role->syncPermissions($permissions);
$permission->syncRoles($roles);
//A permission can be removed from a role using 1 of these methods:
$role->revokePermissionTo($permission);
$permission->removeRole($role);

$user->assignRole($roles);
$user->removeRole($role);
$user->syncRoles($roles);
```
9. Check permissions by:
```php
auth()->user()->hasRole($roles);
auth()->user()->can($permission);
```

# Further notes:

https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/notes.md
