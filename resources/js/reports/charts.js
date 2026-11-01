import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from "chart.js";

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
);

Chart.defaults.font.family = "'Inter Variable', sans-serif";
Chart.defaults.color = "#111827";

const PRIMARY = "#2563EB";
const PRIMARY_BORDER = "#1D4ED8";
const SECONDARY_TEXT = "#6B7280";
const GRID_COLOR = "#E5E7EB";

const STATUS_COLORS = {
    Pendiente: "#F59E0B",
    Confirmado: "#2563EB",
    "En preparación": "#6366F1",
    Listo: "#14B8A6",
    Entregado: "#16A34A",
    Cancelado: "#DC2626",
};

const currency = new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
});

const builders = {
    sales: (data) => ({
        type: "bar",
        data: {
            labels: data.etiquetas,
            datasets: [
                {
                    label: "Ventas entregadas",
                    data: data.ventas,
                    backgroundColor: PRIMARY,
                    borderColor: PRIMARY_BORDER,
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: 44,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            `Ventas: ${currency.format(ctx.parsed.y ?? 0)}`,
                        afterLabel: (ctx) =>
                            `Pedidos entregados: ${data.pedidos[ctx.dataIndex] ?? 0}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 12,
                        color: SECONDARY_TEXT,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: GRID_COLOR },
                    ticks: {
                        color: SECONDARY_TEXT,
                        callback: (value) => currency.format(Number(value)),
                    },
                },
            },
        },
    }),

    status: (data) => ({
        type: "doughnut",
        data: {
            labels: data.etiquetas,
            datasets: [
                {
                    data: data.valores,
                    backgroundColor: data.etiquetas.map(
                        (label) => STATUS_COLORS[label] ?? SECONDARY_TEXT,
                    ),
                    borderColor: "#FFFFFF",
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            cutout: "58%",
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const count = ctx.parsed ?? 0;

                            return `${ctx.label}: ${count} ${count === 1 ? "pedido" : "pedidos"}`;
                        },
                    },
                },
            },
        },
    }),

    products: (data) => ({
        type: "bar",
        data: {
            labels: data.etiquetas,
            datasets: [
                {
                    label: "Unidades vendidas",
                    data: data.unidades,
                    backgroundColor: PRIMARY,
                    borderColor: PRIMARY_BORDER,
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: 28,
                },
            ],
        },
        options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) =>
                            data.etiquetas[items[0]?.dataIndex ?? 0] ?? "",
                        label: (ctx) => `Unidades: ${ctx.parsed.x ?? 0}`,
                        afterLabel: (ctx) =>
                            `Ingresos: ${currency.format(data.ingresos[ctx.dataIndex] ?? 0)}`,
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: GRID_COLOR },
                    ticks: {
                        precision: 0,
                        color: SECONDARY_TEXT,
                    },
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        color: "#111827",
                        callback(value) {
                            const label = this.getLabelForValue(value);

                            return typeof label === "string" &&
                                label.length > 22
                                ? `${label.slice(0, 22)}…`
                                : label;
                        },
                    },
                },
            },
        },
    }),
};

export function renderReportCharts() {
    document.querySelectorAll("canvas[data-report-chart]").forEach((canvas) => {
        if (canvas.dataset.reportChartInitialized === "true") {
            return;
        }

        const build = builders[canvas.dataset.reportChart];

        if (!build) {
            return;
        }

        try {
            const payload = JSON.parse(canvas.dataset.reportChartData ?? "{}");
            new Chart(canvas, build(payload));
            canvas.dataset.reportChartInitialized = "true";
        } catch {
            canvas
                .closest(".card")
                ?.querySelector("[data-report-chart-error]")
                ?.removeAttribute("hidden");
        }
    });
}
