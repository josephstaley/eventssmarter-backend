<template>
    <div class="filter-list">
        <div class="header">
            {{ type }}
        </div>
        <input type="text" class="form-control" placeholder="Search...">
        <a v-for="choice in choices" :class="{'active':active == choice.toLowerCase()}" :href="link(choice)">{{ choice }}</a>
        <a href="/events" :class="{'active':active=='null'}">All {{ type }}</a>
    </div>
</template>
<script>
    export default {
        props: ['choices','type','active'],
        methods: {
            link(choice){
                return `/events/${this.type}/${choice.toLowerCase()}`
            }
        }
    }
</script>