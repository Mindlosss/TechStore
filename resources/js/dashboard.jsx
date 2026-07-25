import React from 'react';
import { createRoot } from 'react-dom/client';

const dashboardRoots = new WeakMap();

function BusinessDashboard({ stats }) {
    const currency = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    });

    const metrics = [
        {
            id: 'category',
            label: 'Categoría más popular',
            value: stats.topCategory.name ?? 'Sin datos',
            supporting: `${stats.topCategory.favorites} favoritos`,
            accent: 'border-blue-500/40 bg-blue-500/10',
            marker: 'bg-blue-500',
        },
        {
            id: 'product',
            label: 'Producto más agregado',
            value: stats.topProduct.name ?? 'Sin datos',
            supporting: `${stats.topProduct.favorites} favoritos`,
            accent: 'border-emerald-500/40 bg-emerald-500/10',
            marker: 'bg-emerald-500',
        },
        {
            id: 'price',
            label: 'Precio promedio',
            value: currency.format(stats.averagePrice),
            supporting: 'Todos los favoritos',
            accent: 'border-amber-500/40 bg-amber-500/10',
            marker: 'bg-amber-500',
        },
        {
            id: 'users',
            label: 'Usuarios activos',
            value: stats.activeUsers,
            supporting: 'Con al menos un favorito',
            accent: 'border-violet-500/40 bg-violet-500/10',
            marker: 'bg-violet-500',
        },
    ];

    return (
        <section
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="Indicadores de negocio"
        >
            {metrics.map((metric) => (
                <article
                    key={metric.id}
                    className={`flex min-h-40 flex-col justify-between rounded-xl border p-5 ${metric.accent}`}
                >
                    <span className="flex items-center gap-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        <span className={`size-2 rounded-full ${metric.marker}`} />
                        {metric.label}
                    </span>

                    <span className="flex flex-col gap-1">
                        <strong className="line-clamp-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                            {metric.value}
                        </strong>
                        <span className="text-sm text-zinc-500 dark:text-zinc-400">
                            {metric.supporting}
                        </span>
                    </span>
                </article>
            ))}
        </section>
    );
}

function mountDashboard() {
    const element = document.getElementById('business-dashboard');

    if (!element || dashboardRoots.has(element)) {
        return;
    }

    const root = createRoot(element);
    const stats = JSON.parse(element.dataset.stats);

    dashboardRoots.set(element, root);
    root.render(<BusinessDashboard stats={stats} />);
}

function unmountDashboard() {
    const element = document.getElementById('business-dashboard');
    const root = element ? dashboardRoots.get(element) : null;

    if (root) {
        root.unmount();
        dashboardRoots.delete(element);
    }
}

document.addEventListener('livewire:navigating', unmountDashboard);
document.addEventListener('livewire:navigated', mountDashboard);

mountDashboard();
