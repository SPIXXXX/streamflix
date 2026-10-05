const dashboardDataElement = document.getElementById('admin-dashboard-chart-data');

if (dashboardDataElement) {
    const chartData = JSON.parse(dashboardDataElement.textContent);
    const { default: ApexCharts } = await import('apexcharts');

    const textColor = '#a1a1aa';
    const gridColor = 'rgba(148, 163, 184, 0.12)';
    const chartFont = 'Figtree, ui-sans-serif, system-ui, sans-serif';

    const renderEmptyState = (elementId, message) => {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `<div class="flex min-h-64 items-center justify-center text-sm text-sf-muted">${message}</div>`;
        }
    };

    const genreCounts = new Map();
    chartData.genres.labels.forEach((genreGroup, index) => {
        genreGroup.split(',').forEach((genre) => {
            const normalizedGenre = genre.trim();
            if (normalizedGenre) {
                genreCounts.set(
                    normalizedGenre,
                    (genreCounts.get(normalizedGenre) ?? 0) + Number(chartData.genres.values[index]),
                );
            }
        });
    });
    const popularGenres = [...genreCounts.entries()]
        .sort((first, second) => second[1] - first[1])
        .slice(0, 8);

    const commonOptions = {
        chart: {
            background: 'transparent',
            fontFamily: chartFont,
            foreColor: textColor,
            toolbar: { show: false },
            animations: { enabled: true, speed: 450 },
        },
        dataLabels: { enabled: false },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4,
            padding: { left: 8, right: 12 },
        },
        tooltip: {
            theme: 'dark',
            style: { fontFamily: chartFont },
        },
        noData: {
            text: 'No data available yet',
            style: { color: textColor, fontFamily: chartFont },
        },
    };

    if (popularGenres.length > 0) {
        new ApexCharts(document.getElementById('genreChart'), {
            ...commonOptions,
            chart: { ...commonOptions.chart, type: 'donut', height: 280 },
            series: popularGenres.map(([, total]) => total),
            labels: popularGenres.map(([genre]) => genre),
            colors: ['#e11d48', '#8b5cf6', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#f97316', '#ec4899'],
            stroke: { show: true, colors: ['#17111f'], width: 3 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name: { show: true, color: textColor, offsetY: 20 },
                            value: { show: true, color: '#f4f4f5', fontSize: '24px', fontWeight: 700, offsetY: -16 },
                            total: {
                                show: true,
                                label: 'Films',
                                color: textColor,
                                formatter: (chart) => chart.globals.seriesTotals.reduce((total, count) => total + count, 0),
                            },
                        },
                    },
                },
            },
            legend: {
                position: 'bottom',
                fontSize: '12px',
                labels: { colors: textColor },
                markers: { width: 8, height: 8, radius: 8 },
                itemMargin: { horizontal: 10, vertical: 5 },
            },
        }).render();
    } else {
        renderEmptyState('genreChart', 'Add films to see genre distribution.');
    }

    if (chartData.decades.labels.length > 0) {
        new ApexCharts(document.getElementById('decadeChart'), {
            ...commonOptions,
            chart: { ...commonOptions.chart, type: 'bar', height: 280 },
            series: [{ name: 'Films', data: chartData.decades.values }],
            colors: ['#8b5cf6'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 5,
                    barHeight: '58%',
                    distributed: false,
                },
            },
            xaxis: {
                categories: chartData.decades.labels,
                labels: { style: { colors: textColor } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: { labels: { style: { colors: textColor } } },
            legend: { show: false },
        }).render();
    } else {
        renderEmptyState('decadeChart', 'Add films with release years to see decade trends.');
    }

    if (chartData.activity.labels.length > 0) {
        const activityLabels = chartData.activity.labels.map((month) => {
            const [year, monthNumber] = month.split('-');
            return new Date(Number(year), Number(monthNumber) - 1).toLocaleDateString(undefined, { month: 'short', year: '2-digit' });
        });

        new ApexCharts(document.getElementById('activityChart'), {
            ...commonOptions,
            chart: { ...commonOptions.chart, type: 'area', height: 280 },
            series: [{ name: 'Reviews', data: chartData.activity.values }],
            colors: ['#3b82f6'],
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 0.7, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] },
            },
            markers: { size: 4, strokeWidth: 0, hover: { size: 6 } },
            xaxis: {
                categories: activityLabels,
                labels: { style: { colors: textColor } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: { style: { colors: textColor } },
            },
            legend: { show: false },
        }).render();
    } else {
        renderEmptyState('activityChart', 'Review activity will appear here as members post reviews.');
    }
}
