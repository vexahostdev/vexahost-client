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

        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = \Illuminate\Auth\Notifications\ResetPassword::$createUrlCallback
                ? call_user_func(\Illuminate\Auth\Notifications\ResetPassword::$createUrlCallback, $notifiable, $token)
                : url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false));

            $noreply = \App\Support\MailIdentity::noreply();
            $replyTo = \App\Support\MailIdentity::replyTo();

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->from($noreply['address'], $noreply['name'])
                ->replyTo($replyTo['address'], $replyTo['name'])
                ->subject(\Illuminate\Support\Facades\Lang::get('Reset Password Notification'))
                ->line(\Illuminate\Support\Facades\Lang::get('You are receiving this email because we received a password reset request for your account.'))
                ->action(\Illuminate\Support\Facades\Lang::get('Reset Password'), $url)
                ->line(\Illuminate\Support\Facades\Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
                ->line(\Illuminate\Support\Facades\Lang::get('If you did not request a password reset, no further action is required.'));
        });

        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function ($notifiable, string $verificationUrl) {
            $noreply = \App\Support\MailIdentity::noreply();
            $replyTo = \App\Support\MailIdentity::replyTo();

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->from($noreply['address'], $noreply['name'])
                ->replyTo($replyTo['address'], $replyTo['name'])
                ->subject(\Illuminate\Support\Facades\Lang::get('Verify Email Address'))
                ->line(\Illuminate\Support\Facades\Lang::get('Please click the button below to verify your email address.'))
                ->action(\Illuminate\Support\Facades\Lang::get('Verify Email Address'), $verificationUrl)
                ->line(\Illuminate\Support\Facades\Lang::get('If you did not create an account, no further action is required.'));
        });
    }
}
