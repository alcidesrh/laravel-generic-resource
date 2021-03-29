## <p align="center">A generic and agnostic Laravel's Resource and ResourceCollection.</p>

### This package can help you to fetch data as a traditional Laravel's Resource but without make a Resource for every single case.

 Let say sometimes you may need just the id and name fields of some entity: e.g. to list it in an input's select. 
 
 Maybe you can use an existing Resource of that entity but if that Resource return more that the id and name fields then you are doing data **overfetching** that can slow down you app and it could bring others issues like memory leaks for example. 
 
 Another solution is to make a dadicate Resource for that particular case but as the app it grows you will find yourself making a new Resource for every single case even when you need to fetch some data which no require a complex transformation.  

 ## Usage

 **Generic Resource example**:

    use Alcidesrh\Generic\GenericResource;
    ...
    $user = User::find(1);
    return new GenericResource($user, ['id', 'name']); //it will only return the id and name fields.

**Working with nested or related models**:

Supose the User class has a parent property of type User class as well in a type belongsTo relation with itself. And also User class has a type belongsToMany relation with Product class. So $user->parent return an intance of User class and $user->products a collection of intances of Product class. 

Let say that with want a list of users with just the these fields: id, name, parent (just id and name fields of the parent) and products list(just id, name and price fields of the product). This is how we can get those data:

    use Alcidesrh\Generic\GenericResource;
    ...
    $user = User::find(1);
    return new GenericResource($user, [  
        'id', 'name',  
        'parent' => ['id', 'name'],  
        'products' => ['id', 'name', 'price']  
    ]); 
  
  

You can add many nested level as the relations allow  
    
    ...
    'products' => [  
        'id',  
        'name',  
        'price',  
        'order' => ['id', 'created_at', 'company' => ['id', 'name']]]**  
  
  

**Note:** If the second argument (array of fields to get) is not supplied all fields of the model will be returned.

**Generic ResourceCollection example**

    use Alcidesrh\Generic\GenericResourceCollection;
    ...
    $users = User::where('active', 1);
    return new GenericResourceCollection( $users->paginate( $perPage ), ['id', 'name']);

    //you can pass nested property as well as the example before
    return new GenericResourceCollection( $users->paginate( $perPage ), ['id', 'name', 'parent' => ['id', 'name'], 'products' => ['id', 'name', 'price']]);

**Note**: Both GenericResource and GenericResourceCollection classes are the same types referenced in the official *[Laravel's Api Resources documentation](https://laravel.com/docs/8.x/eloquent-resources)* with some extra code to make it generic and agnostic. So you can expect the same structure and behavior.

## Requirement

-Laravel >= 5  
-Php >= 7.0

## Installation

    composer require alcidesrh/laravel-generic-resource


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
