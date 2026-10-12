<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * イベントとリスナーの対応関係を定義する。
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * アプリケーションのイベントを登録する。
     */
    public function boot(): void
    {
        //
    }

    /**
     * イベントとリスナーの自動検出を有効にするか判定する。
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
