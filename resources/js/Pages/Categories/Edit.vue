<template>
  <AdminLayout title="Chỉnh sửa danh mục" breadcrumb="Chỉnh sửa thông tin danh mục">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-tags mr-2"></i>
          Chỉnh sửa: {{ category.name }}
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tên danh mục <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.name }"
                  placeholder="Nhập tên danh mục"
                  required
                />
                <div v-if="form.errors.name" class="invalid-feedback">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="form-group">
                <label>App <span class="text-danger">*</span></label>
                <select
                  :disabled="usePage().props.auth.user.roles.includes('app_owner')"
                  v-model="form.app_id"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors.app_id
                  }"
                  required
                >
                  <option value="">Chọn app</option>
                  <option v-for="app in apps" :key="app.id" :value="app.id">
                    {{ app.name }}
                  </option>
                </select>
                <div v-if="form.errors.app_id" class="invalid-feedback">
                  {{ form.errors.app_id }}
                </div>
              </div>

              <div class="form-group">
                <label>Mô tả</label>
                <textarea
                  v-model="form.description"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors.description
                  }"
                  rows="5"
                  placeholder="Mô tả về danh mục này"
                ></textarea>
                <div v-if="form.errors.description" class="invalid-feedback">
                  {{ form.errors.description }}
                </div>
              </div>

              <div class="form-group">
                <div class="form-check">
                  <input v-model="form.active" type="checkbox" class="form-check-input" id="active" />
                  <label class="form-check-label" for="active"> Kích hoạt danh mục </label>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <!-- Stats -->
              <div class="card bg-light">
                <h6 class="card-header">Thống kê</h6>
                <div class="card-body">
                  <div class="row text-center">
                    <div class="col-6">
                      <h4 class="text-primary">
                        {{ category.vouchers.length || 0 }}
                      </h4>
                      <small class="text-muted">Vouchers</small>
                    </div>
                    <div class="col-6">
                      <h4 class="text-info">
                        {{ category.vouchers.filter((voucher) => voucher.active).length || 0 }}
                      </h4>
                      <small class="text-muted">Đang hoạt động</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
              <i class="fas fa-save mr-1"></i> Cập nhật
            </button>
            <Link :href="route('admin.categories.index')" class="btn btn-secondary ml-2">
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

const props = defineProps({
  category: Object,
  apps: Array
})

const form = useForm({
  name: props.category.name,
  app_id: props.category.app_id,
  description: props.category.description || '',
  active: props.category.active
})

const submit = () => {
  form.put(route('admin.categories.update', props.category.id))
}
</script>
