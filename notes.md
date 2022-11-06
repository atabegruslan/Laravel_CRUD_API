# Notes

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

Then complete the store method, like in: https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Http/Controllers/Web/SocialController.php

### Useful tutorials:

- https://github.com/laravel/socialite
- https://www.youtube.com/watch?v=D3oLLz8bFp0
- http://devartisans.com/articles/complete-laravel5-socialite-tuorial

## Notifications

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
2. See https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/public/js/enable-firebase-push.js and https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/public/js/firebase-service-worker.js.
3. Run the `create_fcm_tokens_table` migration script.
4. Make the `notification/firebase` API route handle it in the backend controller and DB.
5. To send: POST to https://fcm.googleapis.com/fcm/send
6. To do this properly, use notification (https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Notifications/NewEntry.php) and create a custom channel (https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Channels/FirebaseChannel.php). Creating custom channel tutorial is here: https://laravel.com/docs/master/notifications#custom-channels
7. Handle incoming notification in `messaging.onMessage` and the service worker's `messaging.setBackgroundMessageHandler`

# Notes about Laravel

## Eloquent ORM or Query Builder (`\DB`)

### Speed vs Changing DB

Eloquent is slower. But easier when changing DB, eg from MySQL to PostgreSQL

https://stackoverflow.com/questions/38391710/laravel-eloquent-vs-query-builder-why-use-eloquent-to-decrease-performance

![](/Illustrations/Eloquent_Speed.png)

### Functionalities

Eloquent have more functionalities. (You can code for them in the model file)

### Considerations for N+1 problem

With Query Builder, you can explicitly write queries with considerations for N+1 problem.  
With Eloquent, you have to use things like `with` to enable considerations for N+1 problem. Eloquent won't do it by itself.  

![image](https://user-images.githubusercontent.com/20809372/200185002-e6b1b4e4-0d74-4ac9-8513-83cc1fc615ce.png)

https://www.youtube.com/watch?v=uVsY_OXRq5o

#### N+1 problem 

By default: lazy load.  
But lazy load have N + 1 problem.  
EG: Picture have Metadata. So if you want to retrieve N Pictures and their width: `$picture->metadata->width`, then for each picture another query will be run for metadata. Hence, you'll end up with N + 1 queries (N for Metadata and 1 for Picture).  
Eager loading reduces this from N+1 to 2:  
```
select * from pictures
select * from metadatas where id in (1, 2, 3, 4, 5, ...)
```
Eager loading have 2 functions that we can utilize: `Picture::with('metadata')->get();` or `Picture::all()->load('metadata');`.  

- https://viblo.asia/p/eager-loading-trong-laravel-su-dung-with-hay-load-RnB5p0bG5PG
- https://laravel.com/docs/5.2/eloquent-relationships#eager-loading
- https://www.youtube.com/watch?v=bZlvzvGpCEE
- https://www.youtube.com/watch?v=N0phQbyzF0I

## Better code for DB optimization

1. Add indexes and FKs
2. Use `->get()` last. So group and order, then take the first n entries, and finally use `->get()`.
3. Refer to relationship instead of fetching all related entries. So use `$model->relation()` instead of `$model->relation`. Use things like `->count()` after that.

Also:
- Use magic methods eg `withCount`.
- Use `with` to avoid N+1 problem.

https://www.youtube.com/watch?v=yAAqAxiaEmg

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

## Scheduling tasks

- https://laravel.com/docs/8.x/scheduling
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

## Filter in view:

### In Blade

- https://codecourse.com/watch/filtering-in-laravel-blade  

- https://pineco.de/laravel-blade-filters/
- Unfortunately that library doesn't work in the newest versions of Laravel. But, we can still imitate it:

1. Make provider: `php artisan make:provider BladeFiltersServiceProvider` , https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Providers/BladeFiltersServiceProvider.php
2. Make service: https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Services/BladeFiltersCompiler.php
3. Make custom provider: `php artisan make:provider TranslateServiceProvider` , https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Providers/TranslateServiceProvider.php
4. Register in `config/app.php` : https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/config/app.php#L187-188
5. Use it in Blade: `{{ $blablah | translate:'vn' }}`

### In Vue

- https://vuejs.org/v2/guide/filters.html
- https://v1.vuejs.org/guide/custom-filter.html
- https://stackoverflow.com/questions/54744877/vue-filters-for-input-v-model
- https://scotch.io/tutorials/how-to-create-filters-in-vuejs-with-examples#toc-defining-and-using-filters

1. In `resources\js\app.js` write your filter: https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/resources/js/app.js
2. In Vue, use it like: `{{ blahblah | to_3dp }}`
3. run `npm run dev`

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

1. Write your custom pagination component, like: https://github.com/atabegruslan/Laravel_CRUD_API/tree/master/resources/js/components/common/Pagination.vue

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

## Factory

- https://youtu.be/MHBDUJ51Pqs

![](/Illustrations/factory1.PNG)

![](/Illustrations/factory2.PNG)

![](/Illustrations/factory3.PNG)

- https://laravel.com/docs/8.x/database-testing
- States (eg: for generating only users of admin type) https://stackoverflow.com/a/57502690
- `create` persists to DB, `make` creates 1 instance of model https://stackoverflow.com/a/44119474
- A model instance is created first, then `afterMaking` closure is called, then the Object is writen into the DB and finally `afterCreating` is called https://stackoverflow.com/q/58953181

### Relationships

- https://laravel.com/docs/8.x/database-testing#has-many-relationships (`->has()` and `->for()`)
- https://joelclermont.com/post/laravel-8-factory-relationships/ (Factory in factory)
- https://www.codegrepper.com/code-examples/whatever/laravel+factory+relationship (via callbacks)

### To Read

- https://laravel.com/docs/9.x/mocking
- https://laravel.com/docs/7.x/database-testing
- https://laravel-news.com/laravel-log-fake-2-0

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

---

# More notes

https://github.com/Ruslan-Aliyev/Laravel8_Newest_Notes
