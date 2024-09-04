
** Tutorial

Would you like to use UUIDs for all client IDs? (yes/no)
Please add the [Laravel\Passport\HasApiTokens] trait to your User model.  



** Postman
1. Create collection varilable ```ACCESS-TOKEN```
2. Create post response script for login and register
```
// Extract the token from the response
var jsonData = pm.response.json();
var token = jsonData.data.token.access_token;;

// Set Collection Varilable XSRF-TOKEN to access all other request
pm.collectionVariables.set('ACCESS-TOKEN', token);

```
3. add ```ACCESS-TOKEN``` variable to Authorization as a bearer token so that every request you made has bearer token included.

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

