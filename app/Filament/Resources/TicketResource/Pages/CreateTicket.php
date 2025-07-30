<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
    protected static ?string $title = 'ایجاد وظیفه';
    protected static ?string $navigationLabel = 'ایجاد وظیفه';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    
}