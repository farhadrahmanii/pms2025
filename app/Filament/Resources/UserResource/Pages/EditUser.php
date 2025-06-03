<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('addToDepartment')
                ->label('Add to Department')
                ->icon('heroicon-o-user-group')
                ->form([
                    \Filament\Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->options(\App\Models\Department::all()->pluck('name', 'id'))
                        ->required()
                        ->default(function ($livewire) {
                            $user = $livewire->record;
                            $currentDepartment = $user->departments()->first();
                            return $currentDepartment ? $currentDepartment->id : null;
                        }),
                ])
                ->action(function (array $data) {
                    $user = $this->record;
                    $departmentId = $data['department_id'];
                    // Detach user from all departments first
                    $user->departments()->sync([$departmentId]);
                    \Filament\Notifications\Notification::make()
                        ->title('User assigned to department!')
                        ->success()
                        ->send();
                }),
        ];
    }

}
