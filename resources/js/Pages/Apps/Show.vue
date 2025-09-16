<template>
  <AdminLayout title="Chi tiết App" breadcrumb="Thông tin chi tiết ứng dụng">
    <div class="row">
      <!-- App Info -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-mobile-alt mr-2"></i>
              {{ app.name }}
            </h5>
            <div class="card-tools">
              <Link v-if="canEdit" :href="route('admin.apps.edit', app.id)" class="btn btn-sm btn-warning">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div v-if="app.logo" class="mb-3 ms-3">
              <img
                :src="`/storage/${app.logo}`"
                alt="App logo"
                class="img-fluid rounded"
                style="max-width: 150px; max-height: 150px; object-fit: cover"
              />
            </div>
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless mb-1">
                  <tr>
                    <td><strong>Mini App ID:</strong></td>
                    <td>
                      <code>{{ app.mini_app_id }}</code>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Trạng thái:</strong></td>
                    <td>
                      <span class="badge" :class="app.active ? 'bg-success text-white' : 'bg-warning text-black'">
                        {{ app.active ? 'Hoạt động' : 'Tạm dừng' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Ngày tạo:</strong></td>
                    <td>
                      {{ formatDateTime(app.created_at) }}
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Cập nhật cuối:</strong></td>
                    <td>
                      {{ formatDateTime(app.updated_at) }}
                    </td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless mb-2">
                  <tr>
                    <td><strong>Chủ sở hữu:</strong></td>
                    <td>{{ app.owner_name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ app.owner_email }}</td>
                  </tr>
                  <tr>
                    <td><strong>Ngày tạo:</strong></td>
                    <td>
                      {{ formatDate(app.created_at) }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <strong>Mật khẩu:</strong>
                    </td>
                    <td class="pb-2">
                      <button
                        v-if="canResetPassword"
                        @click="resetOwnerPassword"
                        class="btn btn-sm btn-warning"
                        style="border-bottom: 1px solid #ffc107"
                        :disabled="processing"
                      >
                        <span v-if="processing" class="spinner-border spinner-border-sm mr-1"></span>
                        <i class="fas fa-key mr-1"></i>
                        Reset mật khẩu
                      </button>
                    </td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- Description -->
            <div v-if="app.description" style="margin-left: 20px">
              <h6 class="mb-2"><strong>Mô tả:</strong></h6>
              <div class="border p-3 rounded bg-light">{{ app.description }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-chart-bar mr-2"></i>
              Thống kê tổng quan
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 mb-3">
                <h3 class="text-primary">
                  {{ app?.user_profiles?.length || 0 }}
                </h3>
                <small class="text-muted">Người dùng</small>
              </div>
              <div class="col-6 mb-3">
                <h3 class="text-info">
                  {{ app?.categories?.length || 0 }}
                </h3>
                <small class="text-muted">Danh mục</small>
              </div>
              <div class="col-6 mb-3">
                <h3 class="text-success">
                  {{ app?.vouchers?.length || 0 }}
                </h3>
                <small class="text-muted">Vouchers</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-tools mr-2"></i>
              Thao tác nhanh
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <button
                @click="toggleStatus"
                class="btn"
                :class="app.active ? 'btn-warning' : 'btn-success'"
                :disabled="processing2"
              >
                <span v-if="processing2" class="spinner-border spinner-border-sm mr-2"></span>
                <i :class="app.active ? 'fas fa-pause' : 'fas fa-play'" class="mr-1"></i>
                {{ app.active ? 'Tạm dừng' : 'Kích hoạt' }}
              </button>

              <Link v-if="canEdit" :href="route('admin.apps.edit', app.id)" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>

              <Link
                :href="
                  route('admin.users.index', {
                    app_id: app.id
                  })
                "
                class="btn btn-info"
              >
                <i class="fas fa-users mr-1"></i> Quản lý người dùng
              </Link>

              <Link
                :href="
                  route('admin.vouchers.index', {
                    app_id: app.id
                  })
                "
                class="btn btn-secondary"
              >
                <i class="fas fa-ticket-alt mr-1"></i> Quản lý vouchers
              </Link>

              <button v-if="canDelete" @click="deleteApp" class="btn btn-danger">
                <i class="fas fa-trash mr-1"></i> Xóa ứng dụng
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-history mr-2"></i>
          Hoạt động gần đây
        </h5>
      </div>
      <div class="card-body">
        <div v-if="recentActivities?.length === 0" class="text-center text-muted py-4">Chưa có hoạt động nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Thời gian</th>
                <th>Loại</th>
                <th>Người dùng</th>
                <th>Mô tả</th>
                <th>Điểm</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="activity in recentActivities" :key="activity.id">
                <td>
                  {{ formatDateTime(activity.created_at) }}
                </td>
                <td>
                  <span class="badge" :class="getActivityBadgeClass(activity.type)">
                    {{ getActivityTypeText(activity.type) }}
                  </span>
                </td>
                <td>
                  <span v-if="activity.profile">
                    {{ activity.profile.name }}<br />
                    <small class="text-muted">{{ activity.profile.user?.phone }}</small>
                  </span>
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>{{ activity.description }}</td>
                <td>
                  <span v-if="activity.amount" :class="activity.amount > 0 ? 'text-success' : 'text-danger'">
                    {{ activity.amount > 0 ? '+' : '' }}{{ activity.amount }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  app: Object,
  stats: Object,
  recentActivities: Array,
  auth: Object
})

const processing = ref(false)
const processing2 = ref(false)

const canEdit = computed(() => {
  const user = props.auth.user
  return user.roles?.includes('admin') || (user.roles?.includes('app_owner') && user.owned_app_id === props.app.id)
})

const canDelete = computed(() => {
  return props.auth.user.roles?.includes('admin')
})

const canResetPassword = computed(() => {
  return props.auth.user.roles?.includes('admin')
})

const toggleStatus = () => {
  if (processing2.value) return

  processing2.value = true
  router.post(
    route('admin.apps.toggle-status', props.app.id),
    {},
    {
      onFinish: () => {
        processing2.value = false
      }
    }
  )
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

const deleteApp = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa app "${props.app.name}"? Tất cả dữ liệu liên quan sẽ bị xóa!`)) {
    router.delete(route('admin.apps.destroy', props.app.id))
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('vi-VN')
}

const getActivityTypeText = (type) => {
  const types = {
    points_earned: 'Tích điểm',
    points_spent: 'Tiêu điểm',
    voucher_redeemed: 'Đổi voucher',
    voucher_used: 'Dùng voucher',
    user_registered: 'Đăng ký'
  }
  return types[type] || type
}

const getActivityBadgeClass = (type) => {
  const classes = {
    points_earned: 'badge-success',
    points_spent: 'badge-warning',
    voucher_redeemed: 'badge-info',
    voucher_used: 'badge-primary',
    user_registered: 'badge-secondary'
  }
  return classes[type] || 'badge-light'
}
</script>
