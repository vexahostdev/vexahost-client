<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cegah Chrome warning: "was preloaded using link preload but not used" akibat Cloudflare Early Hints / Rocket Loader
        Vite::usePreloadTagAttributes(fn () => false);

        if (request()->header('x-forwarded-proto') === 'https' || (request()->server('HTTP_X_FORWARDED_PROTO') === 'https') || request()->isSecure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Listener MessageSending: pengaman untuk memastikan Reply-To selalu terisi
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, function (\Illuminate\Mail\Events\MessageSending $event) {
            if (empty($event->message->getReplyTo())) {
                $replyTo = \App\Support\MailIdentity::replyTo();
                $event->message->replyTo(new \Symfony\Component\Mime\Address($replyTo['address'], $replyTo['name']));
            }
        });
    }
}
