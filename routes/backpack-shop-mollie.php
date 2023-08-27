<?php

/*
|--------------------------------------------------------------------------
| Eleven59\BackpackShopMollie Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Eleven59\BackpackShopMollie package.
|
*/

/**
 * User Routes
 */

 Route::group([
     'middleware'=> array_merge(
     	(array) config('backpack.base.web_middleware', 'web'),
     ),
     'namespace' => 'Eleven59\BackpackShopMollie\Http\Controllers',
 ], function() {
     Route::get(config('eleven59.backpack-shop-mollie.webhook_url', '/mollie/webhook'), ['uses' => 'RouteController@webhook']);
 });
