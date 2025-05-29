<?php

namespace App\Filament\Widgets;

use Filament\Pages\Actions\Action;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\HtmlString;
use Filament\Pages\Actions\ActionGroup;
class FavoriteProjects extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 6
    ];

    protected function getColumns(): int
    {
        return 6;
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List projects');
    }
    protected function getActions(): array
    {
        return [
            Action::make('daily-report')
                ->label(__('Daily Report'))
                ->url('/daily-report')
                ->icon('heroicon-o-plus-circle'),
            Action::make('list-projects')
                ->label(__('Show all projects'))
                ->color('secondary')
                ->url(fn(): string => route('filament.resources.projects.index'))
                ->icon('heroicon-o-view-list'),
        ];
    }

    protected function getCards(): array
    {
        $favoriteProjects = auth()->user()->favoriteProjects;
        $cards = [];
        foreach ($favoriteProjects as $project) {
            $ticketsCount = $project->tickets()->count();
            $contributors = $project->contributors;
            $contributorsList = '';
            foreach ($contributors as $contributor) {
                $avatarUrl = $contributor->photo ? asset('storage/' . $contributor->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($contributor->name);
                $contributorsList .= '<li class="flex items-center gap-2">
                    <img src="' . $avatarUrl . '" alt="' . e($contributor->name) . '" class="w-6 h-6 rounded-full object-cover border" />
                    <span>' . e($contributor->name) . '</span>
                </li>';
            }
            $cards[] = Card::make('', new HtmlString('
                    <div class="flex items-center gap-2 -mt-2 text-lg">
                        <div style=\'background-image: url("' . $project->cover . '")\'
                             class="w-12 h-12 bg-cover bg-center bg-no-repeat"></div>
                        <span>' . $project->name . '</span>
                    </div>
                '))
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:shadow-lg'
                ])
                ->description(new HtmlString('
                        <div class="w-full flex items-center gap-2 mt-2 text-gray-500 font-normal">'
                    . $ticketsCount
                    . ' '
                    . __($ticketsCount > 1 ? 'Tickets' : 'Ticket')
                    . ' '
                    . __('and')
                    . ' '
                    . $contributors->count()
                    . ' '
                    . __($contributors->count() > 1 ? 'Contributors' : 'Contributor')
                    . '</div>
                        <div class="text-xs w-full flex items-center gap-2 mt-2">
                            <a class="text-primary-400 hover:text-primary-500 hover:cursor-pointer"
                               href="' . route('filament.resources.projects.view', $project) . '">
                                ' . __('View details') . '
                            </a>
                            <span class="text-gray-300">|</span>
                            <a class="text-primary-400 hover:text-primary-500 hover:cursor-pointer"
                               href="' . route('filament.pages.kanban/{project}', ['project' => $project->id]) . '">
                                ' . __('Tickets') . '
                            </a>
                            <span class="text-gray-300">|</span>
                            <a class="text-primary-400 hover:text-primary-500 hover:cursor-pointer"
                               href="' . route('filament.resources.tickets.create', ['project' => $project->id]) . '">
                                ' . __('Create new ticket') . '
                            </a>
                        </div>
                        <div class="w-full flex flex-col gap-2 mt-2">
                            <span class="text-xs font-semibold text-gray-500">' . __('Contributors') . '</span>
                            <ul class="list-none grid grid-cols-3 gap-2 text-xs">' . $contributorsList . '</ul>
                        </div>
                    '));
        }
        return $cards;
    }
}
