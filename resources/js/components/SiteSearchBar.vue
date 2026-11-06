<template>
    <form class="search-bar-component" method="post" action="/">
        <slot></slot>
        <div class="col-md-9 col-12" style="padding-left: 0px;">
            <input class="form-control input-lg" placeholder="Search..." v-model="search" @click="request" name="query">
            <div class="results" v-if="events.length>0 || organizations.length>0 || users.length >0">
                <div v-if="events.length>0">
                    <h2 style="padding:5px">Events</h2>
                    <EventRow
                            v-for="event in events"
                            v-bind:key="event.id"
                            :event="event">
                    </EventRow>
                </div>
                <div v-if="organizations.length>0">
                    <h2 style="padding:5px">Organizations</h2>
                    <OrganizationRow
                            v-for="organization in organizations"
                            v-bind:key="organization.id"
                            :organization="organization">
                    </OrganizationRow>
                </div>
                <div v-if="users.length>0">
                    <h2 style="padding:5px">People</h2>
                    <UserRow
                            v-for="user in users"
                            v-bind:key="user.id"
                            :user="user">
                    </UserRow>
                </div>
            </div>
            <button class="btn btn-lg btn-block btn-search"><i class="fa fa-search"></i></button>
        </div>
        <div class="col-md-3  col-12 hidden">
            <select class="form-control input-lg" name="category">
                <option selected disabled>Category</option>
                <option v-for="(category, index) in categories" :key="index">{{ category }}</option>
            </select>
        </div>
        <div class="col-md-3  col-12" style="padding-right: 0px;">
            <select class="form-control input-lg" name="city">
                <option selected disabled>City</option>
                <option v-for="(city, index) in cities" :key="index">{{ city }}</option>
            </select>
        </div>

        <div class="clearfix"></div>
    </form>
</template>
<style scoped>
    .results {
        background: #fff;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        margin-top: -6px;
        width: 95%;
        padding: 3px;
        position: absolute;
        z-index: 10;
    }

    h2 {
        border-bottom: 1px solid #f5f5f5;
        padding-left: 10px;
    }
</style>
<script>

    import {UserRow, EventRow, OrganizationRow} from './SiteSearchResults'

    export default {
        props: ['cities', 'categories'],
        components: {UserRow, EventRow, OrganizationRow},
        watch: {
            search() {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(this.ajax, 400)
            }
        },
        methods: {
            ajax() {
                if (this.search.trim() === '') {
                    this.users = [];
                    this.events = [];
                    this.organizations = [];
                    return;
                }
                this.request();
            },
            request() {
                if (this.search === '') return;
                axios.get(`/api/search?query=${this.search}`).then(response => {
//                    this.users = response.data.users;
                    this.events = response.data.events;
                    this.organizations = response.data.organizations;
                })
            }
        },
        data() {
            return {
                search: '',
                timeout: 0,
                users: [],
                events: [],
                organizations: []
            }
        }
    }
</script>
