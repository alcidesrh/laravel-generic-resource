<?php

Route::group(['namespace' => '\Alcidesrh\Generic'], static function () {
    
    Route::group(['prefix' => config('generic-resource.route.prefix', 'generic')], static function () {
        Route::post('/' . config('generic-resource.route.list_route_name', 'list'), 'GenericController@list')->name('laravel_generic_resource.list');
        Route::post('/' . config('generic-resource.route.create_route_name', 'create'), 'GenericController@create')->name('laravel_generic_resource.create');
        Route::post('/' . config('generic-resource.route.update_route_name', 'update'), 'GenericController@update')->name('laravel_generic_resource.update');
        Route::post('/' . config('generic-resource.route.item_route_name', 'item'), 'GenericController@item')->name('laravel_generic_resource.item');
        Route::post('/' . config('generic-resource.route.delete_route_name', 'delete'), 'GenericController@delete')->name('laravel_generic_resource.delete');
    });
});
