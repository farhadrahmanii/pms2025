<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Filament::serving(function () {
            $user = auth()->user();

            Filament::registerNavigationItems([
                // Chat Room link (custom)
                NavigationItem::make()
                    ->label('Chat Room')
                    ->icon('heroicon-o-chat')
                    ->url('/chatify')
                    ->sort(100),
            ]);
        });
    }
}
