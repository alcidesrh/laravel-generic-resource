## <p align="center">A generic and agnostic Laravel's Resource and ResourceCollection.</p>

### This package can help you to fetch data as a traditional Laravel's Resource but without make a Resource for every single case.

 Let say sometimes you may need just the id and name fields of some entity: e.g. to list it in an input's select. 
 
 Maybe you can use an existing Resource of that entity but if that Resource return more that the id and name fields then you are doing data **overfetching** that can slow down the app and it could bring others issues like memory leaks for example. 
 
 Another solution is to make a dadicate Resource for that particular case but as the app it grows you will find yourself making a new Resource for every single case even when you need to fetch some data which no require a complex transformation.  

 ## Usage

 **Generic Resource example**:  

  ```php
    use Alcidesrh\Generic\GenericResource;

    $user = User::find(1); 

    //it will only return the id and name fields.
    return new GenericResource( $user, ['id', 'name']);
  ``` 

**Working with nested or related models**:

Supose the User class has a parent property of type User class as well of ```belongsTo``` relation with itself. And also User class has a ```belongsToMany``` relation with Product class. So ```$user->parent``` return an intance of User class and ```$user->products``` a collection of intances of Product class. 

Let say that with want a list of users with just these fields: id, name, parent (only id and name fields of the parent) and products list(only id, name and price fields of the product). This is how we can get those data:


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

**Important:** In order to return nested relations data it is require make the query on the Model Fascade.

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
        // it can not be access the this property
        // cause the the object recovered an stdClass
        'parent' => ['id', 'name'] 
    ] );
  ```

**Note:** If the second argument (the array of fields to get) is not supplied all fields of the model will be returned.

<br>

**Generic ResourceCollection example**

 ```php
    use Alcidesrh\Generic\GenericResourceCollection;

    $users = User::where('active', 1);  
    // it will return a collection of user with only the id and name fields.
    return new GenericResourceCollection( $users->paginate( $perPage ), ['id', 'name']);

    //you can pass nested property as well as the example before
    return new GenericResourceCollection( $users->paginate( $perPage ), [  
        'id',  
        'name',  
        'parent' => ['id', 'name'],  
        'products' => ['id', 'name', 'price']  
    ]);
  ```

**Note**: Both ```GenericResource``` and ```GenericResourceCollection``` classes were made following the guide line from the official *[Laravel's Api Resources documentation](https://laravel.com/docs/8.x/eloquent-resources)* with some extra code to make it generic and agnostic. So you can expect the same structure and behavior.

<br>

## Requirement

-Laravel >= 5  
-php >= 7.0

<br>

## Installation  

  ```sh
  composer require alcidesrh/laravel-generic-resource
  ```  
<br>

## GenericController

The main goal of this package is provide the agnostic ```GenericResource``` and ```GenericResourceCollection```. However this package provide also a generic or agnostic ```GenericController``` which can be used to fetch data which not require a complex query or transformation and return a ```GenericResource``` or ```GenericResourceCollection``` only with the fields that we require. 

It can help to not overload the app with routes and controller's functions for every small and simple data portion require dynamically.  

This ```GenericController``` has four routes than can be configured as will it be shown later:  
  ```php
  /generic/list    //return a GenericResourceCollection
  /generic/create: //return a GenericResource of the type created
  /generic/update: //return a GenericResource of the type updated
  /generic/delete: //return a true if the item was deleted
  ```  

###  Route /generic/list will return a GenericResourceCollection

 ```js
  axios
  .post("/generic/list", {
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
      created_at: {operator: '>', value: '2020-09-11 20:26:00.0'},
      // will generate ( email_verified_at IS NOT NULL )
      email_verified_at: {operator: '!=', value: null},
      // when the operator's parameter is omitted the default operator will be '=', will generate ( role_id = 2 )
      role_id: 2,
      // the non-existent 'contain' will generate ( email LIKE %legendary% AND email LIKE %zangetsu% )
      // this example zangetsu.ins@company.com and jhon.legendary.dc@company.com will match
      email: {operator: 'contain', value: ['legendary',  'zangetsu']},
      
    },
    //orWhere clause accept same rules as simple where with one more
    orWhere: {
      // you can pass an array as a value, it will generate (role_id = 1 OR role_id = 2)
      role_id: [1, 2],
      // will generate (role_id != 1 OR role_id != 2)
      role_id: {operator: '!=', value: [1,2]}
    },
    whereIn: {
      // return items with those ids
       id: [1, 23, 35]
    },
    whereNotIn: {
      // return items with neither of these ids
       id: [1, 23, 35]
    },
    whereBetween: {
      // return items with price between 25 and 35
       price: [25, 35]
    },
    // return items with price less than 25 and greater than 35
    whereNotBetween: {
       id: [25, 35]
    },
    // order by id ascendingly of course the value can be DESC
    orderBy:{
     id: 'ASC'
    }
  });
  ``` 


### Configuration
The defaults configuration settings are set in `config/dompdf.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:

    php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

You can still alter the dompdf options in your code before generating the pdf using this command:

    PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    
Available options and their defaults:
* __rootDir__: "{app_directory}/vendor/dompdf/dompdf"
* __tempDir__: "/tmp" _(available in config/dompdf.php)_
* __fontDir__: "{app_directory}/storage/fonts/" _(available in config/dompdf.php)_
* __fontCache__: "{app_directory}/storage/fonts/" _(available in config/dompdf.php)_
* __chroot__: "{app_directory}" _(available in config/dompdf.php)_
* __logOutputFile__: "/tmp/log.htm"
* __defaultMediaType__: "screen" _(available in config/dompdf.php)_
* __defaultPaperSize__: "a4" _(available in config/dompdf.php)_
* __defaultFont__: "serif" _(available in config/dompdf.php)_
* __dpi__: 96 _(available in config/dompdf.php)_
* __fontHeightRatio__: 1.1 _(available in config/dompdf.php)_
* __isPhpEnabled__: false _(available in config/dompdf.php)_
* __isRemoteEnabled__: true _(available in config/dompdf.php)_
* __isJavascriptEnabled__: true _(available in config/dompdf.php)_
* __isHtml5ParserEnabled__: false _(available in config/dompdf.php)_
* __isFontSubsettingEnabled__: false _(available in config/dompdf.php)_
* __debugPng__: false
* __debugKeepTemp__: false
* __debugCss__: false
* __debugLayout__: false
* __debugLayoutLines__: true
* __debugLayoutBlocks__: true
* __debugLayoutInline__: true
* __debugLayoutPaddingBox__: true
* __pdfBackend__: "CPDF" _(available in config/dompdf.php)_
* __pdflibLicense__: ""
* __adminUsername__: "user"
* __adminPassword__: "password"

### Tip: UTF-8 support
In your templates, set the UTF-8 Metatag:

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

### Tip: Page breaks
You can use the CSS `page-break-before`/`page-break-after` properties to create a new page.

    <style>
    .page-break {
        page-break-after: always;
    }
    </style>
    <h1>Page 1</h1>
    <div class="page-break"></div>
    <h1>Page 2</h1>
    
### License

This DOMPDF Wrapper for Laravel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
