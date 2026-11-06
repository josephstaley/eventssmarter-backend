<template>
    <div class="border" @click="handleClick">
        <div class="col-xs-2 text-center" style="overflow:hidden">
            <img :src="user.avatar"
                 alt="" style="height: 40px;">
        </div>
        <div class="col-xs-10">
            <div class="username">
                <strong>{{ user.name }}</strong>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</template>

<style scoped>
    .username {
        margin-top: 10px;
    }
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
        props: ['user'],
        methods: {
            handleClick(){
                window.location = `/people/${user.id}`;
            }
        }
    }
</script>
