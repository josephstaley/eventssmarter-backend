<template>
    <div>
        <div v-if="!editing">
            <a href="#" @click="loadEditor"><i class="fa fa-pencil"></i> Edit</a>
            <div v-html="content.body"></div>
        </div>
        <div v-if="editing">
            <div id="editor" v-html="content.body"></div>
            <div v-if="saved" style="position: fixed">Saved...</div>
        </div>
    </div>
</template>
<script>
    import Quill from "quill"
    import "quill/dist/quill.bubble.css"

    const url = function (id) {
        return `/admin/content/${id}`
    };

    export default {
        props: ['content'],
        data: function () {
            return {editing: false, body: '', saved: false, timeout: null}
        },
        mounted() {
            this.body = this.content.body;
        },
        methods: {
            post() {
                const body = this.quill.root.innerHTML;
                axios.post(url(this.content.id), {body});
                this.saved = true;
                setTimeout(() => this.saved = false, 500);
            },
            debounce() {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(this.post, 1000);
            },
            editorDelay() {
                this.quill = new Quill('#editor', {
                    theme: 'bubble',
                    placeholder: 'Compose an epic...'
                });
                this.quill.on('text-change', this.debounce);
            },
            loadEditor() {
                this.editing = true;
                setTimeout(this.editorDelay, 300);
            }
        }
    }
</script>