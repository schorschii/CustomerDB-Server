# CardDAV API
The customer database server provides a *read-only* CardDAV API. This allows the user to sync his customers into CardDAV compatible clients such as:

- AVM FRITZ!Box Router Phone Book (for usage with your DECT phone)
  - does currently not support contact images [see here](https://avm.de/service/wissensdatenbank/dok/FRITZ-Box-7590/300_Hintergrund-und-Anruferbilder-in-FRITZ-Fon-einrichten)
- Tunderbird (via AddOn "CardBook")
- Evolution
- macOS/iOS Contacts App

## Credentials
Please enter the complete URL to `web/carddav.php` as server URL in your client, for example: `https://example.com/web/carddav.php` on your own managed server. Many clients only support HTTPS, so please make sure your server has a valid certificate.

As username and password, please enter the account email address with the corresponding password.

The contacts app on macOS behaves in a special way, in CardDAV account setup you have to choose "Advanced" and enter hostname and URL to `web/carddav.php` separately, e.g. `example.com` and `/carddav.php`.

## Debugging
### macOS/iOS Contacts App
https://sabre.io/dav/clients/osx-addressbook/
