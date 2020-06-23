# Customer Database Server
With this PHP web app you can set up your own server for the [Android](https://play.google.com/store/apps/details?id=de.georgsieber.customerdb) and [iOS](https://apps.apple.com/us/app/customer-database/id1496659447) app "Customer Database".

## System Requirements
- (Debian) Linux OS
- Apache 2 Webserver + PHP 7
- MySQL/MariaDB Database Server

## Upgrade
For upgrading your server to a newer version, please read UPGRADE.md.

## Installation
0. Install Apache 2, PHP 7 (with `php-curl`) and MySQL/MariaDB server.
1. Download the latest release and unpack into `/srv/www/customerdb`.
2. Set your Apache vHost web root directory to `/srv/www/customerdb/web`.
3. Create a database on your MySQL server and import the schema from `lib/sql/customerdb.sql`.
4. Edit/create `conf.php` from example file and enter your MySQL credentials. Please do not use the root user but create a new user which is only allowed to operate on the specific database.
5. Select "Own Server" in the settings of your Customer Database app and enter the full URL to `web/api.php`. Example: `http://192.168.2.10/api.php`.
6. Create an account. You can do this in the app (if API and registration is enabled in `conf.php` file) or by using the command line tool (`php console.php createuser <username> <password>`).

## Further (Optional) Steps
- It is highly recommended to setup HTTPS on your web server.
- It is highly recommended to setup fail2ban on your web server. See `lib/fail2ban` for more information.
- You may want to disable the user registration in the `conf.php` file. You can also disable the API or the web frontend here.

## API Documentation
https://github.com/schorschii/customerdb-server/wiki/API-Documentation-(JSON-REST-API)

## Development Status
Currenty, these scripts only provide the API. It is planned to provide a full web frontend for creating and editing customer records in the web browser.
