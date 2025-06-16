<x-filament::page>
    <div class="mb-4 flex items-center gap-4">
        <label for="date" class="font-semibold">Date:</label>
        <input type="date" id="date" wire:model="date" class="border rounded px-2 py-1 dark:bg-gray-800" />
        <x-filament::button color="primary" wire:click="exportExcel">
            Export to Excel
        </x-filament::button>
    </div>

    <div class="mb-6">
        <table class="w-full bg-white border dark:bg-gray-800 border-gray-200 rounded">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">User</th>
                    <th class="px-4 py-2 border-b">Todo Tasks</th>
                    <th class="px-4 py-2 border-b">Progress Tasks</th>
                    <th class="px-4 py-2 border-b">Completed Tasks</th>
                    <th class="px-4 py-2 border-b">Pending Tasks</th>
                    <th class="px-4 py-2 border-b">Rejected Tasks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2 border-b text-center">{{ auth()->user()->name }}</td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 1)->where('status_id', '1')->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 1)->whereDate('updated_at', $date)->where('status_id', '2')->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 1)->where('status_id', '3')->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 0)->whereDate('updated_at', $date)->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', -1)->whereDate('updated_at', $date)->count() }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="w-full bg-white border dark:bg-gray-800 border-gray-200 rounded">
        <thead>
            <tr>
                <th class="px-4 py-2 border-b">Project</th>
                <th class="px-4 py-2 border-b">Name</th>
                <th class="px-4 py-2 border-b">Owner</th>
                <th class="px-4 py-2 border-b">Responsible</th>
                <th class="px-4 py-2 border-b">Estimated Time</th>
                <th class="px-4 py-2 border-b">Expire progress</th>
                <th class="px-4 py-2 border-b">End Date</th>
                <th class="px-4 py-2 border-b">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $ticket)
                <tr class="hover:cursor-pointer" @if($ticket['approved'])
                    onclick="window.location.href='{{ route('filament.resources.tickets.view', ['record' => $ticket['id']]) }}'"
                style="cursor:pointer;" @else
                    style="cursor:not-allowed; opacity:0.6; pointer-events:none; background-color:#f3f4f6;" @endif
                    onclick="window.location.href='{{ route('filament.resources.tickets.view', $ticket['id']) }}'">
                    <td class="px-4 py-2 border-b text-center">{{ $ticket['project']['name'] }}</td>
                    <td class="px-4 py-2 border-b text-center">{{ $ticket['name'] }}</td>
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
                    <td class="px-4 py-2 border-b text-center">{{ $ticket['estimation'] }} hours</td>
                    <td class="px-4 py-2 border-b flex items-center justify-center">
                        @php
                            if (!$ticket->end_date) {
                                $progress = 0;
                                $color = 'gray';
                            } else {
                                $endDate = \Carbon\Carbon::parse($ticket->end_date)->startOfDay();
                                $now = now()->startOfDay();
                                if ($endDate->equalTo($now)) {
                                    $progress = 1;
                                    $color = 'orange';
                                } elseif ($endDate->lessThan($now)) {
                                    $progress = 1;
                                    $color = 'red';
                                } else {
                                    $createdAt = $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->startOfDay() : $now;
                                    $totalPeriod = $createdAt->diffInDays($endDate, false);
                                    $elapsed = $createdAt->diffInDays($now, false);
                                    $progress = $totalPeriod > 0 ? min(1, max(0, $elapsed / $totalPeriod)) : 0;
                                    $color = 'blue';
                                }
                            }
                        @endphp
                        <svg width="42" height="42" viewBox="0 0 36 36">
                            <path fill="none" stroke="#e5e7eb" stroke-width="2"
                                d="M18 2.0845
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 31.831
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path fill="none" stroke="{{ $color }}" stroke-width="2"
                                stroke-dasharray="{{ round($progress * 100) }}, 100"
                                d="M18 2.0845
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 31.831
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <text x="18" y="20.35" fill="{{ $color }}" font-size="8" text-anchor="middle">
                                {{ $progress == 1 && $color == 'orange' ? 'Today' : ($progress == 1 && $color == 'red' ? 'Expired' : (round($progress * 100) . '%')) }}
                            </text>
                        </svg>
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        @if(\Carbon\Carbon::parse($ticket->end_date)->isPast())
                            <span style="color: red; font-weight: bold;"></span>
                            Expired {{ \Carbon\Carbon::parse($ticket->end_date)->diffForHumans(null, null, false, 1) }}
                            </span>
                        @else
                            <span style="color: blue; font-weight: bold;">
                                Ticket will expire on {{ \Carbon\Carbon::parse($ticket->end_date)->toFormattedDateString() }}
                            </span>
                        @endif</span>
                    </td>

                    <td class="px-4 py-2 border-b text-center">
                        @if($ticket['approved'])
                            <style>
                                /* From Uiverse.io by neerajbaniwal */
                                .btn-shine {
                                    transform: translate(-50%, -50%);
                                    padding: 12px 48px;
                                    color: #fff;
                                    background: linear-gradient(to right, #9f9f9f 0, #fff 10%, rgb(105, 187, 38) 20%);
                                    background-position: 0;
                                    -webkit-background-clip: text;
                                    -webkit-text-fill-color: transparent;
                                    animation: shine 3s infinite linear;
                                    animation-fill-mode: forwards;
                                    -webkit-text-size-adjust: none;
                                    font-weight: 600;
                                    font-size: 16px;
                                    text-decoration: none;
                                    white-space: nowrap;
                                    font-family: "Poppins", sans-serif;
                                }

                                .btn-pending {
                                    transform: translate(-50%, -50%);
                                    padding: 12px 48px;
                                    color: #fff;
                                    background: linear-gradient(to right, #9f9f9f 0, #fff 10%, rgb(245, 10, 30) 20%);
                                    background-position: 0;
                                    -webkit-background-clip: text;
                                    -webkit-text-fill-color: transparent;
                                    animation: shine 3s infinite linear;
                                    animation-fill-mode: forwards;
                                    -webkit-text-size-adjust: none;
                                    font-weight: 600;
                                    font-size: 16px;
                                    text-decoration: none;
                                    white-space: nowrap;
                                    font-family: "Poppins", sans-serif;
                                }

                                @-moz-keyframes shine {
                                    0% {
                                        background-position: 0;
                                    }

                                    60% {
                                        background-position: 180px;
                                    }

                                    100% {
                                        background-position: 180px;
                                    }
                                }

                                @-webkit-keyframes shine {
                                    0% {
                                        background-position: 0;
                                    }

                                    60% {
                                        background-position: 180px;
                                    }

                                    100% {
                                        background-position: 180px;
                                    }
                                }

                                @-o-keyframes shine {
                                    0% {
                                        background-position: 0;
                                    }

                                    60% {
                                        background-position: 180px;
                                    }

                                    100% {
                                        background-position: 180px;
                                    }
                                }

                                @keyframes shine {
                                    0% {
                                        background-position: 0;
                                    }

                                    60% {
                                        background-position: 180px;
                                    }

                                    100% {
                                        background-position: 180px;
                                    }
                                }
                            </style>
                            <!-- From Uiverse.io by neerajbaniwal -->
                            <a href="#" class="btn-shine">Approved</a>
                        @else
                            <span class="flex items-center justify-center">
                                <style>
                                    .loader {
                                        position: relative;
                                        width: 2em;
                                        height: 2em;
                                        display: inline-block;
                                    }

                                    .track,
                                    .inner-track {
                                        position: absolute;
                                        width: 100%;
                                        height: 100%;
                                        border-radius: 50%;
                                        box-shadow: inset -0.1em -0.1em 0.2em #d1d1d1, inset 0.1em 0.1em 0.2em #ffffff;
                                    }

                                    .inner-track {
                                        width: 80%;
                                        height: 80%;
                                        top: 10%;
                                        left: 10%;
                                        border: 0.3em solid #f0f0f0;
                                    }

                                    .orb {
                                        position: absolute;
                                        width: 0.7em;
                                        height: 0.7em;
                                        top: 50%;
                                        left: 50%;
                                        background-color: #c0cfda;
                                        border-radius: 50%;
                                        animation: spin 1.5s infinite cubic-bezier(0.68, -0.55, 0.27, 1.55);
                                        background: radial-gradient(circle at 30% 30%, #ffffff, #ccc);
                                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 -1px 2px rgba(255, 255, 255, 0.2), inset 0 1px 2px rgba(0, 0, 0, 0.2);
                                    }

                                    @keyframes spin {
                                        0% {
                                            transform: translate(-50%, -50%) rotate(90deg) translate(1em) rotate(-90deg);
                                        }

                                        100% {
                                            transform: translate(-50%, -50%) rotate(450deg) translate(1em) rotate(-450deg);
                                        }
                                    }
                                </style>
                                <div class="loader">
                                    <div class="track"></div>
                                    <div class="inner-track"></div>
                                    <div class="orb"></div>
                                    <span class="btn-pending text-gray-500 ">Pending</span>
                                </div>
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Rejected Tickets List --}}
    @if(isset($rejectedTickets) && $rejectedTickets->isNotEmpty())
        <h2 class="text-xl font-bold mt-10 mb-4 text-red-600 dark:text-red-400 animated:puls" style="color: red;">Rejected
            Tickets
        </h2>
        <hr style="height: 20px; border: 10px solid red; border-radius: 5px;" />
        <table class="w-full bg-white dark:bg-gray-800 border border-gray-200 rounded dark:text-white-500 mt-2"
            style="margin-bottom: 50px;">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">ID</th>
                    <th class="px-4 py-2 border-b">Owner</th>
                    <th class="px-4 py-2 border-b">Responsible</th>
                    <th class="px-4 py-2 border-b">Ticket Name</th>
                    <th class="px-4 py-2 border-b">Date Progress</th>
                    <th class="px-4 py-2 border-b">End Date</th>
                    <th class="px-4 py-2 border-b">Rejected At</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rejectedByProject = $rejectedTickets->groupBy('project_id');
                @endphp
                @foreach($rejectedByProject as $projectId => $projectTickets)
                    <tr>
                        <td colspan="8" class="bg-gray-100 dark:bg-gray-900 font-bold text-lg px-4 py-2 border-b">
                            Project: {{ optional($projectTickets->first()->project)->name ?? 'Unknown Project' }}
                        </td>
                    </tr>
                    @foreach($projectTickets as $ticket)
                        <tr onclick="if(!event.target.closest('.no-row-redirect')){window.location='{{ route('filament.resources.tickets.view', ['record' => $ticket->id]) }}'}"
                            style="cursor:pointer;">
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
                            <td class="px-4 py-2 border-b flex items-center justify-center">
                                @php
                                    if (!$ticket->end_date) {
                                        $progress = 0;
                                        $color = 'gray';
                                    } else {
                                        $endDate = \Carbon\Carbon::parse($ticket->end_date)->startOfDay();
                                        $now = now()->startOfDay();
                                        if ($endDate->equalTo($now)) {
                                            $progress = 1;
                                            $color = 'orange';
                                        } elseif ($endDate->lessThan($now)) {
                                            $progress = 1;
                                            $color = 'red';
                                        } else {
                                            $createdAt = $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->startOfDay() : $now;
                                            $totalPeriod = $createdAt->diffInDays($endDate, false);
                                            $elapsed = $createdAt->diffInDays($now, false);
                                            $progress = $totalPeriod > 0 ? min(1, max(0, $elapsed / $totalPeriod)) : 0;
                                            $color = 'blue';
                                        }
                                    }
                                @endphp
                                <svg width="42" height="42" viewBox="0 0 36 36">
                                    <path fill="none" stroke="#e5e7eb" stroke-width="2"
                                        d="M18 2.0845
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 31.831
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path fill="none" stroke="{{ $color }}" stroke-width="2"
                                        stroke-dasharray="{{ round($progress * 100) }}, 100"
                                        d="M18 2.0845
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 31.831
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <text x="18" y="20.35" fill="{{ $color }}" font-size="8" text-anchor="middle">
                                        {{ $progress == 1 && $color == 'orange' ? 'Today' : ($progress == 1 && $color == 'red' ? 'Expired' : (round($progress * 100) . '%')) }}
                                    </text>
                                </svg>
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                @if(\Carbon\Carbon::parse($ticket->end_date)->isPast())
                                    <span style="color: red; font-weight: bold;"></span>
                                    Expired {{ \Carbon\Carbon::parse($ticket->end_date)->diffForHumans(null, null, false, 1) }}
                                    </span>
                                @else
                                    <span style="color: blue; font-weight: bold;">
                                        expire on {{ \Carbon\Carbon::parse($ticket->end_date)->toFormattedDateString() }}
                                    </span>
                                @endif</span>
                            </td>
                            <td class="px-4 py-2 border-b">
                                {{ $ticket->updated_at ? Carbon\Carbon::parse($ticket->updated_at)->diffForHumans() : '-' }}
                            </td>
                            <td class="px-4 py-2 border-b no-row-redirect">
                                <div class="relative inline-block text-left">
                                    <button type="button"
                                        class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 focus:outline-none"
                                        id="actionsDropdownButton-{{ $ticket->id }}" aria-expanded="true" aria-haspopup="true"
                                        onclick="document.getElementById('actionsDropdownMenu-{{ $ticket->id }}').classList.toggle('hidden')">
                                        Actions
                                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.584l3.71-3.354a.75.75 0 111.02 1.1l-4.25 3.84a.75.75 0 01-1.02 0l-4.25-3.84a.75.75 0 01.02-1.06z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="actionsDropdownMenu-{{ $ticket->id }}"
                                        class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 hidden z-50">
                                        <div class="py-1">

                                            <button
                                                class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                                                wire:click="deleteTicket({{ $ticket->id }})"
                                                onclick="event.stopPropagation(); if(confirm('Are you sure you want to delete this ticket?')) { Livewire.emit('deleteTicket', {{ $ticket->id }}); } return false;">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @elseif(isset($rejectedTickets))
        <div class="text-gray-500 mt-4">No rejected tickets.</div>
    @endif
</x-filament::page>