<?php

namespace DigitalRisks\LaravelEventStore;

use DigitalRisks\LaravelEventStore\Console\Commands\EventStoreReset;
use DigitalRisks\LaravelEventStore\Console\Commands\EventStoreWorker;
use DigitalRisks\LaravelEventStore\Console\Commands\EventStoreWorkerThread;
use DigitalRisks\LaravelEventStore\Contracts\ShouldBeStored;
use DigitalRisks\LaravelEventStore\EventStore;
use DigitalRisks\LaravelEventStore\Listeners\SendToEventStoreListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/eventstore.php' => config_path('eventstore.php'),
            ], 'config');

            $this->commands([
                EventStoreWorker::class,
                EventStoreWorkerThread::class,
                EventStoreReset::class,
            ]);
        }

        $this->eventClasses();
        $this->threadLogger();
        $this->workerLogger();
        $this->workerErrorLogger();

        Event::listen(ShouldBeStored::class, SendToEventStoreListener::class);
    }

    /**
     * Set the eventToClass method.
     *
     * @return void
     */
    public function eventClasses()
    {
        if (empty(EventStore::$eventToClass)) {
            EventStore::eventToClass();
        }
    }

    /**
     * Handle logging when event is triggered.
     *
     * @return void
     */
    public function threadLogger()
    {
        if (empty(EventStore::$threadLogger)) {
            EventStore::threadLogger();
        }
    }

    /**
     * Handle passing of std::out output from thread
     *
     * @return void
     */
    public function workerLogger()
    {
        if (empty(EventStore::$workerLogger)) {
            EventStore::workerLogger();
        }
    }

    /**
     * Handle passing of std:err output from thread
     *
     * @return void
     */
    public function workerErrorLogger()
    {
        if (empty(EventStore::$workerErrorLogger)) {
            EventStore::workerErrorLogger();
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/eventstore.php', 'eventstore');

        $this->app->singleton(EventStore::class, function () {
            return new EventStore;
        });
    }
}
