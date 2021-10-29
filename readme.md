## <p align="center">A generic and agnostic Laravel Resource and ResourceCollection</p>



```php
  use Alcidesrh\Generic\GenericResource;

  $user = User::find(1);

  // It will only return the id and name fields.
  return new GenericResource( $user, ['id', 'name']);
```

This package can help you to return data as a traditional Laravel Resource without making a Resource or ResourceCollection for every single Model.

<!-- Sometimes you may need just the id and name fields of an entity: e.g. to list it in an input select.

Maybe you can use an existing Resource of that entity, but if that Resource returns more that the id and name fields, then
you are doing data **overfetching** that can slow down the app and it could bring others issues like memory leaks for example.

Another solution is to make a dedicated Resource for that particular case, but as the app grows, you will find yourself making a new
Resource for every single case, even when you need to fetch some data that doesn't require a complex transformation. -->

<!-- `GenericResource` and `GenericResourceColecction` implement a solution to deal with that.   -->
<br>

 <details open="open">
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#requirements">Requirements</a></li>
    <li><a href="#installation">Installation</a></li>
    <li>
      <a href="#usage">Usage</a>
      <ul>
        <li><a href="#genericresource">GenericResource</a></li>
        <li><a href="#genericresourcecollection">GenericResourceCollection</a></li>
        <li><a href="#genericcontroller">GenericController</a>
    <ul>
        <li><a href="#route-namespace-and-pagination-configuration">Route namespace and pagination configuration</a></li>
      </ul>
    </li>
      </ul>
    </li>   
  </ol>
</details>
<br>

## Requirements

-Laravel >= 5  
-php >= 7.0

<br>

## Installation

```sh
composer require alcidesrh/laravel-generic-resource
```

<br>

## Usage

#### GenericResource

```php
  use Alcidesrh\Generic\GenericResource;

  $user = User::find(1);

  //it will only return the id and name fields.
  return new GenericResource( $user, ['id', 'name']);
```

<br>

**Working with nested or related models:**

Supose the User class has a parent property of type User class as well, a `belongsTo` relation with itself. And also User class has a `belongsToMany` relation with Product class. So `$user->parent` returns an intance of User class and `$user->products` a collection of intances of Product class.

Let say that with want a list of users with just these fields: id, name, parent (only id and name fields of the parent) and products list (only id, name and price fields of the product). This is how we can get only those data:

```php
  use Alcidesrh\Generic\GenericResource;

  $user = User::find(1);
  return new GenericResource( $user, [
      'id',
      'name',
      'parent' => ['id', 'name'],
      'products' => ['id', 'name', 'price']
  ]);
```

<br>
You can add many nested level as the relations allow:  
<br>
<br>

```php
    ...
    'products' => [
        'id',
        'name',
        'price',
        'order' => ['id', 'created_at', 'company' => ['id', 'name']]
    ]
```

<br>

**Important:** In order to return nested relations data it is required make the query through the model's Facade.

```php
    // this will work
    new GenericResource( User::find(1), ['id', 'name'] );

    // this will work
    new GenericResource( User::find(1), ['id', 'name', 'parent' => ['id', 'name']] );

    // this will work
    new GenericResource( DB::table('users')->where('id', 1)->first(), ['id', 'name'] )

    // this won't
    new GenericResource( DB::table('users')->where('id', 1)->first(), [
        'id',
        'name',
        'parent' => ['id', 'name']
        // it can not be access the parent property since the object retrieved is an stdClass type
    ] );
```

  <br>
 
**Note:**  
- If the second argument (the array of fields to get) is not supplied, all fields of the model will be returned.  
- If one of the fields to return doesn't exist in the model, will be omitted in the result array.

<br>

#### GenericResourceCollection

```php
   use Alcidesrh\Generic\GenericResourceCollection;

   $users = User::where('active', 1);
   // it will return a collection of user with only the id and name fields.
   return new GenericResourceCollection( $users->paginate( $perPage ), ['id', 'name']);

   //you can pass nested property as well as in the GenericResource
   return new GenericResourceCollection( $users->paginate( $perPage ), [
       'id',
       'name',
       'parent' => ['id', 'name'],
       'products' => ['id', 'name', 'price']
   ]);
```

<br>

**Note**: Both `GenericResource` and `GenericResourceCollection` classes were made following the guide line from the official [Laravel's Api Resources documentation](https://laravel.com/docs/8.x/eloquent-resources) with some extra code to make it generic and agnostic. So you can expect the same structure and behavior.

<br>

## GenericController

The main goal of this package is to provide an agnostic `GenericResource` and `GenericResourceCollection`. However this package also provides an
agnostic `GenericController` which can be used to fetch data that doesn't require a complex query or transformation, and it will return a `GenericResource` or `GenericResourceCollection` only with the fields that were requested or all fields if none was requested.

It can help to prevent overloading the app with routes and controller functions for every small and simple data portion required dynamically in the front-end via ajax.

The `GenericController` has five routes:

```php
Method: POST /generic/list
Method: POST /generic/create
Method: POST /generic/update
Method: POST /generic/item
Method: POST /generic/delete
```

  <br>

### /generic/list get a list. It return a GenericResourceCollection

```js
axios.post("/generic/list", {
  // table to query
  table: "users",
  // page to return
  page: 1,
  // item per page
  itemsPerPage: 10,
  // fileds to return
  fields: ["id", "name", "created_at", "role_id", "email", "company_id"],
  // where clause: rule is column: value or column: {operator: someoperator, value: somevalue}
  // operator value should be some of these: '=', '!=', '<', '<=', '>', '>=', '<>', 'like', 'contain'
  where: {
    // will generate ( created_at > '2021-03-11 20:26:00.0' )
    created_at: { operator: ">", value: "2020-09-11 20:26:00.0" },
    // will generate ( email_verified_at IS NOT NULL )
    email_verified_at: { operator: "!=", value: null },
    // when the operator's parameter is omitted the default operator will be '=', will generate ( role_id = 2 )
    role_id: 2,
    // the non-existent 'contain' will generate ( email LIKE %legendary% AND email LIKE %zangetsu% )
    // this example zangetsu.ins@company.com and jhon.legendary.dc@company.com will match
    email: { operator: "contain", value: ["legendary", "zangetsu"] },
  },
  //orWhere clause accept same rules as simple where with one more
  orWhere: {
    // you can pass an array as a value, it will generate (role_id = 1 OR role_id = 2)
    role_id: [1, 2],
    // will generate (role_id != 1 OR role_id != 2)
    role_id: { operator: "!=", value: [1, 2] },
  },
  whereIn: {
    // return items with those ids
    id: [1, 23, 35],
  },
  whereNotIn: {
    // return items with neither of these ids
    id: [1, 23, 35],
  },
  whereBetween: {
    // return items with price between 25 and 35
    price: [25, 35],
  },
  // return items with price less than 25 and greater than 35
  whereNotBetween: {
    id: [25, 35],
  },
  // order by id ascendingly of course the value can be DESC
  orderBy: {
    id: "ASC",
  },
});
```

<br>

**Note:** It is not posible to ask for nested relations data in the `fields` parameter above due the generic nature of the query. DB Facade is used to make the query, which returns stdClass type.

<br>

### /generic/create create an item. It will return a GenericResource

```js
axios.post("/generic/create", {
  table: "roles",
  // fields to return in the GenericResource once created
  fields: ["id", "name"],
  // values: pair column: value
  values: {
    name: "Admin",
    slug: "admin",
  },
  // can insert many in one request
  many: [
    {
      name: "User editor",
      slug: "user-editor",
    },
    {
      name: "Forum admin",
      slug: "forum-admin",
    },
  ],
});
```

  <br>
  
  ### /generic/update update an item. It will return a GenericResource

```js
axios
.post("/generic/update", {
  table: "roles",
  // id of the item to update
  id: 3,
  // many ids to update many items with the same values in one request.
  many: [35, 36, 37]
  // fields to return in the GenericResource once updated
  fields: ["id", "name"],
  // values: pair column: value
  values: {
    name: "Room Admin",
    slug: "room-admin",
  },
});
```

  <br>
  
  ### /generic/item to get an item. It will return a GenericResource

```js
axios.post("/generic/delete", {
  table: "user",
  // id of the item to delete
  id: 3,
  //fields to return in the GenericResource
  fields: ["id", "name", "slug"],
});
```

  <br>
  
  ### /generic/delete delete an item  
  
  ```js
  axios
  .post("/generic/delete", {
    table: "user",
    // id of the item to delete
    id: 3,
  });
  ```

  <br>

## Route namespace and pagination configuration

Once installed runing console command ` php artisan vendor:publish` will publish the package's configuration. It can also be done manually copy /vendor/alcidesrh/generic-resource.php to /config  
<br>

**/config/generic-resource.php**

```php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel generic Resource package configuration
    |--------------------------------------------------------------------------
    |
     */
    // configure route and prefix
    // e.g. to have this route https://yourdomain/agnostic/items for items list
    // change prefix: agnostic and list_route_name: items
    'route' => [

        //Route's prefix for generic CRUD(create, read, update and delete) operations
        //Deafault 'generic' e.g.: axios.post( 'https://yourdomain/generic' )
        'prefix' => 'generic',

        //Route for list of generic items.
        //Deafault 'list' e.g. axios.post( 'https://yourdomain/generic/list' )
        'list_route_name' => 'list',

        //Route to create an item.
        //Deafault 'create' e.g. axios.post( 'https://yourdomain/generic/create', {table: 'users', values: [ {username: 'whatever', role_id: 1}], field: [id, username] } )
        'create_route_name' => 'create',

        //Route to update an item.
        //Deafault 'update'  e.g. axios.post( 'https://yourdomain/generic/update', {table: 'users', id: 1, values: [ {username: 'whatever', role_id: 1}], field: [id, username] } )
        'update_route_name' => 'update',

        //Route to get an item.
        //Deafault 'item' e.g. axios.post( 'https://yourdomain/generic/item', {table: 'users', fields: [ {username: 'whatever', role_id: 1}] } )
        'show_route_name' => 'item',

        //Route to delete a generic item.
        //Deafault 'delete' e.g. axios.post( 'https://yourdomain/generic/delete', {table: 'users', id: 1} )
        'delete_route_name' => 'delete',
    ],
    // configure pagination items per page and parameters names.
    'pagination' => [

        //Items per page. Default 20.
        'itemsPerPage' => 20,

        //Name of the param of the current page e.g. axios.post( 'https://yourdomain/generic/delete', {table: 'users', page: 1} )
        'name_param_page' => 'page',

        //Name of the param of the number of items per page e.g. axios.post( 'https://yourdomain/generic/list', {table: 'users', page: 1, itemsPerPage: 30} )
        'name_param_item_per_page' => 'itemsPerPage',
    ],
];
```

<br>

_If you find this package useful please consider star it. Thank you._

### License

This Generic Resource package for Laravel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
