<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl font-bold mb-4">Welcome to M2 Profi Platform</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    This is the main dashboard for the platform administration.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Tenants</h3>
                <p class="text-3xl font-bold text-primary-600">{{ \App\Models\Tenant::count() }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Users</h3>
                <p class="text-3xl font-bold text-primary-600">{{ \App\Models\User::count() }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Settings</h3>
                <p class="text-3xl font-bold text-primary-600">{{ \App\Models\SettingDefinition::count() }}</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
