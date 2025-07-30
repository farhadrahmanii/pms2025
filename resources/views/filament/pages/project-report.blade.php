<x-filament::page>
    {{-- Header Section --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-black dark:text-white mb-2">
                    گزارش پروژه
                </h1>
                <p class="text-gray-700 dark:text-gray-400">
                    مشاهده و مدیریت گزارش‌های پروژه با قابلیت صادر کردن به Excel
                </p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <x-filament::button color="success" wire:click="refreshReport" class="flex items-center">
                    <span class="mr-2">🔄</span>
                    بروزرسانی
                </x-filament::button>
                <x-filament::button color="primary" wire:click="exportExcel" class="flex items-center">
                    <span class="mr-2">📥</span>
                    صادر کردن Excel
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-black dark:text-white mb-4">
            فیلترهای گزارش
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-2">
                    تاریخ شروع
                </label>
                <input type="date" wire:model="startDate"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white text-black" />
            </div>

            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-2">
                    تاریخ پایان
                </label>
                <input type="date" wire:model="endDate"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white text-black" />
            </div>

            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-2">
                    وظیفه
                </label>
                <select wire:model="selectedUser"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white text-black">
                    <option value="">همه وظیفه‌ها</option>
                    @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-2">
                    پروژه
                </label>
                <select wire:model="selectedProject"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white text-black">
                    <option value="">همه پروژه‌ها</option>
                    @foreach(\App\Models\Project::all() as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
        {{-- Total Tickets --}}
        <div
            class="bg-blue-50 dark:bg-gradient-to-br dark:from-blue-500 dark:to-blue-600 rounded-lg p-6 shadow-lg border border-blue-200 dark:border-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 dark:text-blue-100 text-sm font-medium">کل تیکت‌ها</p>
                    <p class="text-blue-900 dark:text-white text-2xl font-bold">{{ $summary['total_tickets'] ?? 0 }}</p>
                </div>
                <span class="text-3xl">🎫</span>
            </div>
        </div>

        {{-- Todo Tickets --}}
        <div
            class="bg-yellow-50 dark:bg-gradient-to-br dark:from-yellow-500 dark:to-yellow-600 rounded-lg p-6 shadow-lg border border-yellow-200 dark:border-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-700 dark:text-yellow-100 text-sm font-medium">در انتظار</p>
                    <p class="text-yellow-900 dark:text-white text-2xl font-bold">{{ $summary['todo_tickets'] ?? 0 }}
                    </p>
                </div>
                <span class="text-3xl">⏰</span>
            </div>
        </div>

        {{-- In Progress Tickets --}}
        <div
            class="bg-orange-50 dark:bg-gradient-to-br dark:from-orange-500 dark:to-orange-600 rounded-lg p-6 shadow-lg border border-orange-200 dark:border-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-700 dark:text-orange-100 text-sm font-medium">در حال انجام</p>
                    <p class="text-orange-900 dark:text-white text-2xl font-bold">
                        {{ $summary['in_progress_tickets'] ?? 0 }}</p>
                </div>
                <span class="text-3xl">▶️</span>
            </div>
        </div>

        {{-- Completed Tickets --}}
        <div
            class="bg-green-50 dark:bg-gradient-to-br dark:from-green-500 dark:to-green-600 rounded-lg p-6 shadow-lg border border-green-200 dark:border-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-700 dark:text-green-100 text-sm font-medium">تکمیل شده</p>
                    <p class="text-green-900 dark:text-white text-2xl font-bold">
                        {{ $summary['completed_tickets'] ?? 0 }}</p>
                </div>
                <span class="text-3xl">✅</span>
            </div>
        </div>

        {{-- Pending Tickets --}}
        <div
            class="bg-purple-50 dark:bg-gradient-to-br dark:from-purple-500 dark:to-purple-600 rounded-lg p-6 shadow-lg border border-purple-200 dark:border-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-700 dark:text-purple-100 text-sm font-medium">در انتظار تایید</p>
                    <p class="text-purple-900 dark:text-white text-2xl font-bold">{{ $summary['pending_tickets'] ?? 0 }}
                    </p>
                </div>
                <span class="text-3xl">⚠️</span>
            </div>
        </div>

        {{-- Rejected Tickets --}}
        <div
            class="bg-red-50 dark:bg-gradient-to-br dark:from-red-500 dark:to-red-600 rounded-lg p-6 shadow-lg border border-red-200 dark:border-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-700 dark:text-red-100 text-sm font-medium">رد شده</p>
                    <p class="text-red-900 dark:text-white text-2xl font-bold">{{ $summary['rejected_tickets'] ?? 0 }}
                    </p>
                </div>
                <span class="text-3xl">❌</span>
            </div>
        </div>
    </div>

    {{-- Main Report Table --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-black dark:text-white">
                لیست تیکت‌ها ({{ count($report) }} مورد)
            </h2>
        </div>

        @if(count($report) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            نام تیکت
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            پروژه
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            زمان تخمینی
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            وضعیت
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($report as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-black dark:text-white">
                                {{ $ticket->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black dark:text-white">
                            {{ optional($ticket->project)->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black dark:text-white">
                            {{ $ticket->estimation ?? 0 }} ساعت
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->approved == 1)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <span class="mr-1">✅</span>
                                تایید شده
                            </span>
                            @elseif($ticket->approved == 0)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <span class="mr-1">⏰</span>
                                در انتظار
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <span class="mr-1">❌</span>
                                رد شده
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <span class="text-6xl mb-4 block">📭</span>
            <h3 class="mt-2 text-sm font-medium text-black dark:text-white">هیچ تیکتی یافت نشد</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                در بازه زمانی انتخاب شده هیچ تیکتی وجود ندارد.
            </p>
        </div>
        @endif
    </div>

    {{-- Rejected Tickets Section --}}
    @if(isset($rejectedTickets) && $rejectedTickets->isNotEmpty())
    <div
        class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/20">
            <h2 class="text-lg font-semibold text-red-900 dark:text-red-100">
                تیکت‌های رد شده ({{ $rejectedTickets->count() }} مورد)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-red-50 dark:bg-red-900/20">
                    <tr>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">
                            شناسه
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">
                            نام تیکت
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($rejectedTickets as $ticket)
                    <tr class="hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black dark:text-white">
                            #{{ $ticket->id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-black dark:text-white">
                                {{ $ticket->name }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-filament::page>