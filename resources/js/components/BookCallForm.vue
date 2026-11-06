<template>
   <div class="row">
      <div class="col-md-4 col-sm-6">
         <vue-cal
            class="vuecal--green-theme"
            xsmall
            hide-view-selector
            :time="false"
            active-view="month"
            :disable-views="['week', 'day', 'years']"
            :min-date="event_start_date"
            :max-date="event_end_date"
            @cell-click="dateClick"
            :selected-date="selectedDate"
            :hide-weekdays="!avilable_weekends ? [6, 7] : []"
         >
         </vue-cal>
      </div>
      <div class="col-md-4 col-sm-6">
         <p class="bold mt-30">Book Date</p>
         <input type="text" v-model="selectedDate" readonly class="form-control" name="call_date">
         <p class="bold mt-30">Choose Time</p>
         <select name="call_time" class="form-control">
            <option v-for="(time, index) in availableTimes" :key="index" :value="time">{{ getStartTime(time) }} - {{ getEndTime(time) }}</option>
         </select>
         <div class="text-danger" v-if="call_time_warning">{{ call_time_warning }}</div>
         <div class="ticket-price mt-30">
            <label>Price  {{ price }}</label>
         </div>
      </div>
   </div>
</template>

<script>
import VueCal from "vue-cal";
import "vue-cal/dist/vuecal.css";
import moment from "moment";
export default {
   props:[
      "event_start_date",
      "event_end_date",
      "avilable_weekends",
      "call_avilable_start_time",
      "call_avilable_end_time",
      "call_time",
      "old_call_start_times",
      "price",
      "call_time_warning",
      "quantity"
   ],
   components: { VueCal },
   data() {
      return {
         event_starts_at: null,
         event_ends_at: null,
         selectedDate: null,
         scheduledTimes : [],
         availableTimes: [],
         callTime: null,
      };
   },
   mounted() {
      // if today is passed rather than event start day
      if (moment(this.event_start_date).diff(moment()) < 0){
         this.event_start_date = moment().format('YYYY-MM-DD')
      }
      this.event_starts_at = moment(this.event_start_date)
      this.event_ends_at = moment(this.event_end_date)
      this.scheduledTimes = []
      this.old_call_start_times.map(datetime =>{
         let date = moment(datetime.call_start_time).format('YYYY-MM-DD')
         if (!this.scheduledTimes[date]){
            this.scheduledTimes[date] = []
         }
         this.scheduledTimes[date].push(moment(datetime.call_start_time).format('HH:mm'))
      })
      this.selectedDate = moment(this.event_start_date).format('YYYY-MM-DD')
      if (moment(this.event_end_date).diff(moment()) >= 0){
         this.dateClick(this.selectedDate)
      }
   },
   methods: {
      dateClick(date){
         date = moment(date).format('YYYY-MM-DD')
         this.selectedDate = date
         this.availableTimes = []
         let callStartTime = moment(this.call_avilable_start_time, 'HH:mm')
         let callEndtime = moment(this.call_avilable_end_time, 'HH:mm')
         if(this.selectedDate === this.event_starts_at.format('YYYY-MM-DD') && callStartTime.isBefore(moment(this.event_starts_at.format('HH:mm'), 'HH:mm'))){
            callStartTime = moment(this.event_starts_at.format('HH:mm'), 'HH:mm')
         }
         if (this.selectedDate === this.event_ends_at.format('YYYY-MM-DD') && callEndtime.isAfter(moment(this.event_ends_at.format('HH:mm'), 'HH:mm'))){
            callEndtime = moment(this.event_ends_at.format('HH:mm'), 'HH:mm')
         }
         if (this.call_time === "01:00:00"){
            this.callTime = 1
            if (callStartTime.add(1, 'hours').diff(callEndtime) > 0) return
            do{
               callStartTime.subtract(1, 'hours');
               this.availableTimes.push(callStartTime.format('HH:mm'));
               callStartTime.add(1, 'hours');
            }
            while(callStartTime.add(1, 'hours').diff(callEndtime) <= 0);
         }
         else{
            this.callTime = parseInt(this.call_time.substr(3, 2))
            if (callStartTime.add(this.callTime, 'minutes').diff(callEndtime) > 0) return
            do{
               callStartTime.subtract(this.callTime, 'minutes');
               this.availableTimes.push(callStartTime.format('HH:mm'));
               callStartTime.add(this.callTime, 'minutes');
            }
            while(callStartTime.add(this.callTime, 'minutes').diff(callEndtime) <= 0);
         }
         if (this.scheduledTimes[date] && this.scheduledTimes[date].length > 0){
            this.scheduledTimes[date].map(time =>{
               this.availableTimes = this.availableTimes.filter(value =>{
                  return value !== time
               })
            })
         }
         if (this.availableTimes.length > this.quantity) this.availableTimes.length = this.quantity
      },
      getEndTime(time){
         if (this.callTime === 1){
            return moment(time, 'HH:mm').add(1, 'hour').format('hh:mm A')
         }
         else {
            return moment(time, 'HH:mm').add(this.callTime, 'minutes').format('hh:mm A')
         }
      },
      getStartTime(time){
         return moment(time, 'HH:mm').format('hh:mm A')
      }
   },
   watch: {
   },
};
</script>
<style>
.vuecal__cell--disabled {
   text-decoration: line-through;
   background: none !important;
}
.vuecal__cell--selected:before {
   border-color: rgb(255, 207, 77) !important;
}
.vuecal__cell--disabled:before{
   border-color: hsla(0,0%,76.9%,.25) !important;
}
.vuecal__title-bar {
   background-color: rgb(255, 207, 77) !important;
   color: #fff;
}
.vuecal__cell--today,
.vuecal__cell--current {
   background-color: #9c27b0b3 !important;
   color: white;
}
.vuecal__cell:not(.vuecal__cell--disabled) {
   color: black;
   background-color: #fffacd;
}
.vuecal__cell {
   cursor: pointer;
}
.vuecal:not(.vuecal--day-view) .vuecal__cell--selected:not(.vuecal__cell--disabled) {
   background-color: rgb(255, 207, 77) !important;
   color: white;
}
.vuecal__cell-date{
    line-height: 40px !important;
}
.vuecal__weekdays-headings, .vuecal__all-day{
   padding-right: 0px !important;
}
.bold{
   font-weight: bold;
   color: #000;
}
.mt-30{
   margin-top: 30px;
}
</style>