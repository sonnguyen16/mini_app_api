<template>
  <AdminLayout title="Chỉnh sửa App" breadcrumb="Chỉnh sửa thông tin ứng dụng">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-mobile-alt mr-2"></i>
          Chỉnh sửa: {{ app.name }}
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <!-- App Info -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Thông tin ứng dụng</h6>

              <div class="form-group">
                <label>Tên ứng dụng <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.name }"
                  placeholder="Nhập tên ứng dụng"
                  required
                />
                <div v-if="form.errors.name" class="invalid-feedback">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="form-group">
                <label>Mini App ID </label>
                <input
                  v-model="form.mini_app_id"
                  type="text"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.mini_app_id
                  }"
                  placeholder="123456789"
                />
                <div v-if="form.errors?.mini_app_id" class="invalid-feedback">
                  {{ form.errors.mini_app_id }}
                </div>
                <small class="form-text text-muted"> ID định danh của Mini App trên Zalo </small>
              </div>

              <div class="form-group">
                <label>Mô tả</label>
                <textarea
                  v-model="form.description"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors.description
                  }"
                  rows="3"
                  placeholder="Mô tả về ứng dụng"
                ></textarea>
                <div v-if="form.errors.description" class="invalid-feedback">
                  {{ form.errors.description }}
                </div>
              </div>

              <div class="form-group">
                <label>Logo ứng dụng</label>
                <div v-if="app.logo" class="mb-2">
                  <img
                    :src="`/storage/${app.logo}`"
                    alt="Current app logo"
                    class="img-thumbnail"
                    style="max-width: 100px; max-height: 100px"
                  />
                  <p class="text-muted small mt-1">Logo hiện tại</p>
                </div>
                <input
                  @change="handleLogoUpload"
                  type="file"
                  class="form-control-file"
                  :class="{ 'is-invalid': form.errors.logo }"
                  accept="image/*"
                />
                <div v-if="form.errors.logo" class="invalid-feedback">
                  {{ form.errors.logo }}
                </div>
                <small class="form-text text-muted">
                  Chỉ chấp nhận file ảnh (JPG, PNG). Tối đa 2MB. Để trống nếu không thay đổi.
                </small>
              </div>

              <div class="form-group">
                <div class="form-check">
                  <input v-model="form.active" type="checkbox" class="form-check-input" id="active" />
                  <label class="form-check-label" for="active"> Kích hoạt ứng dụng </label>
                </div>
              </div>
            </div>

            <!-- Owner Info & Stats -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Thông tin đăng nhập</h6>

              <!-- input owner_name -->
              <div class="form-group">
                <label>Tên chủ sở hữu <span class="text-danger">*</span></label>
                <input
                  v-model="form.owner_name"
                  type="text"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.owner_name
                  }"
                  placeholder="Nhập tên chủ sở hữu"
                  required
                />
                <div v-if="form.errors?.owner_name" class="invalid-feedback">
                  {{ form.errors.owner_name }}
                </div>
              </div>

              <div class="card bg-light mb-3">
                <div class="card-body">
                  <p class="card-text">
                    <strong>Email:</strong>
                    {{ app.owner_email }}<br />
                  </p>
                  <button @click="resetOwnerPassword" class="btn btn-sm btn-warning" :disabled="processing">
                    <span v-if="processing" class="spinner-border spinner-border-sm mr-1"></span>
                    <i class="fas fa-key mr-1"></i> Reset mật khẩu
                  </button>
                </div>
              </div>

              <h6 class="text-primary mb-3">Thống kê</h6>
              <!-- Stats -->
              <div class="card bg-light">
                <div class="card-body">
                  <div class="row text-center">
                    <div class="col-4">
                      <h4 class="text-primary">
                        {{ app?.user_profiles?.length || 0 }}
                      </h4>
                      <small class="text-muted">Người dùng</small>
                    </div>
                    <div class="col-4">
                      <h4 class="text-info">
                        {{ app?.vouchers?.length || 0 }}
                      </h4>
                      <small class="text-muted">Vouchers</small>
                    </div>
                    <div class="col-4">
                      <h4 class="text-success">
                        {{ app?.categories?.length || 0 }}
                      </h4>
                      <small class="text-muted">Danh mục</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
              <i class="fas fa-save mr-1"></i> Cập nhật
            </button>
            <Link :href="route('admin.apps.show', app.id)" class="btn btn-info ml-2">
              <i class="fas fa-eye mr-1"></i> Xem chi tiết
            </Link>
            <Link :href="route('admin.apps.index')" class="btn btn-secondary ml-2">
              <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  app: Object,
  stats: Object,
  auth: Object
})

const processing = ref(false)

const form = useForm({
  name: props.app.name,
  mini_app_id: props.app.mini_app_id,
  description: props.app.description || '',
  logo: null,
  active: props.app.active,
  owner_name: props.app.owner_name
})

const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.logo = file
  }
}

const resetOwnerPassword = () => {
  if (confirm('Bạn có chắc chắn muốn reset mật khẩu cho chủ app này?')) {
    processing.value = true
    router.post(
      route('admin.apps.reset-password', props.app.id),
      {},
      {
        onFinish: () => {
          processing.value = false
        }
      }
    )
  }
}

const submit = () => {
  form.post(route('admin.apps.update', props.app.id), {
    forceFormData: true
  })
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}
</script>
