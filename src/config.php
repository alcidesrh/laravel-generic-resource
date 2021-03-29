<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel generic resource package configuration
    |--------------------------------------------------------------------------
    |
     */
    'route' => [

        //Route's prefix for generic CRUD(create, read, update and delete) operations.
        //Deafault 'generic' e.g.: axios.post( 'https://yourdomain/generic' )
        'prefix' => 'generic',

        //Route for list of generic items.
        //Deafault 'list' e.g. axios.post( 'https://yourdomain/generic/list' )
        'list_route_name' => 'list',

        //Route to create a generic item.
        //Deafault 'create' e.g. axios.post( 'https://yourdomain/generic/create', {table: 'users', values: [ {username: 'whatever', role_id: 1}], field: [id, username] } )
        'create_route_name' => 'create',

        //Route to update a generic item.
        //Deafault 'update'  e.g. axios.post( 'https://yourdomain/generic/update', {table: 'users', id: 1, values: [ {username: 'whatever', role_id: 1}], field: [id, username] } )
        'update_route_name' => 'update',

        //Route to get a generic item.
        //Deafault 'item' e.g. axios.post( 'https://yourdomain/generic/item', {table: 'users', fields: [ {username: 'whatever', role_id: 1}] } )
        'show_route_name' => 'item',

        //Route to delete a generic item.
        //Deafault 'delete' e.g. axios.post( 'https://yourdomain/generic/delete', {table: 'users', id: 1} )
        'delete_route_name' => 'delete',

    ],

    'pagination' => [

        //Items per page. Default 20.
        'itemsPerPage' => 20,

        //Name of the param of the current page e.g. axios.post( 'https://yourdomain/generic/delete', {table: 'users', page: 1} )
        'name_param_page' => 'page',

        //Name of the param of the number of items per page e.g. axios.post( 'https://yourdomain/generic/list', {table: 'users', page: 1, itemsPerPage: 30} )
        'name_param_item_per_page' => 'itemsPerPage',

    ],

];
