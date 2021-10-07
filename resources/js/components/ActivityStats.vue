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
    </div>
</template>

<script>
import ZoomableActivityChart from "./ZoomableActivityChart";
import {http} from "../mixins/http";

export default {
    name: "ActivityStats",

    components: {ZoomableActivityChart},

    mixins: [http],

    data() {
        return {
            selection_stats: {},
            activity: {}
        }
    },

    computed: {
        hasSelectionStats() {
            return Object.keys(this.selection_stats).length > 0;
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
        }
    },

    created() {
        this.selection_stats = _.get(window, ['pageData', 'data', 'stats'], {});
        this.activity = _.get(window, ['pageData', 'activity'], {});
    }
}
</script>
