<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="text-center">
                    <h4 class="title">Testimonials</h4>
                    <hr>
                    <div class="testimonial">
                        <blockquote>{{ quote }}</blockquote>
                    </div>
                </div>
            </div>
        </div>
        <div class="row section">
            <div @click="setActive(index)"
                 :class="{'active':index===active}"
                 class="col-md-3 text-center quote"
                 v-for="(quote,index) in quotes">
                <div><img :src="quote.image" alt="Quoted for AJE"></div>
                <div v-if="active===index">
                    <span class="text-uppercase">{{ quote.name }}</span>
                    <p>{{ quote.subtitle }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    blockquote:before {
        font-family: serif;
        content: open-quote;
        font-size: 4em;
        line-height: 0.1em;
        margin-right: 0.25em;
        vertical-align: -0.4em;
    }
    blockquote:after{
        font-family: serif;
        content: close-quote;
        font-size: 4em;
        line-height: 0.1em;
        margin-left: 0.25em;
        vertical-align: -0.4em;
    }
    img {
        border-radius: 55%;
        height: 80px;
        width: 80px;
        margin: 0 auto;
    }

    .quote {
        cursor: pointer;
        opacity: .4;
        color: white;
    }

    .quote.active {
        opacity: 1;
    }

    p {
        font-weight: 100;
    }

    blockquote {
        padding: 0;
        margin: 0 0 22px;
        border-left: none;
        color: white;
        font-size: 30px;
    }
</style>
<script>
    export default {
        props: ['quotes'],
        data() {
            return {active: 1}
        },
        methods: {
            setActive(index) {
                this.active = index;
            }
        },
        computed: {
            quote() {
                return this.quotes[this.active].quote;
            }
        }
    }
</script>