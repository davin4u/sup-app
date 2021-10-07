<template>
    <div>
        <canvas id="myChart" width="400" height="200"></canvas>
    </div>
</template>

<script>
import Chart from 'chart.js/auto';

export default {
    name: "ActivityChart",

    data() {
        return {
            chart_data: [],
            x_points: [],
            y_points: []
        }
    },

    methods: {
        initChart() {
            let ctx = document.getElementById('myChart').getContext('2d');

            let myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.x_points,
                    datasets: [{
                        label: 'Dataset',
                        data: this.y_points,
                        backgroundColor: 'rgba(54, 162, 235, 0.4)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        fill: 'start'
                    }]
                },
                options: {
                    plugins: {
                        filler: {
                            propagate: false,
                        },
                        title: {
                            display: true,
                            //text: (ctx) => 'Fill: ' + ctx.chart.data.datasets[0].fill
                            text: (ctx) => 'Average speed'
                        },
                        tooltip: {
                            callbacks: {
                                footer: this.tooltipBuilder,
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                    }
                }
            });
        },

        tooltipBuilder(tooltipItems) {
            return `Distance: ${tooltipItems[0].label} m\nAverage Speed: ${tooltipItems[0].raw} km/h`;
        }
    },

    mounted() {
        this.initChart();
    },

    created() {
        this.x_points = _.get(window, ['chart_data', 'x_points'], []);
        this.y_points = _.get(window, ['chart_data', 'y_points'], []);
    }
}
</script>

