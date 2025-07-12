<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FavoriteProjects;
use App\Filament\Widgets\LatestActivities;
use App\Filament\Widgets\LatestComments;
use App\Filament\Widgets\LatestProjects;
use App\Filament\Widgets\LatestTickets;
use App\Filament\Widgets\TicketsByPriority;
use App\Filament\Widgets\TicketsByType;
use App\Filament\Widgets\TicketTimeLogged;
use App\Filament\Widgets\UserTimeLogged;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\ActionGroup;
use Filament\Pages\Dashboard as BasePage;
use JibayMcs\FilamentTour\Tour\HasTour;
use JibayMcs\FilamentTour\Tour\Step;
use JibayMcs\FilamentTour\Tour\Tour;
class Dashboard extends BasePage
{
    use HasTour;

    protected static bool $shouldRegisterNavigation = false;

    protected function getColumns(): int|array
    {
        return 6;
    }

    protected function getActions(): array
    {
        $user = auth()->user();

        // Example: Only show actions if user has the required role
        // You can adjust the logic to check for specific permissions as needed

        $actions = [];

        if ($user->hasRole('Project Manager')) {
            $actions[] = ActionGroup::make([
                Action::make('daily-report')
                    ->label(__('Daily Report'))
                    ->url('/daily-report')
                    ->icon('heroicon-o-plus-circle'),
            ]);
        }

        if ($user->hasRole('Project Manager')) {
            $actions[] = Action::make('list-projects')
                ->label(__('Show all projects'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.projects.index'))
                ->icon('heroicon-o-view-list');
        }

        if ($user->hasRole('Project Manager')) {
            $actions[] = Action::make('create-ticket')
                ->label(__('Create ticket'))
                ->url(fn(): string => route('filament.resources.tickets.create'))
                ->icon('heroicon-o-plus-circle');
        }

        if ($user->hasRole('Project Manager')) {
            $actions[] = Action::make('list-tickets')
                ->label(__('Show all tickets'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.tickets.index'))
                ->icon('heroicon-o-view-list');
        }

        if ($user->hasRole('Project Manager')) {
            $actions[] = Action::make('create-user')
                ->label(__('Create user'))
                ->url(fn(): string => route('filament.resources.users.create'))
                ->icon('heroicon-o-plus-circle');
        }

        if ($user->hasRole('Project Manager')) {
            $actions[] = Action::make('list-users')
                ->label(__('Show all users'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.users.index'))
                ->icon('heroicon-o-view-list');
        }

        return $actions;
    }

    protected function getWidgets(): array
    {
        return [
            FavoriteProjects::class,
            LatestActivities::class,
            LatestComments::class,
            LatestProjects::class,
            LatestTickets::class,
            TicketsByPriority::class,
            TicketsByType::class,
            TicketTimeLogged::class,
            UserTimeLogged::class
        ];
    }

    public function tours(): array
    {
        return [
            Tour::make('dashboard')
                ->steps(
                    Step::make()
                        ->title("Welcome to your Project Management System!"),
                    Step::make('.fi-avatar')
                        ->title('Woaw ! Here is your avatar !')
                        ->description('You look nice !')
                        ->icon('heroicon-o-user-circle')
                        ->iconColor('primary')
                ),
        ];
    }
}
