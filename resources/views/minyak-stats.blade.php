<div class="px-4 py-3 space-y-4">
    <div class="grid gap-4 md:grid-cols-3">
        {{-- Total Pemakaian --}}
        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="text-red-600 dark:text-red-400">
                    <x-heroicon-m-arrow-trending-down class="w-6 h-6" />
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Pemakaian Minyak
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($totalPemakaian) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Masuk --}}
        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="text-green-600 dark:text-green-400">
                    <x-heroicon-m-arrow-trending-up class="w-6 h-6" />
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Minyak Masuk
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($totalMasuk) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Stock --}}
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="text-blue-600 dark:text-blue-400">
                    <x-heroicon-m-circle-stack class="w-6 h-6" />
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Stock Minyak
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($totalStock) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
