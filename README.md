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

POST `{{domain}}/oauth/token`

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

GET `{{domain}}/api/user`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

Return user data

### Insert new user

POST `{{domain}}/api/user`

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

GET `{{domain}}/api/entry`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

Return all entries

### Get one entry

GET `{{domain}}/api/entry/{id}`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

Return one entry

### Create entry

POST `{{domain}}/api/entry`

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

POST `{{domain}}/api/entry/{id}`

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

POST `{{domain}}/api/entry/{id}`

| Header Field Name | Header Field Value |
| --- | --- |
| Accept | application/json |
| Authorization | Bearer (access token) |

| Post Form Data Name | Post Form Data Value |
| --- | --- |
| _method | DELETE |

Return OK or Error response

---

# Setup

```
git clone https://github.com/atabegruslan/Laravel_CRUD_API.git
cd Laravel_CRUD_API
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan passport:install
php artisan webpush:vapid
npm install
npm run dev
```

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

### 3rd party notifications - WebPush

- Documentations
    - https://laravel.com/docs/5.8/notifications#specifying-delivery-channels
    - https://github.com/laravel-notification-channels/webpush
        - https://github.com/laravel-notification-channels/webpush/blob/master/src/WebPushMessage.php
    - https://laravel-notification-channels.com/webpush/#installation
- Tutorials
    - https://github.com/cretueusebiu/laravel-web-push-demo
    - https://medium.com/@sagarmaheshwary31/push-notifications-with-laravel-and-webpush-446884265aaa <sup>Very helpful</sup>
- Theory
    - Web Notification API
        - https://www.sitepoint.com/introduction-web-notifications-api/
        - https://www.youtube.com/watch?v=EEhohSp0if4
        - https://developer.mozilla.org/en-US/docs/Web/API/notification <sup>Documentation</sup>
        - https://web-push-book.gauntface.com/chapter-05/02-display-a-notification/
    - Push API
        - https://www.izooto.com/web-push-notifications-explained
        - https://developers.google.com/web/updates/2016/01/notification-actions <sup>Actions</sup>
    - Notification API working in conjunction with Push API
        - https://www.youtube.com/watch?v=ggUY0Q4f5ok 
        - https://www.youtube.com/watch?v=HlYFW2zaYQM

1. `composer require laravel-notification-channels/webpush`
2. User model use `NotificationChannels\WebPush\HasPushSubscriptions;`
3. `php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider" --tag="migrations"
`
4. `php artisan migrate`
5. Generate VAPID public and private keys in `.env`: `php artisan webpush:vapid` (Prequisite: OpenSSL https://www.xolphin.com/support/OpenSSL/OpenSSL_-_Installation_under_Windows )
6. See `enable-push` & `service-worker` js files.
7. See `NotificationController` and its route.
8. `NewEntry` Notification class:

```php
public function via($notifiable)
{
    return ['mail', 'database', WebPushChannel::class];
}
```

and complete `toWebPush` function.

9. `Notification::send($users, new NewEntry);`

![](/Illustrations/push_notifications_anatomy.png)

Tutorial:
- https://reposhub.com/php/miscellaneous/cretueusebiu-laravel-web-push-demo.html

You will also need: 
- Enable `gmp` extension in `php.ini`
- Allow notifications from your site: https://sendpulse.com/knowledge-base/push-notifications/enable-disable-push-notifications-google-chrome
- If using XAMPP, need HTTPS setup: https://github.com/atabegruslan/Others/blob/master/Development/xampp.md#https-on-local-xampp
- If using cURL, may need to disable `CURLOPT_SSL_VERIFYPEER` flag: 
    - https://stackoverflow.com/questions/17490963/php-curl-returns-false-on-https/27514992
    - https://github.com/guzzle/guzzle/issues/1935#issuecomment-629548739

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

## Comments feature

1. `php artisan make:migration create_comments_table --create=comments`

2. `php artisan migrate`

3. `php artisan make:model Comment`. 1 blog should have many comments.

4. `php artisan make:controller Api/CommentController --resource`

5. `@include('parts/_entry_comments')` in `resources/views/entry/show.blade.php` and `edit.blade.php`

6. In `parts/_entry_comments.blade.php`
```html
<div class="vuepart">
    <Comments></Comments>
</div>
```

7. In `resources/js/app.js`: `Vue.component('comments', require('./components/entry/Comments.vue').default);`

8. Create `resources/js/components/entry/Comments.vue`

### `@user` autosuggest functionality:

First we need to make user profile pages. Then:

1. Make a text input field and an user autosuggest list:
```html
<textarea class="text-input-field"></textarea>

<select id="autosuggest" @change="choseUser" size="5"></select>
```

2. Add onkeyup event listener to the text input field, with the handler function:

Before the actual code, need to debounce:
```js
var displayUserList;

$("textarea.text-input-field").keyup(function() {
    clearTimeout(displayUserList);

    displayUserList = setTimeout(function() {
        // The actual code
    }, 500);

});
```

The actual code:
```js
var selection = window.getSelection();
var currPos   = selection.anchorOffset;
var currInput = selection.focusNode.wholeText;

if (currPos)
{
    var currPart  = currInput.substring(0, currPos);
    var currWord  = currPart.substring(currPart.lastIndexOf(" ") + 1);

    if (currWord.charAt(0) === '@')
    {
        var username = currWord.replace("@", "");

        if (username)
        {
            // AJAX get user list to populate select#autosuggest

```

3. On user select `choserUser`
```js
choseUser(event) 
{
    var id    = event.target.value;
    var label = $(event.target).find("option:selected").text();

    var commentText = $("textarea.text-input-field").html();
    var replaced    = commentText.replace('@user', '<a href="link/xx/yy/'+ id +'">'+ label +'</a>');

    $("textarea.text-input-field").html('replaced');
}
```

But for this to work well, the text input field will need to be a WYSIWYG editor ...

#### WYSIWYG Editor: CKEditor

CDNs:

- https://cdn.ckeditor.com/ <sup>Good Doc</sup>
- https://www.jsdelivr.com/package/npm/@ckeditor/ckeditor5-editor-classic?path=src
    - https://cdn.jsdelivr.net/npm/@ckeditor/ckeditor5-editor-classic@16.0.0/src/classiceditor.js
- https://stackoverflow.com/questions/49714473/modifying-capturing-key-press-with-ckeditor5  <sup>Usage</sup>

Setup in Laravel/Vue:

- https://ckeditor.com/docs/ckeditor5/latest/builds/guides/integration/frameworks/vuejs.html

1. `npm install --save @ckeditor/ckeditor5-vue @ckeditor/ckeditor5-build-classic`
2. In `.vue`, at the beginning of `<script>` tag: `import ClassicEditor from '@ckeditor/ckeditor5-build-classic';`
3. Now in Vue's `mounted` method you can use 
```
ClassicEditor
  .create( document.querySelector( "textarea.text-input-field" ) )
  .then( editor => { ...
```
4. To redisplay your WYSIWYG comments as HTML: `<p v-html="comment.contents">{{ comment.contents }}</p>`

## Events (Hooks)

- https://www.youtube.com/watch?v=e40_eal2DmM
- https://laravel.com/docs/8.x/events#dispatching-events

1. To setup - Either:
```
php artisan make:event NewEntryMade
php artisan make:listener HandleNewEntry --event=NewEntryMade
```
Or: `app\Providers\EventServiceProvider.php`
```php
protected $listen = [
    Registered::class => [
        SendEmailVerificationNotification::class,
    ],
    'App\Events\NewEntryMade' => [
        'App\Listeners\HandleNewEntry',
    ],
];
```
and then `php artisan event:generate`

2. To make event from within eg controller
```php
event(new \App\Events\NewEntryMade($params));
```

3. The rest of the code:
```php
class NewEntryMade
{
    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }
```

```php
class HandleNewEntry
{
    public function __construct() {}

    public function handle(\App\Events\NewEntryMade $event)
    {
        $params = $event->params;
        // Do something, eg: saving an Eloquent model
    }
```

## Activity log

https://github.com/spatie/laravel-activitylog

# Further notes:

https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/notes.md
