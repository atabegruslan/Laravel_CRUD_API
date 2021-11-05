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

## Notifications

- https://developers.google.com/cloud-messaging
- https://laravel.com/docs/8.x/notifications

### Default mail notification:

https://www.youtube.com/watch?v=WshDno7igKA

1. `php artisan make:notification NewEntry`
2. User model use `Illuminate\Notifications\Notifiable`
3. Notify user in controller: `$user->notify(new NewEntry);` or `\Notification::send($users, new NewEntry);`
4. Inside the notification class

```php
public function via($notifiable)
{
    return ['mail'];
}
```

5. Do your email message.

### Default database notification:

https://www.youtube.com/watch?v=Tkq0H-McErE

1. `php artisan notifications:table`
2. `php artisan migrate`
3. Run `php artisan make:notification NewEntry` if you haven't already.
4. Inside the notification class

```php
public function via($notifiable)
{
    return ['mail', 'database'];
}
```

Note: If the mail method doesn't exist, it will default to the `toArray` method.

5. But here we create a `toDatabase` method, which corresponds to `return [..., 'database']`. It should have the same form as the `toArray` method. Now the order is: `toMail` & `toDatabase`, then `toArray`.
6. Notify user in controller.
7. Create a new entry. Notification entries (one DB entry for each notified user) should appear in the `notifications` DB table. If it works, we then create the interface to show these notifications.
8. When displaying notifications, the key line of code to use is: `auth()->user()->unreadNotifications`.

## Ziggy Routes

https://github.com/tightenco/ziggy

## Region feature

So that a place-entry can belong to a region.

Need to create a many-to-many relationship between place and region.

### Theory of many to many relationships in Laravel

Use pivot tables. `Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable::sync()` is especially useful: https://laraveldaily.com/pivot-tables-and-many-to-many-relationships/

![](/Illustrations/pivot-sync.png)

### Steps

https://appdividend.com/2018/05/17/laravel-many-to-many-relationship-example

1. `php artisan make:model Region`

2. `php artisan make:migration create_regions_table`

3. `php artisan make:migration create_region_entry_table --create=region_entry`

4. `php artisan migrate`

5. Complete region model and edit entry model.

6. Edit entry controller's store and update functions.

7. Make region route and controller (`php artisan make:controller Web/RegionController --resource`) and views.

8. Make region display in entry list and item views.

### Make regions hierarchical

So that, eg: East Asia is a subset of Asia

1. `php artisan make:migration create_region_tree_table --create=region_tree`

2. `php artisan migrate`

3. `php artisan make:model RegionTree`

4. Make manipulatable tree in view. There are a few options for making draggable and droppable hierarchical tree in frontend:
    - jQuery UI (But no provision for trees)
        - https://jqueryui.com/droppable/
            - https://jsfiddle.net/atabegaslan/j7web6yp/
    - JSTree (Have provision for trees. Draggable and Droppable provided for in its DnD plugin)
        - https://www.jstree.com/
            - https://jsfiddle.net/atabegaslan/my9q02sf/
    - Recursion (Needed because the nested data can be infinitely deep)
        - https://vuejsdevelopers.com/2017/10/23/vue-js-tree-menu-recursive-components/ 
            - https://jsfiddle.net/atabegaslan/mhf58zg9/
    - JSTree for Vue (Fortunately the ability of recursion for infinitely-deep nested-data is already here)
        - https://www.npmjs.com/package/vue-jstree
        - https://www.vuescript.com/tag/tree-view/
            - https://www.vuescript.com/interactive-tree-view-vue-js-2-vjstree/
                - https://zdy1988.github.io/vue-jstree/
    - **In conclusion:** JSTree in Vue is the most convenient. But the `regions` list view was written in `blade`, so now it needs to be re-written in `Vue`.

5. Put `Vue.component('regions', require('./components/region/Tree.vue').default)` into `resources/js/app.js` and `<Regions></Regions>` into the `regions` list view. (**Setting up Vue in Laravel**: https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/vue.md)

6. Vue relies on AJAX to get data. So make the necessary api route.
```php
Route::group(['namespace' => 'Api'], function () {
    Route::resource('/region', 'RegionController');
```

7. `php artisan make:controller Api/RegionController --resource`

8. `npm install vue-jstree`

# Further notes:

https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/notes.md
