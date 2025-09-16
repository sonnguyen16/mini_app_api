<template>
  <AdminLayout title="Chỉnh sửa voucher" breadcrumb="Chỉnh sửa thông tin voucher">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-ticket-alt mr-2"></i>
          Chỉnh sửa voucher: {{ voucher.name }}
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <!-- Basic Info -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Thông tin cơ bản</h6>

              <div class="form-group">
                <label>Tên voucher <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.name }"
                  placeholder="Nhập tên voucher"
                  required
                />
                <div v-if="form.errors?.name" class="invalid-feedback">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="form-group">
                <label>App <span class="text-danger">*</span></label>
                <select
                  :disabled="usePage().props.auth.user.roles.includes('app_owner')"
                  v-model="form.app_id"
                  @change="onAppChange"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.app_id }"
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
                <label>Danh mục <span class="text-danger">*</span></label>
                <select
                  v-model="form.category_id"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.category_id }"
                  required
                >
                  <option value="">Chọn danh mục</option>
                  <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <div v-if="form.errors?.category_id" class="invalid-feedback">
                  {{ form.errors.category_id }}
                </div>
              </div>

              <div class="form-group">
                <label>Điểm yêu cầu <span class="text-danger">*</span></label>
                <input
                  v-model.number="form.required_points"
                  type="number"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.required_points }"
                  placeholder="Số điểm cần để đổi voucher"
                  min="1"
                  required
                />
                <div v-if="form.errors?.required_points" class="invalid-feedback">
                  {{ form.errors.required_points }}
                </div>
              </div>

              <div class="form-group">
                <label>Hình ảnh voucher</label>
                <div v-if="voucher.image" class="mb-2">
                  <img
                    :src="`/storage/${voucher.image}`"
                    alt="Current voucher image"
                    class="img-thumbnail"
                    style="max-width: 200px; max-height: 150px"
                  />
                  <p class="text-muted small mt-1">Hình ảnh hiện tại</p>
                </div>
                <input
                  @change="handleImageUpload"
                  type="file"
                  class="form-control-file"
                  :class="{ 'is-invalid': form.errors?.image }"
                  accept="image/*"
                />
                <div v-if="form.errors?.image" class="invalid-feedback">
                  {{ form.errors.image }}
                </div>
                <small class="form-text text-muted">
                  Chỉ chấp nhận file ảnh (JPG, PNG, GIF). Tối đa 2MB. Để trống nếu không thay đổi.
                </small>
              </div>
            </div>

            <!-- Settings -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Cài đặt voucher</h6>

              <div class="form-group">
                <label>Số lượng voucher</label>
                <input
                  v-model.number="form.quantity"
                  type="number"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.quantity }"
                  placeholder="Để trống = không giới hạn"
                  min="1"
                />
                <div v-if="form.errors?.quantity" class="invalid-feedback">
                  {{ form.errors.quantity }}
                </div>
                <small class="form-text text-muted"> Để trống nếu không giới hạn số lượng </small>
              </div>

              <div class="form-group">
                <label>Ngày hết hạn</label>
                <input
                  v-model="form.expire_at"
                  type="datetime-local"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors?.expire_at }"
                />
                <div v-if="form.errors?.expire_at" class="invalid-feedback">
                  {{ form.errors.expire_at }}
                </div>
                <small class="form-text text-muted"> Để trống nếu voucher không có hạn sử dụng </small>
              </div>

              <div class="form-group">
                <div class="form-check">
                  <input v-model="form.active" type="checkbox" class="form-check-input" id="active" />
                  <label class="form-check-label" for="active"> Kích hoạt voucher </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="form-group">
            <label>Mô tả voucher <span class="text-danger">*</span></label>
            <textarea
              v-model="form.description"
              class="form-control"
              :class="{ 'is-invalid': form.errors.description }"
            ></textarea>
            <div v-if="form.errors.description" class="invalid-feedback d-block">
              {{ form.errors.description }}
            </div>
          </div>

          <!-- Detail -->
          <div class="form-group">
            <label>Chi tiết voucher</label>
            <CKEditor
              v-model="form.detail"
              :input-class="form.errors.detail ? 'form-control is-invalid' : 'form-control'"
            />
            <div v-if="form.errors.detail" class="invalid-feedback d-block">
              {{ form.errors.detail }}
            </div>
          </div>

          <!-- Usage Condition -->
          <div class="form-group">
            <label>Điều kiện sử dụng</label>
            <CKEditor
              v-model="form.usage_condition"
              :input-class="form.errors.usage_condition ? 'form-control is-invalid' : 'form-control'"
            />
            <div v-if="form.errors.usage_condition" class="invalid-feedback d-block">
              {{ form.errors.usage_condition }}
            </div>
          </div>

          <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
              <i class="fas fa-save mr-1"></i> Cập nhật voucher
            </button>
            <Link :href="route('admin.vouchers.show', voucher.id)" class="btn btn-info ml-2">
              <i class="fas fa-eye mr-1"></i> Xem chi tiết
            </Link>
            <Link :href="route('admin.vouchers.index')" class="btn btn-secondary ml-2">
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
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CKEditor from '@/Components/CKEditor.vue'

const props = defineProps({
  voucher: Object,
  apps: Array,
  categories: Array
})

const form = useForm({
  name: props.voucher.name,
  app_id: props.voucher.app_id,
  category_id: props.voucher.category_id,
  description: props.voucher.description || '',
  detail: props.voucher.detail || '',
  usage_condition: props.voucher.usage_condition || '',
  required_points: props.voucher.required_points,
  quantity: props.voucher.quantity || '',
  expire_at: props.voucher.expire_at ? formatDateTimeLocal(props.voucher.expire_at) : '',
  image: null,
  active: props.voucher.active
})

const filteredCategories = computed(() => {
  if (!form.app_id) return []
  return props.categories.filter((cat) => cat.app_id == form.app_id)
})

const onAppChange = () => {
  form.category_id = ''
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.image = file
  }
}

const submit = () => {
  form.post(route('admin.vouchers.update', props.voucher.id), {
    _method: 'put'
  })
}

function formatDateTimeLocal(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().slice(0, 16)
}
</script>
