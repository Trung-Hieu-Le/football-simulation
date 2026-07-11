(function () {
    const statLabels = ['ATK', 'DEF', 'CTL', 'CRE', 'PAC', 'MEN', 'DIS', 'LCK', 'STA', 'GK'];
    const statFields = [
        'attack', 'defense', 'control', 'creative', 'pace',
        'mental', 'discipline', 'luck', 'stamina', 'goalkeeping',
    ];
    const charts = {};

    function readStatsFromForm(form) {
        return statFields.map((field) => {
            const input = form.querySelector(`[name="${field}"]`);
            return parseInt(input?.value || '50', 10);
        });
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
                datasets: [{
                    label: 'Stats',
                    data: readStatsFromForm(form),
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgb(13, 110, 253)',
                    pointBackgroundColor: 'rgb(13, 110, 253)',
                }],
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
                    legend: { display: false },
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
