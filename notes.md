# Notes

## Eloquent ORM

ORM is slower. But easier when changing DB, eg from MySQL to PostgreSQL

- https://stackoverflow.com/questions/38391710/laravel-eloquent-vs-query-builder-why-use-eloquent-to-decrease-performance

### N+1 problem 

EG: Post with many Comments. It's bad to retrieve the POST from the DB, then retrieve its Comments from the DB one at a time. Overcome this in Laravel by using the `with` function.

- https://github.com/atabegruslan/Others/blob/master/DB/db.md#eager-vs-lazy-load

## Social Login (Socialite)

### Register

#### Facebook Developer Console

https://developers.facebook.com

![](/Illustrations/FBSignUp1.PNG)
![](/Illustrations/FBSignUp2.PNG)
![](/Illustrations/FBSignUp3.PNG)

#### Google Developer Console

https://console.developers.google.com/

https://developers.google.com/identity/sign-in/web/sign-in

![](/Illustrations/GoogleSignUp.PNG)

.env
```
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_CALLBACK_URL=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_CALLBACK_URL=
```

config/services.php
```php
'facebook' => [
    'client_id'     => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect'      => env('FACEBOOK_CALLBACK_URL'),
],
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_CALLBACK_URL'),
],
```

### Install Socialite

`composer require laravel/socialite`

config/app.php
```php
'providers' => [
	Laravel\Socialite\SocialiteServiceProvider::class,
],
'aliases' => [
	'Socialite' => Laravel\Socialite\Facades\Socialite::class,
],
```

### Modifications

```sql
ALTER TABLE `users` 
    ADD `type` VARCHAR(10) NOT NULL DEFAULT 'normal' AFTER `updated_at`, 
    ADD `social_id` VARCHAR(500) NULL DEFAULT NULL AFTER `type`;
```

Also make `users`.`email` not unique. 

The existing code don't yet allow the same email address to be used for normal and social logins. 

To allow distinction between same emails of different login methods:

#### For Login:

vendor\laravel\framework\src\Illuminate\Foundation\Auth\AuthenticatesUsers.php 
```php
protected function credentials(Request $request)
{
    //return $request->only($this->username(), 'password');
    $request = $request->only($this->username(), 'password');
    $request['type'] = 'normal';

    return $request;
}
```

#### For Register:

vendor\laravel\framework\src\Illuminate\Foundation\Auth\RegistersUsers.php 
```php
public function register(Request $request)
{
    $request['type'] = 'normal';  // add this line
    $this->validator($request->all())->validate();
```

##### Install `unique_with`

`unique_with` is used to allow composite key validation. ( https://github.com/felixkiss/uniquewith-validator )

`composer require felixkiss/uniquewith-validator`

config/app.php
```php
'providers' => [
    ...
    Felixkiss\UniqueWithValidator\ServiceProvider::class,
],
```

app\Http\Controllers\Auth\RegisterController.php
```php
protected function validator(array $data)
{
    // return Validator::make($data, [
    //     'name' => 'required|max:255',
    //     'email' => 'required|email|max:255|unique:users',
    //     'password' => 'required|min:6|confirmed',
    // ]);
    return Validator::make($data, [
        'name'     => 'required|max:255',
        'email'    => 'required|email|max:255|unique_with:users,type',
        'password' => 'required|min:6|confirmed',
        'type'     => 'required',
    ]);
}
```

#### For Forget Passwords:

vendor\laravel\framework\src\Illuminate\Foundation\Auth\SendsPasswordResetEmails.php
```php
public function sendResetLinkEmail(Request $request)
{
    $this->validate($request, ['email' => 'required|email']);

    $request1 = $request->only('email'); // add this line
    $request1['type'] = 'normal'; // add this line

    $response = $this->broker()->sendResetLink(
        //$request->only('email') // Laravel 5.4
        //$this->credentials($request) // Or Laravel 5.8
        $request1 // add this line
    );

    return $response == Password::RESET_LINK_SENT
                ? $this->sendResetLinkResponse($response)
                : $this->sendResetLinkFailedResponse($request, $response);
}
```

vendor\laravel\framework\src\Illuminate\Foundation\Auth\ResetsPasswords.php
```php
protected function credentials(Request $request)
{
    $request = $request->only(
        'email', 'password', 'password_confirmation', 'token'
    );
    $request['type'] = 'normal';
    return $request;
    // return $request->only(
    //     'email', 'password', 'password_confirmation', 'token'
    // );
}
```

#### For API Login:

vendor\league\oauth2-server\src\Grant\PasswordGrant.php
```php
protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
{
    ...
    $type = $this->getRequestParameter('type', $request); // add this

    $user = $this->userRepository->getUserEntityByUserCredentials(
        $username,
        $password,
        $type, // add this
        $this->getIdentifier(),
        $client
    );
```

vendor\league\oauth2-server\src\Repositories\UserRepositoryInterface.php
```php
interface UserRepositoryInterface extends RepositoryInterface
{
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $type, // add this
        $grantType,
        ClientEntityInterface $clientEntity
    );
}
```

vendor\laravel\passport\src\Bridge\UserRepository.php
```php
public function getUserEntityByUserCredentials($username, $password, $type /* ADD THIS TYPE */, $grantType, ClientEntityInterface $clientEntity)
{
    ...
    if (method_exists($model, 'findForPassport')) {
        $user = (new $model)->findForPassport($username);
    } else {
        // $user = (new $model)->where('email', $username)->first();
        $user = (new $model)->where('email', $username)->where('type', $type)->first(); // add this
    }
```

#### Models

App\Models\User.php
```php
class User extends Authenticatable{
...
    protected $fillable = [
        'name', 'email', 'password', 'type', 'social_id'
    ];
```

#### Social buttons

resources/views/auth/login.blade.php
```html
<a href="{{ route('social.login', ['facebook']) }}">
    <img src="btn_facebook.png">
</a> 
<a href="{{ route('social.login', ['google']) }}">
    <img src="btn_google.png">
</a> 
```

### Handle Social Sign Ups

`php artisan make:controller Web/SocialController`

app/Http/Controllers/Web/SocialController.php
```php
public function redirectToProvider($provider)
{
    return Socialite::driver($provider)->redirect();
}

public function handleProviderCallback($provider)
{
    $user = Socialite::driver($provider)->user();
    $data = [
        'name'      => $user->getName(),
        'email'     => $user->getEmail(),
        'type'      => $provider,
        'social_id' => $user->getId(),
        'password'  => bcrypt('random') // @todo to be improved later
    ];

    Auth::login(User::firstOrCreate($data));
    
    return Redirect::to('/entry');
}
```

routes/web.php
```php
Route::group(['namespace' => 'Web', 'middleware' => ['auth']], function () {
	Route::get('auth/{provider}', ['uses' => 'SocialController@redirectToProvider', 'as' => 'social.login']);
	Route::get('auth/{provider}/callback', 'SocialController@handleProviderCallback');
});
```

### User creation by API

`php artisan make:controller Api/UserController --resource`

routes/api.php
```php
// Protected Entry CRUDs
Route::group(['namespace' => 'Api', 'middleware' => ['auth:api']], function () {

    Route::resource('/entry', 'EntryController');

    Route::post('/user', 'UserController@store');

});
```

Then complete the store method, like in: https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Http/Controllers/Web/SocialController.php

### Useful tutorials:

- https://github.com/laravel/socialite
- https://www.youtube.com/watch?v=D3oLLz8bFp0
- http://devartisans.com/articles/complete-laravel5-socialite-tuorial


## Contact form with emailing ability

.env
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=***@gmail.com
MAIL_PASSWORD=*****
MAIL_ENCRYPTION=tls
```

Create contact form view, connect it to route then to controller.

Write the controller like this: https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Http/Controllers/Web/EmailController.php

In view, for HTML email template. Here I just show the HTML email that Admin receives:
```html
<p style="font-size: 100%;">Dear Administrator, You got new mail from Travel Blog</p>

<p style="font-size: 160%; background-color: #F0F8FF;">
{{ $body }}
</p>

<p style="font-size: 100%; background-color: #DCDCDC;">
    <b>From:</b> {{ $name }} ( <a href="mailto:{{ $email }}">{{ $email }}</a> )<br>
</p>
```

## Custom Carousel on Welcome Page

Selects a sample of content images for display

Include my custom written js and css: `/js/ruslan_slider.js` and `/css/ruslan_slider.css`.

Also include: `hammer.js` and this jQuery: `http://code.jquery.com/jquery-latest.min.js`

```html
<div class="slides-holder">
    <div class="slider"></div>
</div>
```

```js
$slider1.slider
({
    title: "Title", //carousel's title
    fade: 500, // fade transition time
    pictures: (array of pictures) // eg ["path/to/image/img1.png", "path/to/image/img2.png", ...]
});     
```

## Events (Hooks)

- https://www.youtube.com/watch?v=e40_eal2DmM
- https://laravel.com/docs/5.8/events#dispatching-events
- `php artisan event:generate`

## Notifications

https://developers.google.com/cloud-messaging
https://laravel.com/docs/5.8/notifications

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
5. Generate VAPID public and private keys in `.env`: `php artisan webpush:vapid`
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

### 3rd party notifications - Firebase push notifications

- Tutorials
    - https://www.youtube.com/playlist?list=PLCakfctNSHkGLCs9az_9PKqW1NY1C5HIU <sup>Web, PHP. Very helpful</sup>
    - https://codeburst.io/how-to-add-push-notifications-on-firebase-cloud-messaging-to-react-web-app-de7c6f04c920 <sup>Very helpful</sup>
        - https://developers.google.com/web/fundamentals/push-notifications/how-push-works
        - https://firebase.google.com/docs/cloud-messaging/js/client
        - https://www.youtube.com/watch?v=PPP9zyEPaCw  <sup>Android</sup>
        - https://www.youtube.com/watch?v=XijS62iP1Xo  <sup>Android</sup>
        - https://medium.com/@ThatJenPerson/authenticating-firebase-cloud-messaging-http-v1-api-requests-e9af3e0827b8
    - https://firebase.google.com/docs/cloud-messaging/js/receive
        - https://github.com/firebase/quickstart-js/blob/master/messaging/README.md
    - https://medium.com/android-school/test-fcm-notification-with-postman-f91ba08aacc3
    - https://stackoverflow.com/questions/37711082/how-to-handle-notification-when-app-in-background-in-firebase
    - https://www.youtube.com/playlist?list=PLGVwFLT24VFq3ZTcakcpByFhe1ex1BPuN <sup>Node</sup>
    - https://www.youtube.com/playlist?list=PLUVqY59GNZQOU-bDlBKy7KPBg-czqy5bF <sup>Node</sup>
    - https://www.youtube.com/playlist?list=PLk7v1Z2rk4hjxP_CHAhjXQN3zrcEhluF_  <sup>Android</sup>

1. Create a project and web app in https://console.firebase.google.com/ . Get the Firebase config credentials. Get the `public/manifest.json` too and include it in the HTML link tag.
2. See https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/public/js/enable-firebase-push.js and https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/public/js/firebase-service-worker.js.
3. Run the `create_fcm_tokens_table` migration script.
4. Make the `notification/firebase` API route handle it in the backend controller and DB.
5. To send: POST to https://fcm.googleapis.com/fcm/send
6. To do this properly, use notification (https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Notifications/NewEntry.php) and create a custom channel (https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Channels/FirebaseChannel.php). Creating custom channel tutorial is here: https://laravel.com/docs/master/notifications#custom-channels
7. Handle incoming notification in `messaging.onMessage` and the service worker's `messaging.setBackgroundMessageHandler`

#### HTTPS

When developing FCM on localhost, it helps to have HTTPS

1. Make sure you have `C:\xampp\apache\conf\ssl.crt\server.crt` and `C:\xampp\apache\conf\ssl.key\server.key`.

If you dont have them, run `C:\xampp\apache\makecert.bat` as admin.

2. `xampp\apache\conf\extra\httpd-ssl.conf`
```
<VirtualHost _default_:443>

DocumentRoot "C:/xampp/htdocs"
ServerName www.example.com:443

SSLEngine on

SSLCertificateFile "conf/ssl.crt/server.crt"

SSLCertificateKeyFile "conf/ssl.key/server.key"
```

- https://gist.github.com/nguyenanhtu/33aa7ffb6c36fdc110ea8624eeb51e69
- https://florianbrinkmann.com/en/https-virtual-hosts-xampp-4215/
- https://deanhume.com/testing-service-workers-locally-with-self-signed-certificates/

## Scheduling tasks

- https://laravel.com/docs/5.8/scheduling
- https://www.youtube.com/watch?v=fUqrE9ZBH_Q
- ReactPHP Loop: https://freek.dev/1689-a-package-to-run-the-laravel-scheduler-without-relying-on-cron
- https://www.positronx.io/laravel-cron-job-task-scheduling-tutorial-with-example/

1. Add a command: `php artisan make:command minutely`
2. Do `app/Console/Commands/minutely.php`
3. Do `app/Console/Kernel.php`
4. When testing in console: `php artisan minutely:demonotice`. When put on server: `php artisan schedule:run`. `schedule:run` actually calls `minutely:demonotice` (and whatever other tasks are there). If you run them from console, they will run immediately. But on the server, the latter will run to schedule.
5. Do ONE minutely cronjob on the server for Laravel, and let Laravel's Scheduler handle the rest of its jobs. On linux, do the cron like this: `* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`. On Windows, use Task Scheduler like this: https://quantizd.com/how-to-use-laravel-task-scheduler-on-windows-10/

## Helper

### Method 1

1. Make `app/XxxHelper.php`
```php
use ...\...;

if (! function_exists('helperFunction')) {
    function helperFunction() 
    {

    }
}
```

2. `composer.json`
```
"autoload": {
    "files": [
        "app/XxxHelper.php"
    ],
```

3. `composer dump-autoload`

### Method 2

1. Make `app/Helpers/XxxHelper.php`
```php
namespace App\Helpers;

use ...\...;

class XxxHelper
{
    public static function helperFunction()
    {

    }
}
```

2. `config/app.php`
```php
'aliases' => [
    'XxxHelper' => App\Helpers\XxxHelper::class,
]
```

3. `composer dump-autoload`

## Permissions (Spatie library)

1. `composer require spatie/laravel-permission`
2. Create migration and config files: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
    - Or create seperately by:
        - Migration: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"`
        - Config: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"`
3. `php artisan migrate`
4. Make User model use `Spatie\Permission\Traits\HasRoles`
5. Add `features` to `config/permission.php`: https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/config/permission.php#L130
6. Run seeder: `php artisan db:seed --class=SyncPermissionTableSeeder` to populate `permissions` table from `config/permission.php`'s `features` part.
7. Do MVC for User, Role and Permission
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

### Tutorials

- https://github.com/spatie/laravel-permission
- https://www.youtube.com/watch?v=zIgYJlu03bI&list=PLYtuiR2P4Dr72be9bC_vCYLGRTWjVBmUz
- https://docs.spatie.be/laravel-permission/v3/installation-laravel/
- https://docs.spatie.be/laravel-permission/v3/basic-usage/basic-usage/
- https://docs.spatie.be/laravel-permission/v3/basic-usage/role-permissions/

## Route (Ziggy library)

### Tutorials

- https://github.com/tightenco/ziggy
- https://www.youtube.com/watch?v=rs7_X47wYBs
- https://github.com/tightenco/ziggy/issues/217
- https://github.com/tightenco/ziggy/issues/265
- https://github.com/tightenco/ziggy/issues/70

---

# How to use Vue in Laravel

## Tutorials

- https://www.youtube.com/playlist?list=PL4cUxeGkcC9gQcYgjhBoeQH7wiAyZNrYa <sup>Only Vue, Very good</sup>
- https://www.youtube.com/watch?v=DJ6PD_jBtU0 <sup>Vue in Laravel, Very good</sup>
- https://vuejsdevelopers.com/2018/02/05/vue-laravel-crud/
- https://www.youtube.com/playlist?list=PLVThfwUGtbI9sxI1zPcvQ9Qfx8Yjgxj7C
- https://www.youtube.com/playlist?list=PLB4AdipoHpxaHDLIaMdtro1eXnQtl_UvE

## Setup

Install Laravel. In CLI: `composer create-project --prefer-dist laravel/laravel [project-name]`

Update `node_modules` according to `package.json` . In CLI: `npm install`

Install Bootstrap. In CLI: `npm install bootstrap`

1. Set up `resources/views/welcome.blade.php` like this:

```html
<head>   
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script> 
    <link href="css/app.css" rel="stylesheet">
</head>
<body>
    <div id="app">
        <example></example>
    </div>
    <script src="js/app.js"></script>
</body>
```

In Laravel 5.8
- In `resources/js/app.js`: must include `.default` in `Vue.component('xx', require('./components/xx.vue').default)`( https://stackoverflow.com/questions/49138501/vue-warn-failed-to-mount-component-template-or-render-function-not-defined-i )

2. Have the dev script `package.json` like this:

```js
...
  "scripts": {
    "dev": "node node_modules/cross-env/dist/bin/cross-env.js NODE_ENV=development node_modules/webpack/bin/webpack.js --progress --hide-modules --config=node_modules/laravel-mix/setup/webpack.config.js",
    ...
  },
...
```

3. In CLI: run `npm run dev`, which calls `package.json`'s `scripts`'s `dev`, which calls `webpack.mix.js`, which processes `resources/assets/js/app.js` into `public/js/app.js` and `resource/assets/sass/app.scss` into `public/css/app.css`.

4. In CLI, launch server: `php artisan serve`, then see: `http://127.0.0.1:8000/welcome`, or turn on local server and see `http://localhost/{sitename}/public/welcome`

5. Note that `public/js/app.js` and `public/css/app.css` is used.

#### It should look like this:

![](/Illustrations/vuetest2.PNG)

## Output seperate JS files from Vue files

https://stackoverflow.com/questions/58659571/separate-js-file-for-bundle-and-components-in-vuejs

In your webpack.mix.js
```js
mix.js('resources/js/modules/module1.js', 'public/js/modules')
   .js('resources/js/modules/module2.js', 'public/js/modules');
```

or more elaborately:
```js
let fs = require('fs');

let getFiles = function (dir) 
{
  return fs.readdirSync(dir).filter(file => {
    return fs.statSync(`${dir}/${file}`).isFile();
  });
};

getFiles('resources/js/modules').forEach(function (filepath) {
  mix.js('resources/js/modules/' + filepath, 'public/js/modules');
});
```

resources/js/modules/module1.js, for example, can be like:
```js
import Component from 'resources/js/components/Component.vue';

Vue.component('base', Component);
```

Then in your resources/views/what..ever/view.blade.php:
```
@section('content')
    <Base></Base>
@endsection

@section('script')
    <script src="/public/js/modules/module1.js"></script>
@endsection
```

## How to access protected API routes from Vue view

1. Pass access token from backend to view template, like https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Http/Controllers/Controller.php
2. Keep the token in a meta tag `<meta name="token" content="{{ $token }}">`
3. In the JS part, set the token as a part of the header `axios.defaults.headers.common['Authorization'] = 'Bearer ' + document.head.querySelector('meta[name="token"]').content`
4. In the script part of the Vue view, you can use axios to make requests to token-protected routes.

## Vue Forms

- https://www.youtube.com/watch?v=XZF71ij463Y

---

# How to make the region feature

So that a place-entry can belong to a region.

Need to create a many-to-many relationship between place and region.

## Theory of many to many relationships in Laravel

Use pivot tables. `Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable::sync()` is especially useful: https://laraveldaily.com/pivot-tables-and-many-to-many-relationships/

![](/Illustrations/pivot-sync.png)

## Steps

https://appdividend.com/2018/05/17/laravel-many-to-many-relationship-example/?fbclid=IwAR0AzrvJyhG0tuHEpScPXz4yAy4U6Itc23PAXDygs2yQW8C2GGH8_RheMQM

1. `php artisan make:model Models/Region`

2. Make `database/migrations/xxx_create_regions_table.php`

3. `php artisan make:migration create_region_entry_table --create=region_entry`

4. `php artisan migrate`

5. Complete region model and edit entry model.

6. Edit entry controller's store and update functions.

7. Make region route and controller (`php artisan make:controller Web/RegionController --resource`) and views.

8. Make region display in entry list and item views.

## Make regions hierarchical

So that, eg: East Asia is a subset of Asia

1. `php artisan make:migration create_region_tree_table --create=region_tree`

2. `php artisan migrate`

3. `php artisan make:model Models/RegionTree`

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

5. Put `Vue.component('regions', require('./components/region/Tree.vue').default)` into `resources/js/app.js` and `<Regions></Regions>` into the `regions` list view.

6. Vue relies on AJAX to get data. So make the necessary api route.
```php
Route::group(['namespace' => 'Api'], function () {
    Route::resource('/region', 'RegionController');
```

7. `php artisan make:controller Api/RegionController --resource`

8. `npm install vue-jstree`

---

# How to make the comment feature

1. `php artisan make:migration create_comments_table --create=comments`

2. `php artisan migrate`

3. `php artisan make:model Models/Comment`. 1 blog should have many comments.

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

---

# How to make the 'at user' (@user) autosuggest feature:

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

## WYSIWYG Editor:

### CKEditor

#### Normally we use CDNs:

- https://cdn.ckeditor.com/ <sup>Good Doc</sup>
- https://www.jsdelivr.com/package/npm/@ckeditor/ckeditor5-editor-classic?path=src
    - https://cdn.jsdelivr.net/npm/@ckeditor/ckeditor5-editor-classic@16.0.0/src/classiceditor.js
- https://stackoverflow.com/questions/49714473/modifying-capturing-key-press-with-ckeditor5  <sup>Usage</sup>

#### Setup in Laravel/Vue:

- https://ckeditor.com/docs/ckeditor5/latest/builds/guides/integration/frameworks/vuejs.html

1. `npm install --save @ckeditor/ckeditor5-vue @ckeditor/ckeditor5-build-classic
`
2. In `.vue`, at the beginning of `<script>` tag: `import ClassicEditor from '@ckeditor/ckeditor5-build-classic';`
3. Now in Vue's `mounted` method you can use 
```
ClassicEditor
  .create( document.querySelector( "textarea.text-input-field" ) )
  .then( editor => { ...
```
4. To redisplay your WYSIWYG comments as HTML: `<p v-html="comment.contents">{{ comment.contents }}</p>`

---

# How to filter in view:

## In Blade

- https://codecourse.com/watch/filtering-in-laravel-blade  

- https://pineco.de/laravel-blade-filters/
- Unfortunately that library doesn't work in the newest versions of Laravel. But, we can still imitate it:

1. Make provider: `php artisan make:provider BladeFiltersServiceProvider` , https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Providers/BladeFiltersServiceProvider.php
2. Make service: https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Services/BladeFiltersCompiler.php
3. Make custom provider: `php artisan make:provider TranslateServiceProvider` , https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/app/Providers/TranslateServiceProvider.php
4. Register in `config/app.php` : https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/config/app.php#L187-188
5. Use it in Blade: `{{ $blablah | translate:'vn' }}`

## In Vue

- https://vuejs.org/v2/guide/filters.html
- https://v1.vuejs.org/guide/custom-filter.html
- https://stackoverflow.com/questions/54744877/vue-filters-for-input-v-model
- https://scotch.io/tutorials/how-to-create-filters-in-vuejs-with-examples#toc-defining-and-using-filters

1. In `resources\js\app.js` write your filter: https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/blob/master/resources/js/app.js
2. In Vue, use it like: `{{ blahblah | to_3dp }}`
3. run `npm run dev`

---

# Globally available functions and constants

Use mixins: https://v1.vuejs.org/guide/mixins.html

---

# Notes about Laravel

## Service Provider

![](/Illustrations/servicecontainer1.jpg)

![](/Illustrations/servicecontainer2.png)

- https://code.tutsplus.com/tutorials/how-to-register-use-laravel-service-providers--cms-28966
- Then watch these tutorials:
    - https://www.youtube.com/watch?v=urycXvTEnF8&t=1m
    - https://www.youtube.com/watch?v=GqVdt6OWN-Y&list=PL_HVsP_TO8z7aeylCMe64BIx3VEfvPdn&index=34
- Then watch these tutorials:
    - https://www.youtube.com/watch?v=pIWDFVWQXMQ&list=PL_HVsP_TO8z7aey-lCMe64BIx3VEfvPdn&index=33&t=19m35s
    - https://www.youtube.com/watch?v=hy0oieokjtQ&list=PL_HVsP_TO8z7aey-lCMe64BIx3VEfvPdn&index=35
    - https://laravel.com/docs/8.x/container
        - https://laravel.com/docs/4.2/ioc
    - https://medium0.com/@NahidulHasan/laravel-ioc-container-why-we-need-it-and-how-it-works-a603d4cef10f

### Advantages

Better dependency management
- https://christoph-rumpel.com/2019/8/4-ways-the-laravel-service-container-helps-us-managing-our-dependencies


## Lifecycle

- https://laravel.com/docs/8.x/lifecycle#first-steps
    - https://laravel.com/docs/4.2/lifecycle#request-lifecycle (Summary subsection)
    
## Architectural Patterns

Laravel best fits the ADR pattern.

![](/Illustrations/patterns.png)

## Clear cache

- https://tecadmin.net/clear-cache-laravel-5/
- On top of the above `php artisan config:cache` is also an useful command

## Upload to server

Methods:

1. Upload `public` folder into server's `public_html` folder. Upload the rest to another folder outside of the server's `public_html` folder. In `public/index.php` rectify all relevant paths. Import .sql to server's database. Refactor database-name, username & password in the `.env` file.
2.  Load the entire folder as it is. To rid the `/public/` segment of the URL, put the following into the root folder's `.htaccess`: https://infyom.com/blog/how-to-remove-public-path-from-url-in-laravel-application
3. To rid the `/public/` by: https://www.devopsschool.com/blog/laravel-remove-public-from-url-using-htaccess/

## Different ways of writting things

In Blade
```
@if (!in_array($modLabel, ['xxx', 'yyy']))

@endif
```
is same as
```
@php {{ $skips = ['xxx','yyy','deleted_at']; }} @endphp
@if (!in_array($initLabel, $skips))

@endif
```

In PHP
```
$thisAndPrevious = ActionLog::where([
        [ 'time',            '<=', $log['time']            ],
        [ 'record_key_name', '=',  $log['record_key_name'] ],
        [ 'record_id',       '=',  $log['record_id']       ],
        [ 'model',           '=',  $log['model']           ],
    ])
    ->where(function ($query) {
        $query->where('method', '=', 'create')
              ->orWhere('method', '=', 'update');
    })
    ->orderBy('id', 'DESC')
    ->take(2)
    ->get();
```
is same as
```
$thisAndPrevious = CrudLog::where('time', '<=', $log['time'])
    ->where('record_key_name', '=',  $log['record_key_name'])
    ->where('record_id', '=',  $log['record_id'])
    ->where('model', '=',  $log['model'])
    ->whereIn('method', ['create', 'update'])
    ->orderBy('id', 'DESC')
    ->take(2)
    ->get();
```

## Migration scripts

- Migration is for database structure.
- To run a DB migration script again:
    - `php artisan migrate:rollback` (which deletes the most recent batch out of the `migrations` table)
    - Or go into the DB, manually delete the entry out of the `migrations` table.
- Seeding is for database data.
    - Make seed: `php artisan make:seeder WhateverTableSeeder`
    - Run seed: `php artisan db:seed --class=WhateverTableSeeder`

## Timestamps and Soft Deletes

If you weren't using these before and decide to start using them

1. Adust the database
    - For timestamps: add `created_at` & `updated_at` nullable columns of timestamp type, default now.
    - For soft delete: add `deleted_at` nullable column of timestamp type, default null.
2. Make the migration script consistent by adding 
```php
Schema::create('whatevers', function (Blueprint $table) {
    ...
    $table->softDeletes();
    $table->timestamps();
});
```
3. In model, add:
```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Whatever extends Model
{
    use SoftDeletes;

    public $timestamps = true;
```

You can see that `Illuminate\Database\Eloquent\Model.php::performDeleteOnModel()` is overridden by `Illuminate\Database\Eloquent\SoftDeletes.php::performDeleteOnModel()`

https://www.itsolutionstuff.com/post/how-to-use-soft-delete-in-laravel-5example.html

## Pagination (with and without Vue)

### Blade

#### In controller
```php
    public function index()
    {
        //$data = WhateverModel::orderBy('updated_at', 'ASC')->get()->all();
        $data = WhateverModel::orderBy('updated_at', 'ASC')->paginate(10);

        return view('whatever.index', ['data' => $data]);
```

#### In view `whatever/index.blade.php`
```
<div class="row" >
    <div id="paginate">
        {{ $data->links() }}
    </div>
</div>

```

### Vue

#### For the view

1. Write your custom pagination component, like: https://github.com/atabegruslan/Travel-Blog-Laravel-5-8/tree/master/resources/js/components/common/Pagination.vue

2. In `app.js`
```
import VuePagination from './components/common/Pagination';
Vue.component('vue-pagination', VuePagination);
```

3. Use it `<vue-pagination :pagination="pagination" @paginate="getItems()" />` where `pagination` is
```
{
    "current_page" :1,
    "from"         :1,
    "last_page"    :1,
    "per_page"     :20,
    "to"           :2,
    "total"        :2
}
```

#### In API controller

Pass the `pagination` object into the view.

#### Or you can use other's libraries

- https://bootstrap-vue.js.org/docs/components/pagination/
- https://www.npmjs.com/package/vuejs-paginate
- https://vuejsexamples.com/tag/pagination/
- https://github.com/gilbitron/laravel-vue-pagination/blob/master/README.md
- https://github.com/matfish2/vue-pagination-2/blob/master/README.md

---

# More notes

https://github.com/Ruslan-Aliyev/laravel_notes/

---

# To Do

- Document APIs using Swagger
    - https://github.com/DarkaOnLine/L5-Swagger
    - https://www.youtube.com/playlist?list=PLnBvgoOXZNCOiV54qjDOPA9R7DIDazxBA
    - https://idratherbewriting.com/learnapidoc/pubapis_swagger.html <sup>helpful</sup>
    - https://swagger.io/blog/api-strategy/difference-between-swagger-and-openapi/ <sup>theory</sup>
    - https://swagger.io/blog/api-development/swaggerhub-101-ondemand-tutorial/
    - https://apihandyman.io/writing-openapi-swagger-specification-tutorial-part-1-introduction/
    - https://www.youtube.com/watch?v=xggucT_xl5U
- Better if notifications are triggered by events (and event listeners)
- Fix FCM
    - Notification shouldn't should be send to the same person as many times as the number of users.
        - https://laravel.com/docs/5.8/notifications#using-the-notifiable-trait <sup>read again</sup>
        - https://github.com/laravel-notification-channels/webpush/blob/master/src/WebPushChannel.php <sup>imitate</sup>
            - Maybe more code needed
                - User model should use Trait `NotificationChannels\WebPush\HasPushSubscriptions`
                - Add firebase code to `Illuminate\Notifications\RoutesNotifications::routeNotificationFor`
- Don't put access token into HTML meta tag to assist Axios AJAX requests to protected API routes. Use JS to get token first then access protected routes. Use generators to make code tidier.
