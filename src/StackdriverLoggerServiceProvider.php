<?php
declare(strict_types=1);

namespace Wonderkind\StackdriverLogging;

use Google\Cloud\Logging\LoggingClient;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Wonderkind\StackdriverLogging\Handler\StackdriverLoggingHandler;

class StackdriverLoggerServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/stackdriver.php' => config_path('stackdriver.php'),
        ]);
    }

    /**
     *
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/stackdriver.php', 'stackdriver');
        $this->app->bind(LoggingClient::class, function () {
            return new LoggingClient(['projectId' => config('stackdriver.project_id')]);
        });

        $this->app->bind(StackdriverLoggingHandler::class, function (Application $app) {
            /** @var LoggingClient $loggingClient */
            $loggingClient = $app->make(LoggingClient::class);

            return new StackdriverLoggingHandler($loggingClient->psrLogger(config('stackdriver.log')));
        });

        $this->app->bind(Logger::class, function (Application $app) {
            return new Writer(
                new \Monolog\Logger('stackdriver', [$app->make(StackdriverLoggingHandler::class)]),
                $app->make(Dispatcher::class)
            );
        });
    }
}
