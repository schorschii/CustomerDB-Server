# Customer Database Server
With this PHP web app you can set up your own server for the [Android](https://github.com/schorschii/customerdb-android) and [iOS](https://github.com/schorschii/customerdb-ios) app "Customer Database".

[![Play Store](web/frontend/img/play-store-badge.svg)](https://play.google.com/store/apps/details?id=de.georgsieber.customerdb)
[![App Store](web/frontend/img/app-store-badge.svg)](https://apps.apple.com/us/app/customer-database/id1496659447)

## Highlights
- multi-tenant capable
- *read-only* [CardDAV API](<docs/CardDAV API.md>) (e.g. for syncing with your email client or router phone book (for DECT phone))

## System Requirements
- Linux OS (Debian recommended)
- Apache 2 Webserver + PHP 7
- MySQL/MariaDB Database Server

## Installation (On A Root Server)
0. Install Apache 2, PHP 7 (with `php-curl`) and MySQL/MariaDB on a Linux server.
1. Download the [latest release](https://github.com/schorschii/customerdb-server/releases) and unpack it into `/srv/www/customerdb`.
2. Set your Apache (virtual host) web root directory to `/srv/www/customerdb/web` by editing the corresponding configuration file inside `/etc/apache2/sites-enabled`.
3. Create a database on your MySQL server and import the schema from `sql/customerdb.sql`.
4. Edit/create `conf.php` from the example file (`conf.php.example`) and enter your MySQL credentials. Please do not use the root user but create a new user which is only allowed to operate on the specific database.
5. Select "Own Server" in the settings of your Customer Database app and enter the full URL to the `web/api.php` script. Example: `http://192.168.2.10/api.php`.
6. Create an account. You can do this in the app (if the API and registration is enabled in `conf.php`) or by using the command line tool on the server (`php console.php createuser <username> <password>`).

## Installation (On A Managed Server)
1. Download the [latest release](https://github.com/schorschii/customerdb-server/releases) and unpack it into your webspace.
2. Create a database on your MySQL server and import the schema from `sql/customerdb.sql` using phpMyAdmin or a similar web-based tool from your hosting provider.
3. Edit/create `conf.php` from the example file (`conf.php.example`) and enter your MySQL credentials.
4. Select "Own Server" in the settings of your Customer Database app and enter the full URL to the `web/api.php` script. Example: `http://example.com/web/api.php`.
5. Ensure that the API and registration is enabled in `conf.php`. Now create a sync account inside the app.
6. After you created your personal account on your server, you may now want to disable the registration in `conf.php`.

## Further (Optional) Steps
Especially if your server is available from the internet (and not only locally in your home network):
- it is highly recommended to setup HTTPS on your web server
- it is highly recommended to setup fail2ban on your web server (see [lib/fail2ban](lib/fail2ban/README.md) for more information)
- you may want to disable the user registration in the `conf.php` file (you can also disable the API or the web frontend here)

## Upgrade
For upgrading your server to a newer version, please read [Upgrade.md](docs/Upgrade.md).

## (API) Documentation
Please have a look at the [docs](docs/README.md) folder.

## Development Status
Currenty, these scripts only provide the API. It is planned to provide a full web frontend for creating and editing customer records in the web browser.
