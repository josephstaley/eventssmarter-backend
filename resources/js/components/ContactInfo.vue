<template>
    <div>
        <div class="col-md-4">
            <input v-model="fname" :placeholder="i18n.first_name" name="fname" class="form-control" required>
         </div>
        <div class="col-md-4">
            <input v-model="lname" :placeholder="i18n.last_name" name="lname" class="form-control" required>
        </div>
        <div class="col-md-4 col-md-offset-3">
            <input v-model="email" :placeholder="i18n.email_address" name="email" class="form-control" required>
        </div>
        <div class="col-md-4">
            <input v-model="phone" :placeholder="i18n.phone_number" name="phone_number" class="form-control" required>
        </div>
        <div v-if="emailExists" id="emailDuplicate" class="col-md-12 text-danger">
            <br>
            {{ i18n.that_email_already_exists }}, <a class="link" href="/login">{{ i18n.please_login }}</a> {{ i18n.before_continuing }}.
            <br>
            {{ i18n.or_you_can_reset_password }} <a class="link" href="/password/reset">{{ i18n.here }}</a> {{ i18n.to_login }}.
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            user: {
                type: Object,
                default(){
                    return {}
                }
            },
            i18n: {
                type: Array,
                default(){
                    return []
                }
            }
        },
        watch: {
            email(){
                clearTimeout(this.timeout);
                this.timeout = setTimeout(this.ajaxEmail,400);
            }
        },
        methods: {
            ajaxEmail(){
                axios.get(`/api/email?query=${this.email}`)
                    .then(response=>{
                        this.emailExists = response.data.exists;
                    })
            }
        },
        mounted(){
            if(this.user.email){
                this.fname = this.user.fname;
                this.lname = this.user.lname;
                this.email = this.user.email;
                this.phone = this.user.phone_number;
            }
        },
        data(){
            return {
                timeout: 0,
                email: '',
                phone: '',
                fname: '',
                lname: '',
                emailExists: false,
            }
        }
    }
</script>
<style scoped>
.link{
    text-decoration: underline;
}
</style>
