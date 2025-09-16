<template>
  <AdminLayout title="Thêm chính sách" breadcrumb="Tạo chính sách mới">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-file-contract mr-2"></i>
          Thông tin chính sách mới
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>App <span class="text-danger">*</span></label>
                <select
                  :disabled="usePage().props.auth.user.roles.includes('app_owner')"
                  v-model="form.app_id"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.app_id
                  }"
                  required
                >
                  <option value="">Chọn app</option>
                  <option v-for="app in apps" :key="app.id" :value="app.id">
                    {{ app.name }}
                  </option>
                </select>
                <div v-if="form.errors?.app_id" class="invalid-feedback">
                  {{ form.errors.app_id }}
                </div>
              </div>

              <div class="form-group">
                <label>Loại chính sách <span class="text-danger">*</span></label>
                <select v-model="form.type" class="form-control" :class="{ 'is-invalid': form.errors?.type }" required>
                  <option value="">Chọn loại chính sách</option>
                  <option value="membership_policy">Điều khoản thành viên</option>
                  <option value="privacy_policy">Chính sách bảo mật</option>
                </select>
                <div v-if="form.errors?.type" class="invalid-feedback">
                  {{ form.errors.type }}
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="alert alert-info">
                <strong>Quan trọng:</strong> Mỗi app chỉ có thể có một chính sách cho mỗi loại. Nếu đã tồn tại, chính
                sách cũ sẽ bị ghi đè.
              </div>
            </div>
          </div>

          <!-- Content -->
          <div class="form-group">
            <label>Nội dung chính sách <span class="text-danger">*</span></label>
            <CKEditor
              v-model="form.content"
              :input-class="form.errors?.content ? 'form-control is-invalid' : 'form-control'"
              :config="editorConfig"
            />
            <div v-if="form.errors?.content" class="invalid-feedback d-block">
              {{ form.errors.content }}
            </div>
            <small class="form-text text-muted">
              Sử dụng trình soạn thảo để định dạng nội dung chính sách một cách chuyên nghiệp
            </small>
          </div>

          <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
              <i class="fas fa-save mr-1"></i> Tạo chính sách
            </button>
            <Link :href="route('admin.policies.index')" class="btn btn-secondary ml-2">
              <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CKEditor from '@/Components/CKEditor.vue'

const props = defineProps({
  apps: Array
})

const form = useForm({
  app_id: props.apps[0].id,
  type: '',
  content: ''
})

const editorConfig = {
  toolbar: [
    'heading',
    '|',
    'bold',
    'italic',
    'underline',
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
    'horizontalLine',
    '|',
    'undo',
    'redo'
  ],
  heading: {
    options: [
      {
        model: 'paragraph',
        title: 'Paragraph',
        class: 'ck-heading_paragraph'
      },
      {
        model: 'heading1',
        view: 'h1',
        title: 'Heading 1',
        class: 'ck-heading_heading1'
      },
      {
        model: 'heading2',
        view: 'h2',
        title: 'Heading 2',
        class: 'ck-heading_heading2'
      },
      {
        model: 'heading3',
        view: 'h3',
        title: 'Heading 3',
        class: 'ck-heading_heading3'
      },
      {
        model: 'heading4',
        view: 'h4',
        title: 'Heading 4',
        class: 'ck-heading_heading4'
      }
    ]
  }
}

const submit = () => {
  form.post(route('admin.policies.store'))
}
</script>
