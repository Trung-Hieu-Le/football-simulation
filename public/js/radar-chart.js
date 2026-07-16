(function () {
    const statLabels = ['ATK', 'CRE', 'PAC', 'CTL', 'LCK', 'DEF', 'GOK', 'DIS', 'STA', 'MEN'];
    const statFields = [
        'attack', 'creative', 'pace', 'control', 'luck',
        'defense', 'goalkeeping', 'discipline', 'stamina', 'mental',
    ];
    const charts = {};

    function readStatsFromForm(form) {
        return statFields.map((field) => {
            const input = form.querySelector(`[name="${field}"]`);
            return parseInt(input?.value || '50', 10);
        });
    }

    function getAverageStats() {
        const avg = window.TEAM_AVG_STATS;
        if (Array.isArray(avg) && avg.length === statFields.length) {
            return avg.map((value) => Number(value) || 50);
        }
        return statFields.map(() => 50);
    }

    function initTeamRadar(canvasId, form) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined' || !form) {
            return;
        }

        if (charts[canvasId]) {
            charts[canvasId].destroy();
        }

        charts[canvasId] = new Chart(canvas, {
            type: 'radar',
            data: {
                labels: statLabels,
                datasets: [
                    {
                        label: 'Team',
                        data: readStatsFromForm(form),
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderColor: 'rgb(13, 110, 253)',
                        pointBackgroundColor: 'rgb(13, 110, 253)',
                    },
                    {
                        label: 'Average',
                        data: getAverageStats(),
                        backgroundColor: 'rgba(220, 53, 69, 0.08)',
                        borderColor: 'rgb(220, 53, 69)',
                        pointBackgroundColor: 'rgb(220, 53, 69)',
                        borderWidth: 2,
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: false,
                scales: {
                    r: {
                        min: 0,
                        max: 100,
                        ticks: { stepSize: 20 },
                    },
                },
                plugins: {
                    legend: { display: true, position: 'bottom' },
                },
            },
        });

        form.querySelectorAll('.team-stat-input').forEach((input) => {
            input.addEventListener('input', () => {
                charts[canvasId].data.datasets[0].data = readStatsFromForm(form);
                charts[canvasId].update();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[id^="teamEditModal"]').forEach((modal) => {
            modal.addEventListener('shown.bs.modal', () => {
                const form = modal.querySelector('form');
                const canvas = modal.querySelector('canvas');
                if (form && canvas) {
                    initTeamRadar(canvas.id, form);
                }
            });
        });
    });
})();
