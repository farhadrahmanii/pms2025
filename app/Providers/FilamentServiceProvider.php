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
            Filament::registerNavigationItems([
                NavigationItem::make()
                    ->label('Chat Room')
                    ->icon('heroicon-o-chat')
                    ->url('/chatify') // adjust URL as needed
                    ->sort(100), // lower value = higher in menu
            ]);
        });
    }
}
