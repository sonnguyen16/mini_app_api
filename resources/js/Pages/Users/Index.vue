<template>
  <AdminLayout title="Quản lý người dùng" breadcrumb="Danh sách người dùng">
    <!-- Filters -->
    <div class="card">
      <div class="card-body">
        <form @submit.prevent="search" class="row g-3">
          <div class="col-md-3">
            <input
              v-model="searchForm.search"
              type="text"
              class="form-control"
              placeholder="Tìm theo tên hoặc SĐT..."
            />
          </div>
          <div class="col-md-2">
            <select v-model="searchForm.active" class="form-control">
              <option value="">Tất cả trạng thái</option>
              <option value="1">Hoạt động</option>
              <option value="0">Tạm dừng</option>
            </select>
          </div>
          <div class="col-md-2">
            <select
              :disabled="usePage().props.auth.user.roles.includes('app_owner')"
              v-model="searchForm.app_id"
              class="form-control"
            >
              <option value="">Tất cả apps</option>
              <option v-for="app in apps" :key="app.id" :value="app.id">
                {{ app.name }}
              </option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Tìm kiếm</button>
          </div>
          <div class="col-md-3 text-right">
            <Link :href="route('admin.users.create')" class="btn btn-success">
              <i class="fas fa-plus mr-1"></i> Thêm người dùng
            </Link>
          </div>
        </form>
      </div>
    </div>

    <!-- QR Scanner & Add Points -->
    <div class="row">
      <div class="">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-coins mr-2"></i>
              Cộng/Trừ điểm
            </h5>
          </div>
          <div class="card-body">
            <QRScanner @scanned="onQRScanned" />
            <form @submit.prevent="addPoints">
              <div class="row mt-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Số điện thoại</label>
                    <input
                      v-model="pointsForm.phone"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': pointsForm.errors?.phone
                      }"
                      placeholder="Nhập số điện thoại"
                      required
                    />
                    <div v-if="pointsForm.errors?.phone" class="invalid-feedback">
                      {{ pointsForm.errors.phone }}
                    </div>
                  </div>
                  <div class="form-group">
                    <label>App</label>
                    <select
                      v-model="pointsForm.app_id"
                      class="form-control"
                      :class="{
                        'is-invalid': pointsForm.errors?.app_id
                      }"
                      required
                    >
                      <option value="">Chọn app</option>
                      <option v-for="app in apps" :key="app.id" :value="app.id">
                        {{ app.name }}
                      </option>
                    </select>
                    <div v-if="pointsForm.errors?.app_id" class="invalid-feedback">
                      {{ pointsForm.errors.app_id }}
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Số điểm (+ cộng, - trừ)</label>
                    <input
                      v-model.number="pointsForm.amount"
                      type="number"
                      class="form-control"
                      :class="{
                        'is-invalid': pointsForm.errors?.amount
                      }"
                      placeholder="Ví dụ: 100 hoặc -50"
                      required
                    />
                    <div v-if="pointsForm.errors?.amount" class="invalid-feedback">
                      {{ pointsForm.errors.amount }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Lý do</label>
                    <input
                      v-model="pointsForm.reason"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': pointsForm.errors?.reason
                      }"
                      placeholder="Lý do cộng/trừ điểm"
                    />
                    <div v-if="pointsForm.errors?.reason" class="invalid-feedback">
                      {{ pointsForm.errors.reason }}
                    </div>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-primary" :disabled="pointsForm.processing">
                <span v-if="pointsForm.processing" class="spinner-border spinner-border-sm mr-2"></span>
                <i class="fas fa-coins mr-1"></i> Cập nhật điểm
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Users List -->
    <div class="card">
      <div class="card-body">
        <div v-if="profiles.data.length === 0" class="text-center text-muted py-4">Không tìm thấy người dùng nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tên</th>
                <th>SĐT</th>
                <th>Ngày sinh</th>
                <th>Giới tính</th>
                <th>Điểm tích lũy</th>
                <th>Trạng thái</th>
                <th>App</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="profile in profiles.data" :key="profile.id">
                <td>{{ profile.name }}</td>
                <td>{{ profile.user?.phone || 'N/A' }}</td>
                <td>{{ formatDate(profile.birthday) }}</td>
                <td>{{ formatGender(profile.gender) }}</td>
                <td>
                  <span class="badge badge-primary"> {{ profile.points_total }} điểm </span>
                </td>
                <td>
                  <span class="badge" :class="profile.active ? 'badge-success' : 'badge-danger'">
                    {{ profile.active ? 'Hoạt động' : 'Tạm dừng' }}
                  </span>
                </td>
                <td>{{ profile.app?.name }}</td>
                <td class="btn-group">
                  <Link :href="route('admin.users.show', profile.id)" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i>
                  </Link>
                  <Link :href="route('admin.users.edit', profile.id)" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="profiles.links" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination">
              <li
                v-for="link in profiles.links"
                :key="link.label"
                class="page-item"
                :class="{
                  active: link.active,
                  disabled: !link.url
                }"
              >
                <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
                <span v-else class="page-link" v-html="link.label"></span>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, useForm, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import QRScanner from '@/Components/QRScanner.vue'

const props = defineProps({
  profiles: Object,
  apps: Array,
  filters: Object,
  currentAppId: [String, Number]
})

const searchForm = reactive({
  search: props.filters.search || '',
  active: props.filters.active || '',
  app_id: props.filters.app_id || props.currentAppId || ''
})

const pointsForm = useForm({
  phone: '',
  app_id: props.currentAppId || '',
  amount: '',
  reason: ''
})

const search = () => {
  router.get(route('admin.users.index'), searchForm, {
    preserveState: true,
    replace: true
  })
}

const addPoints = () => {
  pointsForm.post(route('admin.users.add-points'), {
    onSuccess: () => {
      pointsForm.reset()
    }
  })
}

const onQRScanned = (qrData) => {
  // convert qrData to json
  const qrDataJson = JSON.parse(qrData)
  // Giả sử QR code chứa số điện thoại
  pointsForm.phone = qrDataJson.phone
  pointsForm.app_id = qrDataJson.app_id
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}

const formatGender = (gender) => {
  const genders = {
    male: 'Nam',
    female: 'Nữ',
    other: 'Khác'
  }
  return genders[gender] || 'N/A'
}
</script>
