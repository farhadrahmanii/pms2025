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
                    <th class="px-4 py-2 border-b">Title</th>
                    <th class="px-4 py-2 border-b">content</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        @if (Auth::user()->id = $ticket->owner_id)
                            <td class="px-4 py-2 border-b">{{ $ticket->id }}</td>
                            <td class="px-4 py-2 border-b">{{ $ticket->name }}</td>
                            <td class="px-4 py-2 border-b">{{ $ticket->content }}</td>
                            <td class="px-4 py-2 border-b space-x-2">
                                <x-filament::button color="success" wire:click="approve({{ $ticket->id }})"
                                    onclick="return confirm('Are you sure you want to approve this ticket?')">
                                    Approve
                                </x-filament::button>
                                <x-filament::button color="danger" wire:click="reject({{ $ticket->id }})"
                                    onclick="return confirm('Are you sure you want to reject this ticket?')">
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