<x-filament::page>


    <div class="space-y-6"></div>
    <h2 class="text-xl font-bold mb-4">Pending Tickets for Approval</h2>
    @if($tickets->isEmpty())
        <div class="text-gray-500">No tickets pending approval.</div>
    @else
        <table class="w-full bg-white dark:bg-gray-800 border border-gray-200 rounded dark:text-white-500">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">ID</th>
                    <th class="px-4 py-2 border-b">Owner</th>
                    <th class="px-4 py-2 border-b">Responsible</th>
                    <th class="px-4 py-2 border-b">Ticket Name</th>
                    <th class="px-4 py-2 border-b">Start Date</th>
                    <th class="px-4 py-2 border-b">End Date</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        @if (Auth::user()->id = $ticket->owner_id)
                            <td class="px-4 py-2 border-b">{{ $ticket->id }}</td>
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center">
                                    <img src="{{ $ticket->owner->photo ?? $ticket->owner->avatar_url }}"
                                        alt="{{ $ticket->owner->name }}" class="w-8 h-8 rounded-full mr-2">
                                    <span>{{ $ticket->owner->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center">
                                    <img src="{{ $ticket->responsible->photo ?? $ticket->responsible->avatar_url }}"
                                        alt="{{ $ticket->responsible->name }}" class="w-8 h-8 rounded-full mr-2">
                                    {{ $ticket->responsible->name }}
                                </div>
                            </td>
                            <td class="px-4 py-2 border-b">{{ $ticket->name }}</td>
                            <td class="px-4 py-2 border-b">{{ Carbon\Carbon::parse($ticket->updated_at)->diffForHumans() }}</td>
                            <td class="px-4 py-2 border-b text-center">
                                <span
                                    style="color: {{ Carbon\Carbon::parse($ticket->end_date)->diffInDays() <= 1 ? 'red' : 'blue' }}; font-weight: bold;">
                                    {{ Carbon\Carbon::parse($ticket->end_date)->diffForHumans() }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border-b space-x-2">
                                <x-filament::button color="success" wire:click="approve({{ $ticket->id }})"
                                    onclick="return confirm('Are you sure you want to approve this ticket?')">
                                    Approve
                                </x-filament::button>
                                <x-filament::button color="danger" wire:click="reject({{ $ticket->id }})">
                                    Reject
                                </x-filament::button>
                                <x-filament::button color="secondary" wire:click="deleteTicket({{ $ticket->id }})"
                                    onclick="return confirm('Are you sure you want to delete this ticket?')">
                                    Delete
                                </x-filament::button>
                            </td>
                        @endif

                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    </div>


</x-filament::page>