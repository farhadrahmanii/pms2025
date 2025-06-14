<div
    class="flex flex-col items-start space-y-4 p-4 bg-white dark:bg-gray-900 rounded-lg shadow-md border border-gray-100 dark:border-gray-800 w-full ">
    <div class="flex items-center space-x-3 w-full">
        <span class="font-semibold text-gray-700 dark:text-gray-200 text-base">Average Rating</span>
        <div class="flex items-center">
            @for ($i = 1; $i <= 5; $i++)
                <svg class="w-6 h-6 {{ $i <= round($avg) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-700' }} transition-colors"
                    fill="currentColor" viewBox="0 0 20 20">
                    <polygon points="9.9,1.1 12.3,6.6 18.2,7.3 13.7,11.2 15,17 9.9,14.1 4.8,17 6.1,11.2 1.6,7.3 7.5,6.6" />
                </svg>
            @endfor
        </div>
        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $avg ?? '0.00' }} / 5)</span>
    </div>
    @if($userStars)
        <div class="flex items-center space-x-3 w-full">
            <span class="font-semibold text-gray-700 dark:text-gray-200 text-base">Your Rating</span>
            <div class="flex items-center">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 {{ $i <= round($userStars) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-700' }} transition-colors"
                        fill="currentColor" viewBox="0 0 20 20">
                        <polygon points="9.9,1.1 12.3,6.6 18.2,7.3 13.7,11.2 15,17 9.9,14.1 4.8,17 6.1,11.2 1.6,7.3 7.5,6.6" />
                    </svg>
                @endfor
            </div>
            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $userStars }} / 5)</span>
        </div>
    @else
        <div class="flex items-center space-x-3 w-full">
            <span class="font-semibold text-gray-700 dark:text-gray-200 text-base">Your Rating</span>
            <div class="flex items-center">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-700 transition-colors" fill="currentColor"
                        viewBox="0 0 20 20">
                        <polygon points="9.9,1.1 12.3,6.6 18.2,7.3 13.7,11.2 15,17 9.9,14.1 4.8,17 6.1,11.2 1.6,7.3 7.5,6.6" />
                    </svg>
                @endfor
            </div>
            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(0 / 5)</span>
        </div>
    @endif
</div>