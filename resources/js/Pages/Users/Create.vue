<template>
  <AdminLayout title="Thêm người dùng" breadcrumb="Thêm người dùng mới">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-user-plus mr-2"></i>
          Thông tin người dùng mới
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <!-- User Info -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Thông tin tài khoản</h6>

              <div class="form-group">
                <label>Số điện thoại <span class="text-danger">*</span></label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.phone
                  }"
                  placeholder="Nhập số điện thoại"
                  required
                />
                <div v-if="form.errors?.phone" class="invalid-feedback">
                  {{ form.errors.phone }}
                </div>
              </div>

              <div class="form-group">
                <label>Mật khẩu <span class="text-danger">*</span></label>
                <input
                  v-model="form.password"
                  type="password"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors?.password
                  }"
                  placeholder="Nhập mật khẩu"
                  required
                />
                <div v-if="form.errors?.password" class="invalid-feedback">
                  {{ form.errors.password }}
                </div>
              </div>

              <div class="form-group">
                <label>App <span class="text-danger">*</span></label>
                <select
                  v-model="form.app_id"
                  :disabled="usePage().props.auth.user.roles.includes('app_owner')"
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
                <div class="form-check">
                  <input v-model="form.active" type="checkbox" class="form-check-input" id="active" />
                  <label class="form-check-label" for="active"> Kích hoạt tài khoản </label>
                </div>
              </div>
            </div>

            <!-- Profile Info -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Thông tin cá nhân</h6>

              <div class="form-group">
                <label>Họ tên <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.name }"
                  placeholder="Nhập họ tên"
                  required
                />
                <div v-if="form.errors?.name" class="invalid-feedback">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="form-group">
                <label>Ngày sinh</label>
                <input
                  v-model="form.birthday"
                  type="date"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors.birthday
                  }"
                />
                <div v-if="form.errors.birthday" class="invalid-feedback">
                  {{ form.errors.birthday }}
                </div>
              </div>

              <div class="form-group">
                <label>Giới tính</label>
                <select
                  v-model="form.gender"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors.gender
                  }"
                >
                  <option value="">Chọn giới tính</option>
                  <option value="male">Nam</option>
                  <option value="female">Nữ</option>
                  <option value="other">Khác</option>
                </select>
                <div v-if="form.errors.gender" class="invalid-feedback">
                  {{ form.errors.gender }}
                </div>
              </div>

              <div class="form-group">
                <label>Địa chỉ</label>
                <textarea
                  v-model="form.address"
                  class="form-control"
                  :class="{
                    'is-invalid': form.errors.address
                  }"
                  rows="3"
                  placeholder="Nhập địa chỉ (tùy chọn)"
                ></textarea>
                <div v-if="form.errors.address" class="invalid-feedback">
                  {{ form.errors.address }}
                </div>
              </div>
            </div>
          </div>

          <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
              <i class="fas fa-save mr-1"></i> Tạo tài khoản
            </button>
            <Link :href="route('admin.users.index')" class="btn btn-secondary ml-2">
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
  apps: Array
})

const form = useForm({
  phone: '',
  password: '',
  password_confirmation: '',
  app_id: props.apps[0].id,
  name: '',
  birthday: '',
  gender: '',
  address: '',
  active: true
})

const submit = () => {
  form.post(route('admin.users.store'))
}
</script>
