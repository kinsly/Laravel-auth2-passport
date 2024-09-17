
** Tutorial
1. create laravel project
```composer create-project laravel/laravel passport-authentication```
2. Install Laravel Passport API packages
```php artisan install:api --passport```

Make sure to use UUIDs while above installation.
```
Would you like to use UUIDs for all client IDs? (yes/no)
Please add the [Laravel\Passport\HasApiTokens] trait to your User model.  
```
3. Add ```HasApiTokens``` to User model
4. Add below gurd to Auth Provider
```
'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
```
5. Add Following to AppServiceProvider
```
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();
    }
```

6. Create clients for you API
```php artisan passport:client --password```


** Postman
1. Create collection varilable ```ACCESS-TOKEN```
2. Create post response script for login and register
```
// Extract the token and refresh token from the response
var jsonData = pm.response.json();
var token = jsonData.data.token.access_token;
var refresh_token = jsonData.data.token.refresh_token;

// Set token and refresh token to collection variables
pm.collectionVariables.set('ACCESS-TOKEN', token);
pm.collectionVariables.set('REFRESH-TOKEN', refresh_token);

```
3. add ```ACCESS-TOKEN``` variable to Authorization as a bearer token so that every request you made has bearer token included.

*** Refresh Token
1. Add below script to post response of the refresh token request
    ```
    // Extract the token from the response
    var jsonData = pm.response.json();
    var token = jsonData.data.access_token;
    var refresh_token = jsonData.data.refresh_token;

    // Set Collection Varilable XSRF-TOKEN to access all other request
    pm.collectionVariables.set('ACCESS-TOKEN', token);
    pm.collectionVariables.set('REFRESH-TOKEN', refresh_token);
    ```
2. Create collection variable ```REFRESH-TOKEN``` and set post body variable ```refresh_token``` and collection variable as the value for refresh token

** Using Mail service - Resend

Change mail mailer to resend in .env ```MAIL_MAILER=resend ```
Next create API Key and add ```RESEND_KEY=xxxxxxxxxx```

** FAQ

Q. Curl error: Operation timed out after ...
A. Answer mention here only applied to this scenario in a local environment. CURL on Windows or MAC does not work with a port. CURL does not work if your App url on .env file is ```http://127.0.0.1:8000``` or ```http://localhost:8000```. That's why you receive operation timed out curl error for laravel http requests. You can solve this issue by creating a virtual host with a localhost address like ```http://jwt.localhost.com``` or running the laravel application without a port.

Q. How to create vhost using xmapp?
1. Based on the XAMPP default installation location for Windows go to ```C:\xampp\apache\conf\extra```. 
2. Open ```httpd-vhosts.conf```
3. Add following vhost
```
<VirtualHost *:80>
    ServerAdmin webmaster@guestpost.example.com
    # example: D:\external-project\Laravel\passport-authentication\public/
    DocumentRoot "location of the laravel project public folder"
    ServerName jwt.localhost
    ErrorLog "logs/jwt.localhost.com-error.log"
    CustomLog "logs/jwt.localhost.com-access.log" common
     
    <Directory "location of the laravel project folder">
      Options Indexes FollowSymLinks MultiViews
      AllowOverride all
      Order Deny,Allow
      Allow from all
      Require all granted
      Require all granted    
    </Directory>

</VirtualHost>

```

