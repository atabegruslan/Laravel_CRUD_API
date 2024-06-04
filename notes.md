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
