Sendgrid plugin for Kanboard
============================

[![Build Status](https://travis-ci.org/kanboard/plugin-sendgrid.svg?branch=master)](https://travis-ci.org/kanboard/plugin-sendgrid)

Use [Sendgrid](https://sendgrid.com/) to create tasks directly by email or to send notifications.

- Send emails through Sendgrid API
- Create tasks and attachments from incoming emails

Author
------

- Frederic Guillot
- License MIT

Requirements
------------

- Kanboard >= 1.0.39
- Sendgrid API credentials

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/Sendgrid`
3. Clone this repository into the folder `plugins/Sendgrid`

Note: Plugin folder is case-sensitive.

Use Sendgrid to send emails
---------------------------

To use this feature, you have to **create a new API key** in Sendgrid web interface which as **full access to Mail Send**.

The API key must have the permission to send Emails:

![Permissions](https://cloud.githubusercontent.com/assets/323546/22630453/5676af00-ebc8-11e6-949a-8de4ca4ee83e.png)

The API secret key is visible only one time, **do not use the API key ID**:

![API key](https://cloud.githubusercontent.com/assets/323546/22630480/cfabac9a-ebc8-11e6-9328-5c18d34a2d50.png)

### Configuration with the user interface

Set your API credentials on the settings page (**Application Settings > Integrations > Sendgrid**) and set the mail transport to "sendgrid" (**Application Settings > Email Settings**).

### Configuration with the config file (alternative method)

Define those constants in your `config.php` file to send notifications with Sendgrid:

```php
// We choose "sendgrid" as mail transport
define('MAIL_TRANSPORT', 'sendgrid');

// Sendgrid password
define('SENDGRID_API_KEY', 'YOUR_SENDGRID_API_KEY');
```

Use Sendgrid to create tasks from emails
----------------------------------------

This integration works with the [Parse API of Sendgrid](https://sendgrid.com/docs/API_Reference/Webhooks/parse.html).
Kanboard use a webhook to handle incoming emails.

### Sendgrid configuration

1. Create a new domain or subdomain (by example **inbound.mydomain.tld**) with a MX record that point to **mx.sendgrid.net**
2. Add your domain and the Kanboard webhook url to [the configuration page in Sendgrid](https://app.sendgrid.com/settings/parse)

The Kanboard webhook URL is displayed in **Settings > Integrations > Sendgrid**

### Kanboard configuration

1. The sender must have the same email address in Kanboard and must be member of the project
2. Assign a project email address in **Project Edit**
3. Send an email to your project

Troubleshooting
---------------

- Do not use the API Key ID but the API secret key which is visible only after the API key creation
- Make sure your API key has the permission to send emails (full access on "Mail Send")
- Enable debug mode and check logs

Changes
-------

### Version 1.0.6

- Use project email address instead of project identifier
- Create task in first active swimlane
- Add email body as task attachment
- Add support for attachments
- Use Sendgrid APIv3 to send emails
