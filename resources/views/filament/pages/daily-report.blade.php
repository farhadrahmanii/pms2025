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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2 border-b text-center">{{ auth()->user()->name }}</td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 1)->whereDate('updated_at', $date)->where('status_id', '1')->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 1)->whereDate('updated_at', $date)->where('status_id', '2')->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 1)->whereDate('updated_at', $date)->where('status_id', '3')->count() }}
                    </td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ \App\Models\Ticket::where(function ($q) {
    $q->where('responsible_id', auth()->id()); })->where('approved', 0)->whereDate('updated_at', $date)->count() }}
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
                <th class="px-4 py-2 border-b">Start Date</th>
                <th class="px-4 py-2 border-b">End Date</th>
                <th class="px-4 py-2 border-b">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $row)
                <tr class="hover:cursor-pointer"
                    onclick="window.location.href='{{ route('filament.resources.tickets.view', $row['id']) }}'">
                    <td class="px-4 py-2 border-b text-center">{{ $row['project']['name'] }}</td>
                    <td class="px-4 py-2 border-b text-center">{{ $row['name'] }}</td>
                   <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center">
                                    <img src="{{ $row->owner->photo ?? $row->owner->avatar_url }}"
                                        alt="{{ $row->owner->name }}" class="w-8 h-8 rounded-full mr-2">
                                    <span>{{ $row->owner->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center">
                                    <img src="{{ $row->responsible->photo ?? $row->responsible->avatar_url }}"
                                        alt="{{ $row->responsible->name }}" class="w-8 h-8 rounded-full mr-2">
                                    {{ $row->responsible->name }}
                                </div>
                            </td>
                    <td class="px-4 py-2 border-b text-center">{{ $row['estimation'] }} hours</td>
                    <td class="px-4 py-2 border-b text-center">
                        {{ Carbon\Carbon::parse($row['updated_at'])->diffForHumans() }}
                    </td>
                     <td class="px-4 py-2 border-b text-center">
                                <span
                                    style="color: {{ Carbon\Carbon::parse($row->end_date)->diffInDays() <= 1 ? 'red' : 'blue' }}; font-weight: bold;">
                                    {{ Carbon\Carbon::parse($row->end_date)->diffForHumans() }}
                                </span>
                            </td>
                    <td class="px-4 py-2 border-b text-center">
                        @if($row['approved'])
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
</x-filament::page>