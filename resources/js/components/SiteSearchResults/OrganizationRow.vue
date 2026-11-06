<template>
    <div class="border" @click="this.handleClick">
        <div class="col-xs-2 text-center" style="overflow:hidden">
            <img :src="organization.cover_image"
                 alt="" style="height: 40px">
        </div>
        <div class="col-xs-10">
            <div class="">
                <strong>{{ organization.name }}</strong> <br>
                <div class="row lighter">
                    <div class="col-xs-6">
                        <i class="fa fa-map-marker"></i> {{ organization.city }}, {{ organization.state }}
                    </div>
                    <div class="col-xs-6">
                        <i class="fa fa-calendar"></i> {{ organization.upcoming }} Upcoming
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
    export default {
        props: ['organization'],
        methods: {
            handleClick(){
                window.location = `/${this.organization.slug}`;
            }
        }
    }
</script>
