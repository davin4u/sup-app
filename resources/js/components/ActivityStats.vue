<template>
    <div>
        <div class="w-full" v-if="hasSelectionStats">
            <div class="
                flex
                w-1/2
                items-center
                content-center
                justify-items-center
                justify-center
                bg-gray-100
                mx-auto
                text-sm
                rounded-lg
                border
                border-gray-300
                shadow-sm
                text-gray-700
            ">
                <div class="w-1/2 px-2">
                    <div><span class="mr-1">Distance:</span><span class="text-lg text-gray-900">{{ selection_stats.distance_km }}</span><span class="text-sm ml-1">km</span></div>
                    <div><span class="mr-1">Duration:</span><span class="text-lg text-gray-900">{{ selection_stats.duration_m }}</span><span class="text-sm ml-1">m</span></div>
                    <div><span class="mr-1">Average speed:</span><span class="text-lg text-gray-900">{{ selection_stats.average_speed_kmh }}</span><span class="text-sm ml-1">km/h</span></div>
                </div>

                <div class="w-1/2 px-2 text-right">
                    <div><span class="mr-1">Max speed:</span><span class="text-lg text-gray-900">{{ selection_stats.max_speed }}</span><span class="text-sm ml-1">km/h</span></div>
                    <div><span class="mr-1">Min speed:</span><span class="text-lg text-gray-900">{{ selection_stats.min_speed }}</span><span class="text-sm ml-1">km/h</span></div>
                    <div><span class="mr-1">Clean Average speed:</span><span class="text-lg text-gray-900">{{ selection_stats.clean_average_speed }}</span><span class="text-sm ml-1">km/h</span></div>
                </div>
            </div>
        </div>

        <ZoomableActivityChart @chart-selection="onSelection"></ZoomableActivityChart>

        <div class="mt-5">
            <a
                @click.prevent="toggleTrainingStats()"
                href="javascript:void(0)"
                class="hover:no-underline hover:text-gray-700 hover:bg-gray-100 px-3 py-2 border border-gray-300 rounded text-gray-700"
            >
                <StatsIcon class="w-6 h-6 text-gray-500 fill-current inline"></StatsIcon> {{ show_training_stats ? 'Hide training stats' : 'Show training stats' }}
            </a>
        </div>

        <div v-if="show_training_stats && hasSelectionStats && intervals.length > 0" class="mt-5 w-full">
            <div v-for="group in intervals" class="w-full flex justify-content-between">
                <div
                    v-for="interval in group"
                    class="mb-3 p-2 w-1/3 rounded mx-1"
                    :class="{
                        'bg-gray-100': !isWorseInterval(interval),
                        'bg-red-100': isWorseInterval(interval)
                    }"
                >
                    <div class="mb-2 text-lg font-bold flex">
                        <div class="self-center">#{{ interval.number }}</div>

                        <div
                            class="self-center text-right flex-grow"
                            v-if="getLabels(interval)"
                            v-html="getLabels(interval)"
                        ></div>
                    </div>

                    <div class="flex justify-content-between">
                        <div>
                            <div class="text-sm text-center text-gray-600">Distance:</div>
                            <div class="text-lg text-gray-900 text-center" v-html="interval.stats.distance"></div>
                        </div>

                        <div>
                            <div class="text-sm text-center text-gray-600">Time:</div>
                            <div class="text-lg text-gray-900 text-center" v-html="interval.stats.elapsed_time"></div>
                        </div>

                        <div>
                            <div class="text-sm text-center text-gray-600">Max speed:</div>
                            <div class="text-lg text-gray-900 text-center">{{ interval.stats.max_speed }}<span class="text-sm">km/h</span></div>
                        </div>

                        <div>
                            <div class="text-sm text-center text-gray-600">Min speed:</div>
                            <div class="text-lg text-gray-900 text-center">{{ interval.stats.min_speed }}<span class="text-sm">km/h</span></div>
                        </div>

                        <div>
                            <div class="text-sm text-center text-gray-600">Avg speed:</div>
                            <div class="text-lg text-gray-900 text-center">{{ interval.stats.clean_average_speed }}<span class="text-sm">km/h</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 relative w-full" style="height: 300px;">
            <div id="track-map" class="w-full h-full"></div>
        </div>
    </div>
</template>

<script>
import ZoomableActivityChart from "./ZoomableActivityChart";
import {http} from "../mixins/http";
import StatsIcon from "./icons/StatsIcon";

export default {
    name: "ActivityStats",

    components: {StatsIcon, ZoomableActivityChart},

    mixins: [http],

    data() {
        return {
            selection_stats: {},
            activity: {},
            show_training_stats: false
        }
    },

    computed: {
        hasSelectionStats() {
            return Object.keys(this.selection_stats).length > 0;
        },

        mapPoints() {
            let points = [];

            let coords = _.get(window, ['pageData', 'data', 'points'], []);

            for (let k in coords) {
                points.push([coords[k].longitude, coords[k].latitude]);
            }

            return points;
        },

        intervals() {
            let groups = [];
            let i = 0;
            let intervals = _.get(this.selection_stats, ['intervals_stats'], []);

            for (let k in intervals) {
                if (!groups[i]) {
                    groups[i] = [];
                }

                groups[i].push(intervals[k]);

                if ((k+1) % 3 === 0) {
                    i++;
                }
            }

            return groups;
        }
    },

    methods: {
        onSelection(payload) {
            this.getActivityDataForSelection(payload.min, payload.max);
        },

        getActivityDataForSelection(start, end) {
            let url = '/activities/' + this.activity.id + '/activity-data';

            if (!_.isUndefined(start) && !_.isUndefined(end) && !_.isNull(start) && !_.isNull(end)) {
                url += '?start=' + start + '&end=' + end;
            }

            this.http().get(url)
                .then((response) => {
                    this.selection_stats = _.get(response, ['data', 'stats'], {});
                });
        },

        getLabels(interval) {
            if (interval.labels.length === 0) {
                return null;
            }

            let html = '';

            if (interval.labels.indexOf('best_max_speed') !== -1) {
                html += this.buildLabel('best speed', 'green', 'green');
            }

            if (interval.labels.indexOf('best_avg_speed') !== -1) {
                html += this.buildLabel('best avg speed', 'yellow', 'yellow');
            }

            if (interval.labels.indexOf('best_distance') !== -1) {
                html += this.buildLabel('best distance', 'blue', 'blue');
            }

            return html;
        },

        buildLabel(text, bg, color) {
            return `<span class="rounded mx-1 text-sm px-2 py-1 text-${color}-900 bg-${bg}-100">${text}</span>`;
        },

        isWorseInterval(interval) {
            let labels = _.get(interval, ['labels'], []);

            return labels.indexOf('worse_avg_speed') !== -1;
        },

        toggleTrainingStats() {
            this.show_training_stats = !this.show_training_stats;
        }
    },

    created() {
        this.selection_stats = _.get(window, ['pageData', 'data', 'stats'], {});
        this.activity = _.get(window, ['pageData', 'activity'], {});
    },

    mounted() {
        let mapboxgl = require('mapbox-gl/dist/mapbox-gl.js');

        mapboxgl.accessToken = window.mapbox_access_token;

        setTimeout(() => {
            let map = new mapboxgl.Map({
                container: 'track-map',
                style: 'mapbox://styles/mapbox/streets-v11',
                center: this.mapPoints[0],
                zoom: 15
            });

            map.on('load', () => {
                map.addSource('route', {
                    'type': 'geojson',
                    'data': {
                        'type': 'Feature',
                        'properties': {},
                        'geometry': {
                            'type': 'LineString',
                            'coordinates': this.mapPoints
                        }
                    }
                });

                map.addLayer({
                    'id': 'route',
                    'type': 'line',
                    'source': 'route',
                    'layout': {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    'paint': {
                        'line-color': '#ff0000',
                        'line-width': 3
                    }
                });
            })
        }, 2000);
    }
}
</script>
