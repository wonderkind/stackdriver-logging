# Stackdriver Monolog handler

Adds a Monolog handler to Laravel/Lumen

## Requirements

Laravel/Lumen >= 5.6.*

## Installation

First add the repository to your `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "no-api": true,
        "url":  "git@github.com:wonderkind/stackdriver-logging.git"
    }
]
```

Then add the package to the `require` section of your `composer.json`

```json
"require": {
    "wonderkind/stackdriver-logging": "@dev"
}
```

You could also run from you command line:

`composer require wonderkind/stackdriver-logging @dev`

## Configuration

First, make sure you are authenticated to Google Cloud Platform. This can be done by setting the `GOOGLE_APPLICATION_CREDENTIALS` environment variable. If this is not set, it will default to the service account for Compute Engine, Kubernetes Engine, App Engine, and/or Cloud Functions instances. See the [documentation](https://cloud.google.com/docs/authentication/production) for more information. 

Then set the optional `GOOGLE_APPLICATION_CREDENTIALS`variable, the project ID and the log you want to use in your `.env` and add stackdriver as a channel to the Laravel/Lumen configuration.

### .env

```
GOOGLE_STACKDRIVER_LOG=my-log
GOOGLE_STACKDRIVER_PROJECT_ID=my-project
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service_account_credentials.json
```

If you want to use stackdriver as your default logger driver also change the `LOG_CHANNEL` variable to `stackdriver`:

```
LOG_CHANNEL=stackdriver
```

### Laravel/Lumen

In `config/logging.php` add stackdriver to channels list.

```php
[
  'channels' => [
    'stackdriver' => [
            'driver' => 'monolog',
            'handler' => Wonderkind\StackdriverLogging\Handler\StackdriverLoggingHandler::class
        ]
  ]
]
```

If you always want to log to stackdriver besides your current stack, add stackdriver to the stack configuration:

```php
[
  'channels' => [
      'stack' => [
          'driver' => 'stack',
          'channels' => ['single', 'stackdriver'],
    ],
]
```

Make sure to set your `LOG_CHANNEL` environment variable to `stack`

### Lumen

In Laravel the Service Provider will be automatically discovered.  In Lumen you'll need to manually register it in `bootstrap/app.php`. Add this line to the file:

```php
$app->register(Wonderkind\StackdriverLogging\StackdriverLoggerServiceProvider::class);
```

# Usage

To use as default driver set stackdriver as  your default logger or add it as channel to the stack as described in the 'Configuration' section. When then using the Laravel logger, it will log everything to Stackdriver

## Inject via the Service Container

When stackdriver is not the default driver, you can get an (inherited) instance of the Laravel logger with the stackdriver handler by injecting the `\Wonderkind\StackdriverLogging\LoggerInterface`. 

## Using the `Log` Facade

When stackdriver is not the default driver, you can still log to Stackdriver by specifying the channel to log to:

```php
Log::channel('stackdriver')->info('Something happened!');
```

# Resources

- [Stackdriver PHP documentation](https://cloud.google.com/logging/docs/reference/libraries#client-libraries-install-php)
- [Stackdriver PHP library reference](https://googlecloudplatform.github.io/google-cloud-php/#/docs/google-cloud/v0.61.0/logging/loggingclient)
- [Google Cloud Platform authentication documentation](https://cloud.google.com/docs/authentication/production)
- [Laravel logging documentation](https://laravel.com/docs/5.6/logging)
