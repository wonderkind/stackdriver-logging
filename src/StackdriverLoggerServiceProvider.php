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
    }

    /**
     *
     */
    protected function mergeStackdriverConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/stackdriver.php', 'stackdriver');
    }

    /**
     *
     */
    protected function bindLoggingClient(): void
    {
        $this->app->bind(LoggingClient::class, function () {
            return new LoggingClient(['projectId' => config('stackdriver.project_id')]);
        });
    }

    /**
     *
     */
    protected function bindLogHandler(): void
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
        if (function_exists('config_path')) {
            return config_path('stackdriver.php');
        }

        return $this->app->basePath() . '/config/stackdriver.php';
    }
}
