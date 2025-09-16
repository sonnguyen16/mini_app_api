<template>
  <div>
    <textarea :id="editorId" v-model="content" :name="name" :class="inputClass"></textarea>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  name: {
    type: String,
    default: 'editor'
  },
  config: {
    type: Object,
    default: () => ({})
  },
  inputClass: {
    type: String,
    default: 'form-control'
  }
})

const emit = defineEmits(['update:modelValue'])

const editorId = ref(`ckeditor-${Math.random().toString(36).substr(2, 9)}`)
const content = ref(props.modelValue)
let editor = null

const defaultConfig = {
  toolbar: [
    'heading',
    '|',
    'bold',
    'italic',
    'underline',
    'strikethrough',
    '|',
    'bulletedList',
    'numberedList',
    '|',
    'outdent',
    'indent',
    '|',
    'link',
    'blockQuote',
    'insertTable',
    '|',
    'undo',
    'redo'
  ],
  heading: {
    options: [
      { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
      { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
      { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
      { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
    ]
  },
  table: {
    contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
  }
}

const initEditor = async () => {
  try {
    // Load CKEditor from CDN if not already loaded
    if (!window.ClassicEditor) {
      await loadCKEditor()
    }

    const config = { ...defaultConfig, ...props.config }

    editor = await window.ClassicEditor.create(document.getElementById(editorId.value), config)

    editor.setData(content.value)

    editor.model.document.on('change:data', () => {
      const data = editor.getData()
      content.value = data
      emit('update:modelValue', data)
    })
  } catch (error) {
    console.error('CKEditor initialization error:', error)
  }
}

const loadCKEditor = () => {
  return new Promise((resolve, reject) => {
    if (window.ClassicEditor) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = 'https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js'
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })
}

watch(
  () => props.modelValue,
  (newValue) => {
    if (editor && newValue !== content.value) {
      content.value = newValue
      editor.setData(newValue)
    }
  }
)

onMounted(() => {
  initEditor()
})

onUnmounted(() => {
  if (editor) {
    editor.destroy()
  }
})
</script>

<style>
.ck-editor__editable {
  min-height: 200px;
}

.ck.ck-editor {
  max-width: 100%;
}

.ck.ck-editor__main > .ck-editor__editable {
  border-radius: 0 0 0.375rem 0.375rem;
}

.ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
  border-radius: 0.375rem 0.375rem 0 0;
}
</style>
