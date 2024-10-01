
  

# About
Full OAuth2 server API implementation with Laravel 11. Includes
1. Authentication routes login, register, forgot password, password reset
2. Mail verification
3. PHP Feature tests
4. Postman files with API documentation 

## Setup
1. Run ```git clone ```
2. Run ```Composer install```
3. Rename .env example file to .env using ```mv .env.example .env```
4. Add application key using ```php artisan key:generate```
5. Add your database information to ```.env```
6. Update ```APP_URL``` on .env file to match the url of the application. It is mandatary to run auth server correctly.
7. run ```php artisan migrate```
8. Install passport using ```php artisan install:api --passport```
9. Create passport clients to use API ```php artisan passport:client --password```
10. If you are testing this application locally using XAMPP Windows make sure to use localhost without any ports or use vhosts. If you plan to use vhost use below code on Apache host configurations
```
Default File Location: 
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```
```
<VirtualHost *:80>
	ServerAdmin webmaster@guestpost.example.com
# example: D:\Laravel\passport-authentication\public/

 DocumentRoot "location of the laravel project public folder"
 ServerName jwt.localhost
 ErrorLog "logs/jwt.localhost.com-error.log"
 CustomLog "logs/jwt.localhost.com-access.log" common

#Not including public folder. Location to project folder only
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
11. Test application using postman requests. Check Postman Collection folder of this project for all postman request files. Import them.

## Mail Verification after registration
If you update ```.env``` file with correct resend key you will receive mail activation link. Get that link and paste that link on postman to activate.

## Password Reset
Send request named  as "password-reset-request" on postman to get password reset mail.
Send post request to server with form data with token, email and password with password confirmation. Check "reset-password" request. Add token value in the url to token value in the form data. Make sure to send request for 
```http://localhost/api/reset-password```

## Tutorial to integrate passport on Laravel

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

4. Add below guard to ```config/auth```

```
'api' => [
	'driver' => 'passport',
	'provider' => 'users',
	],
```

5. Add Following to ```Providers/AppServiceProvider```

```
/**
* Bootstrap any application services.
*/
public function boot(): void
{
	Passport::enablePasswordGrant();
}
```
6. Create a password client for you Auth2.0 API

```php artisan passport:client --password```

Add client id and client secret to ```.env```
```
PASSPORT_PASSWORD_CLIENT_ID=
PASSPORT_PASSWORD_SECRET=
```

7. Mail Verification
Change mail mailer to resend in .env ```MAIL_MAILER=resend ```. Next create a free account on resend mail service. Add Resend API key to .env
```
RESEND_KEY=
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME=Acme
```
## Database
Run below command to create required tables
```
php artisan migrate
```
## Tests
All the tests are available  ```Tests\Feature``` folder. Run below artisan command to run available tests.
``` php artisan test```
## Postman Configurations

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

### Refresh Token API request

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
# example: D:\Laravel\passport-authentication\public/

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