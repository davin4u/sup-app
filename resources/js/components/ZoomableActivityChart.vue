<template>
    <div class="w-full">
        <div>
            <apexchart type="line" height="230" :options="getOptions()" :series="getSeries()"></apexchart>
        </div>
        <!--<div id="chart-line">
            <apexchart type="area" height="130" :options="chartOptionsLine" :series="seriesLine"></apexchart>
        </div>-->
    </div>
</template>

<script>
export default {
    name: "ZoomableActivityChart",

    data() {
        return {
            x_points: [],
            y_points: []
        }
    },

    computed: {
        points() {
            let points = [];

            for (let k in this.y_points) {
                points.push([this.x_points[k], this.y_points[k]]);
            }

            return points;
        }
    },

    methods: {
        getSeries() {
            return [{
                name: 'Average speed (km/h)',
                data: this.points
            }];
        },

        getOptions() {
            return {
                chart: {
                    id: 'vuechart-example',
                    events: {
                        zoomed: (chartContext, { xaxis, yaxis }) => {
                            this.$emit('chart-selection', {
                                min: xaxis.min,
                                max: xaxis.max
                            });
                        }
                    }
                },
                xaxis: {
                    type: 'numeric'
                }
            }
        }
    },

    created() {
        this.x_points = _.get(window, ['pageData', 'chart_data', 'x_points'], []);
        this.y_points = _.get(window, ['pageData', 'chart_data', 'y_points'], []);
    }
}
</script>
