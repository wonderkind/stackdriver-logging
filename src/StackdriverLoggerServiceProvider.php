<?php
declare(strict_types=1);

namespace Wonderkind\StackdriverLogging;

use Google\Cloud\Logging\LoggingClient;
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
            __DIR__ . '/config/stackdriver.php' => $this->configFilePath(),
        ]);
    }

    /**
     *
     */
    public function register(): void
    {
        $this->mergeStackdriverConfig();
        $this->bindLoggingClient();
        $this->bindLogHandler();
        $this->bindLogger();
    }

    /**
     *
     */
    private function mergeStackdriverConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/stackdriver.php', 'stackdriver');
    }

    private function bindLogger(): void
    {
        $this->app->singleton(LoggerInterface::class, function ($app) {
            $monolog = new \Monolog\Logger('stackdriver', [$app->make(StackdriverLoggingHandler::class)]);

            return new StackdriverLogger($monolog);
        });
    }

    /**
     *
     */
    private function bindLoggingClient(): void
    {
        $this->app->bind(LoggingClient::class, function () {
            return new LoggingClient(['projectId' => config('stackdriver.project_id')]);
        });
    }

    /**
     *
     */
    private function bindLogHandler(): void
    {
        $this->app->bind(StackdriverLoggingHandler::class, function ($app) {
            /** @var LoggingClient $loggingClient */
            $loggingClient = $app->make(LoggingClient::class);

            return new StackdriverLoggingHandler($loggingClient->psrLogger(config('stackdriver.log')));
        });
    }

    /**
     * @return string
     */
    private function configFilePath(): string
    {
        // The config_path helper is only available in Laravel
        // so we need two different ways to make the config file path
        //
        // Laravel:
        if (function_exists('config_path')) {
            return config_path('stackdriver.php');
        }

        // Lumen:
        return $this->app->basePath() . '/config/stackdriver.php';
    }
}
