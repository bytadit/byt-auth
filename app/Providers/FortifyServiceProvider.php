<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // RateLimiter::for('login', function (Request $request) {
        //     $email = (string) $request->email;

        //     return Limit::perMinute(5)->by($email.$request->ip())
        //         ->response(function () {
        //             return redirect()->back()
        //             ->errors()->add('toomany', 'tooo');
        //             // ->with('error', 'Too many failed login attempts.');
        //     });
        // });

        // RateLimiter::for('login', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->email . $request->ip())
        //         ->response(function () {
        //             return redirect()->route('login')
        //                 ->with('error', 'Too many failed login attempts.');
        //         });
        // });

        // RateLimiter::for('login', function (Request $request) {
        //     return Limit::perMinute(3)->by($request->email . $request->ip())
        //         ->response(function () {
        //             return redirect()->route('login')
        //                 ->with('error', 'Too many failed login attempts.');
        //         });
        // });


        RateLimiter::for('login', function (Request $request) {
            $key = 'login.'.$request->ip();
            $max = 3;   // attempts
            $decay = 30;    //seconds

            if (RateLimiter::tooManyAttempts($key, $max)) {
                $seconds = RateLimiter::availableIn($key);
                return redirect()->route('login')
                    ->with('error', __('auth.throttle', ['seconds' => $seconds]));
            } else {
                RateLimiter::hit($key, $decay);
            }
        });


        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
