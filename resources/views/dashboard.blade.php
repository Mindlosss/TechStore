<x-layouts::app :title="__('Dashboard')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Resumen general de favoritos de TechStore.
            </p>
        </div>

        <div
            id="business-dashboard"
            data-stats="{{ json_encode($dashboardStats) }}"
        >
            <p class="text-sm text-zinc-500">Cargando indicadores...</p>
        </div>
    </div>
</x-layouts::app>
