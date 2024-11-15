<div class="px-4 py-3 space-y-4">
    <div class="grid gap-4">
        {{-- Total Jam Kerja --}}
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="text-blue-600 dark:text-blue-400">
                    <x-heroicon-m-clock class="w-6 h-6"/>
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Jam Kerja
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ rtrim(rtrim($totalJamKerja, '0'), '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
