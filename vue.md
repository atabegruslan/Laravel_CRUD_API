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

1. Pass access token from backend to view template, like https://github.com/Ruslan-Aliyev/https://github.com/atabegruslan/Laravel_CRUD_API/blob/master/app/Http/Controllers/Controller.php
2. Keep the token in a meta tag `<meta name="token" content="{{ $token }}">`
3. In the JS part, set the token as a part of the header `axios.defaults.headers.common['Authorization'] = 'Bearer ' + document.head.querySelector('meta[name="token"]').content`
4. In the script part of the Vue view, you can use axios to make requests to token-protected routes.

## Vue Forms

- https://www.youtube.com/watch?v=XZF71ij463Y
