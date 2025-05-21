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

class Dashboard extends BasePage
{
    protected static bool $shouldRegisterNavigation = false;

    protected function getColumns(): int|array
    {
        return 6;
    }

    protected function getActions(): array
    {
        return [
            Action::make('create-project')
                ->label(__('Create project'))
                ->url(fn(): string => route('filament.resources.projects.create'))
                ->icon('heroicon-o-plus-circle'),
            Action::make('list-projects')
                ->label(__('Show all projects'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.projects.index'))
                ->icon('heroicon-o-view-list'),
            Action::make('create-ticket')
                ->label(__('Create ticket'))
                ->url(fn(): string => route('filament.resources.tickets.create'))
                ->icon('heroicon-o-plus-circle'),

            Action::make('list-tickets')
                ->label(__('Show all tickets'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.tickets.index'))
                ->icon('heroicon-o-view-list'),

            Action::make('create-user')
                ->label(__('Create user'))
                ->url(fn(): string => route('filament.resources.users.create'))
                ->icon('heroicon-o-plus-circle'),

            Action::make('list-users')
                ->label(__('Show all users'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.users.index'))
                ->icon('heroicon-o-view-list'),
        ];
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
}
