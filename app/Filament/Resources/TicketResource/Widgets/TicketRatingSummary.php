<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use Filament\Widgets\Widget;

class TicketRatingSummary extends Widget
{
    protected static string $view = 'components.ticket-rating-summary';

    public $record;

    protected function getViewData(): array
    {
        if (!$this->record) {
            return [
                'avg' => null,
                'userStars' => null,
            ];
        }
        // Use the correct method for the willvincent/laravel-rateable package
        $avg = $this->record->averageRating(); // This is a method, not a property
        $userStars = $this->record->userAverageRating(); // This is a method, not a property
        return [
            'avg' => $avg !== null ? number_format($avg, 2) : null,
            'userStars' => $userStars,
        ];
    }
}
