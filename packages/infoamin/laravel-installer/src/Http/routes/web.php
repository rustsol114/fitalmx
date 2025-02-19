<?php

Route::group(['namespace' => 'Infoamin\Installer\Http\Controllers'], function()
{
    if(!env('APP_INSTALL')) {
        Route::get('/', 'WelcomeController@welcome');
    }
	Route::group(['prefix' => 'install', 'middleware' => ['web','installed']], function() {
		Route::get('database', 'DatabaseController@create');
		Route::get('requirements','RequirementsController@requirements');
		Route::get('permissions','PermissionsController@checkPermissions');
		Route::get('seedmigrate', 'DatabaseController@seedMigrate');
		Route::post('database', 'DatabaseController@store');
		Route::get('register', 'UserController@createUser');
		Route::post('register', 'UserController@storeUser');
		Route::get('finish', 'FinalController@finish');
	});
	
	Route::post('install/clear-cache', 'PermissionsController@clearCache');
});
