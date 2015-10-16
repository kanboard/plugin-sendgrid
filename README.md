Sendgrid plugin for Kanboard
============================

Use [Sendgrid](https://sendgrid.com/) to create tasks directly by email or to send notifications.

- Send emails through Sendgrid API
- Create tasks from incoming emails

Author
------

- Frederic Guillot
- License MIT

Installation
------------

- Create a folder **plugins/Sendgrid**
- Copy all files under this directory

Use Sendgrid to send emails
---------------------------

Define those constants in your `config.php` file to send notifications with Sendgrid:

```php
// We choose "sendgrid" as mail transport
define('MAIL_TRANSPORT', 'sendgrid');

// Sendgrid username
define('SENDGRID_API_USER', 'YOUR_SENDGRID_USERNAME');

// Sendgrid password
define('SENDGRID_API_KEY', 'YOUR_SENDGRID_PASSWORD');
```

Use Sendgrid to create tasks from emails
----------------------------------------

This integration works with the [Parse API of Sendgrid](https://sendgrid.com/docs/API_Reference/Webhooks/parse.html).
Kanboard use a webhook to handle incoming emails.

### Sendgrid configuration

1. Create a new domain or subdomain (by example **inbound.mydomain.tld**) with a MX record that point to **mx.sendgrid.net**
2. Add your domain and the Kanboard webhook url to [the configuration page in Sendgrid](https://sendgrid.com/developer/reply)

The Kanboard webhook url is displayed in **Settings > Integrations > Sendgrid**

### Kanboard configuration

1. Be sure that your users have an email address in their profiles
2. Assign a project identifier to the desired projects: **Project settings > Edit**
3. Try to send an email to your project: something+myproject@mydomain.tld
