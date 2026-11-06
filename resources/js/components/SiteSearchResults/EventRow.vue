<template>
    <div class="border" @click="handleClick">
        <div class="col-xs-2 text-center" style="overflow:hidden">
            <img :src="event.image"
                 alt="" style="height: 40px;">
        </div>
        <div class="col-xs-10">
            <div class="">
                <strong>{{ event.name }}</strong> <br>
                <div class="row lighter">
                    <div class="col-xs-6">
                        <i class="fa fa-map-marker"></i> {{ event.city }}, {{ event.state }}
                    </div>
                    <div class="col-xs-6">
                        <i class="fa fa-calendar"></i> {{ eventDate }}
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</template>

<style>
    .lighter {
        color: #c6c6c6;
    }
    .border {
        border-bottom: 1px solid #f5f5f5;
        margin-bottom: 10px;
        padding-bottom: 7px;
    }
    .border:hover {
        background: #be0de108;
        cursor: pointer;
    }
</style>

<script>
    import moment from "moment"
    export default {
        props: ['event'],
        computed: {
            eventDate(){
                return moment(this.event.starts_at).fromNow();
            }
        },
        methods: {
            handleClick(){
                window.location = `/${this.event.organization.slug}/${this.event.slug}_${this.event.id}`;
            }
        }
    }
</script>
