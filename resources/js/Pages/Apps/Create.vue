<template>
  <AdminLayout title="Thêm App" breadcrumb="Tạo ứng dụng mới">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-mobile-alt mr-2"></i>
          Thông tin ứng dụng mới
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
                  :class="{ 'is-invalid': form.errors?.name }"
                  placeholder="Nhập tên ứng dụng"
                  required
                />
                <div v-if="form.errors?.name" class="invalid-feedback">
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
              </div>

              <div class="form-group">
                <label>Mô tả</label>
                <textarea
                  v-model="form.description"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.description
                  }"
                  rows="3"
                  placeholder="Mô tả về ứng dụng"
                ></textarea>
                <div v-if="form.errors?.description" class="invalid-feedback">
                  {{ form.errors.description }}
                </div>
              </div>

              <div class="form-group">
                <label>Logo ứng dụng</label>
                <input
                  @change="handleLogoUpload"
                  type="file"
                  class="form-control-file"
                  :class="{ 'is-invalid': form.errors?.logo }"
                  accept="image/*"
                />
                <div v-if="form.errors?.logo" class="invalid-feedback">
                  {{ form.errors.logo }}
                </div>
                <small class="form-text text-muted"> Chỉ chấp nhận file ảnh (JPG, PNG). Tối đa 2MB. </small>
              </div>

              <div class="form-group">
                <div class="form-check">
                  <input v-model="form.active" type="checkbox" class="form-check-input" id="active" />
                  <label class="form-check-label" for="active"> Kích hoạt ứng dụng </label>
                </div>
              </div>
            </div>

            <!-- Owner Info -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Thông tin chủ sở hữu</h6>

              <div class="form-group">
                <label>Email chủ app <span class="text-danger">*</span></label>
                <input
                  v-model="form.owner_email"
                  type="text"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.owner_email
                  }"
                  placeholder="Nhập Email"
                  required
                />
                <div v-if="form.errors?.owner_email" class="invalid-feedback">
                  {{ form.errors.owner_email }}
                </div>
              </div>

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

              <div class="form-group">
                <label>Mật khẩu chủ app <span class="text-danger">*</span></label>
                <input
                  v-model="form.owner_password"
                  type="password"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.owner_password
                  }"
                  placeholder="Nhập mật khẩu"
                  required
                />
                <div v-if="form.errors?.owner_password" class="invalid-feedback">
                  {{ form.errors.owner_password }}
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
              <i class="fas fa-save mr-1"></i> Tạo ứng dụng
            </button>
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
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const form = useForm({
  name: '',
  mini_app_id: '',
  description: '',
  logo: null,
  active: true,
  owner_email: '',
  owner_name: '',
  owner_password: ''
})

const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.logo = file
  }
}

const submit = () => {
  form.post(route('admin.apps.store'))
}
</script>
