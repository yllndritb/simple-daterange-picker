<template>
  <FilterContainer>
    <span>{{ filter.name }}</span>
    <template #filter>
      <div class="relative">
        <input type="text" class="hidden">
        <input
            :id="id"
            class="block w-full form-control form-control-sm form-input form-input-bordered text-sm px-1"
            :class="{ 'text-white': (value == null) }"
            type="text"
            :dusk="`${filter.name}-daterange-filter`"
            name="daterangefilter"
            autocomplete="off"
            :value="value"
            @apply.daterangepicker="onFocus(e)"
            :placeholder="placeholder"
        />
      </div>
    </template>
  </FilterContainer>
</template>

<script>

export default {
  emits: ['change'],

  props: {
    resourceName: {
      type: String,
      required: true,
    },
    filterKey: {
      type: String,
      required: true,
    }
  },

  data: () => ({
    id: null,
    value: '',
  }),

  mounted() {
    this.id = 'dateRangeCalendar_' + this.generateId()

    setTimeout(() => {
      this.initDateRange()
    }, 1);
  },
  watch: {
    value() {
      this.handleChange(this.value)
    },
  },
  methods: {
    handleChange(value) {
      this.$store.commit(`${this.resourceName}/updateFilterState`, {
        filterClass: this.filterKey,
        value: value,
      })
      this.$emit('change')
    },
    initDateRange: function () {
      const ref = this
      const idSelector = ('#' + this.id)
      const dateTimeRange = ref.filter.dateTimeRange
      let {startDateString,endDateString}=this.filterStartEndDateTime
      startDateString = startDateString ?? ''
      endDateString = endDateString ?? ''
      if(startDateString && endDateString){
        $(idSelector).daterangepicker({
          "startDate": startDateString,
          "endDate": endDateString,
          "timePicker": true,
          "timePickerIncrement": 30,
          "timePicker24Hour": true,
          locale: {
            format: 'MM/DD/YYYY HH:mm',
            cancelLabel: 'Clear'
          }
        }, function (start, end, label) {
          if (start && end) {
            ref.currentStartDate = start
            ref.currentEndDate = end
          }
        }).on('cancel.daterangepicker', function(ev, picker) {
          $('#daterange').val('');
          ref.value = ''
        })
            .on('apply.daterangepicker', function (ev, picker) {
              if (ref.currentStartDate && ref.currentEndDate) {
                ref.value = ref.currentStartDate.format('MM/DD/YYYY HH:mm') + ' to ' + ref.currentEndDate.format('MM/DD/YYYY HH:mm')
              }
            })
      }else{
        $(idSelector).daterangepicker({
          "autoUpdateInput": false,
          "timePicker": true,
          "timePickerIncrement": 30,
          "timePicker24Hour": true,
          locale: {
            format: 'MM/DD/YYYY HH:mm',
            cancelLabel: 'Clear'
          }
        }, function (start, end, label) {
          if (start && end) {
            ref.currentStartDate = start
            ref.currentEndDate = end
          }
        }).on('cancel.daterangepicker', function(ev, picker) {
          $('#daterange').val('');
          ref.value = ''
        }).on('apply.daterangepicker', function (ev, picker) {
          if (ref.currentStartDate && ref.currentEndDate) {
            ref.value = ref.currentStartDate.format('MM/DD/YYYY HH:mm') + ' to ' + ref.currentEndDate.format('MM/DD/YYYY HH:mm')
          }
        })
      }

    },
    generateId: function () {
      return Math.random().toString(36).substring(2) +
          (new Date()).getTime().toString(36);
    },
  },

  computed: {
    filter() {
      return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey);
    },
    filterStartEndDateTime() {
      const value = this.filter.currentValue
      const [startDateString, endDateString] = value.split(' to ');
      return {startDateString, endDateString};
    },
  },
}
</script>
