<template>
    <div class="comment-add">


      <div class="row">
          <div class="col-4">
              <div class="form-floating mb-2">
                  <input type="text"
                         v-model="author"
                         class="form-control" id="floatingInput" placeholder="name@example.com">
                  <label for="floatingInput">Имя автора</label>
              </div>
          </div>
      </div>

        <div class="form-floating">
    <textarea
        v-model="text"
        class="form-control"
        placeholder="Написать комментарий..."
    ></textarea>
            <label for="floatingTextarea">Написать комментарий...</label>
        </div>

        <input
            type="file"
            multiple
            @change="onFilesSelected"
            class="form-control mt-2"
        />

        <div class="previews" v-if="files.length">
            <div
                v-for="file in files"
                :key="file.name"
                class="preview-item"
            >
                {{ file.name }}
            </div>
        </div>

        <button class="btn btn-primary mt-2" @click="submit">
            Добавить комментарий
        </button>

    </div>
</template>

<script>
import { useCommentsStore } from '@/stores/useCommentsStore'

export default {
    name: 'CommentAddForm',

    props: {
        taskId: { type: Number, required: true }
    },

    data() {
        return {
            author:'',
            text: '',
            files: [],
            store: useCommentsStore()
        }
    },

    methods: {
        onFilesSelected(e) {
            this.files = Array.from(e.target.files)
        },

        async submit() {
            await this.store.addComment(this.taskId, {
                text: this.text,
                files: this.files,
                author: 'Система'
            })

            this.text = ''
            this.files = []
        }
    }
}
</script>

<style scoped>
.preview-item {
    font-size: 14px;
    margin-top: 4px;
}
</style>
