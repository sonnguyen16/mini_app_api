<template>
  <AdminLayout title="Chi tiết voucher" breadcrumb="Thông tin chi tiết voucher">
    <div class="row">
      <!-- Voucher Info -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-ticket-alt mr-2"></i>
              {{ voucher.name }}
            </h5>
            <div class="card-tools">
              <Link :href="route('admin.vouchers.edit', voucher.id)" class="btn btn-sm btn-warning">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div v-if="voucher.image" class="mb-3">
              <img
                :src="`/storage/${voucher.image}`"
                alt="Voucher image"
                class="img-fluid rounded"
                style="max-width: 100%; max-height: 200px; object-fit: cover"
              />
            </div>
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>App:</strong></td>
                    <td>{{ voucher.app?.name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Danh mục:</strong></td>
                    <td>{{ voucher.category?.name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Điểm yêu cầu:</strong></td>
                    <td>
                      <span class="badge bg-info">
                        {{ voucher.required_points }}
                        điểm
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Số lượng:</strong></td>
                    <td>
                      <span v-if="voucher.quantity" class="badge bg-secondary">
                        {{ voucher.quantity }}
                      </span>
                      <span v-else class="text-muted">Không giới hạn</span>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>Ngày hết hạn:</strong></td>
                    <td>
                      <span v-if="voucher.expire_at">
                        {{ formatDateTime(voucher.expire_at) }}
                      </span>
                      <span v-else class="text-muted">Không hạn chế</span>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Trạng thái:</strong></td>
                    <td>
                      <span class="badge" :class="voucher.active ? 'bg-success' : 'bg-warning'">
                        {{ voucher.active ? 'Hoạt động' : 'Tạm dừng' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Ngày tạo:</strong></td>
                    <td>
                      {{ formatDateTime(voucher.created_at) }}
                    </td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- Description -->
            <div class="mt-4">
              <h6><strong>Mô tả:</strong></h6>
              <div class="border p-3 rounded bg-light" v-html="voucher.description"></div>
            </div>

            <!-- Detail -->
            <div v-if="voucher.detail" class="mt-4">
              <h6><strong>Chi tiết voucher:</strong></h6>
              <div class="border p-3 rounded bg-light" v-html="voucher.detail"></div>
            </div>

            <!-- Usage Condition -->
            <div v-if="voucher.usage_condition" class="mt-4">
              <h6><strong>Điều kiện sử dụng:</strong></h6>
              <div class="border p-3 rounded bg-light" v-html="voucher.usage_condition"></div>
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
              Thống kê
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 mb-3">
                <h3 class="text-primary">
                  {{ stats.redeemed || 0 }}
                </h3>
                <small class="text-muted">Lượt đổi</small>
              </div>
              <div class="col-6 mb-3">
                <h3 class="text-success">
                  {{ stats.used || 0 }}
                </h3>
                <small class="text-muted">Đã sử dụng</small>
              </div>
              <div class="col-6 mb-3">
                <h3 class="text-warning">
                  {{ stats.available || 0 }}
                </h3>
                <small class="text-muted">Chưa sử dụng</small>
              </div>
              <div v-if="voucher.quantity" class="col-6">
                <h3 class="text-info">
                  {{ stats.remaining || 0 }}
                </h3>
                <small class="text-muted">Còn lại</small>
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
                :class="voucher.active ? 'btn-warning' : 'btn-success'"
                :disabled="processing"
              >
                <span v-if="processing" class="spinner-border spinner-border-sm mr-2"></span>
                <i :class="voucher.active ? 'fas fa-pause' : 'fas fa-play'" class="mr-1"></i>
                {{ voucher.active ? 'Tạm dừng' : 'Kích hoạt' }}
              </button>

              <Link :href="route('admin.vouchers.edit', voucher.id)" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>

              <button @click="deleteVoucher" class="btn btn-danger">
                <i class="fas fa-trash mr-1"></i> Xóa voucher
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Redemptions -->
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-history mr-2"></i>
          Lịch sử đổi voucher gần đây
        </h5>
      </div>
      <div class="card-body">
        <div v-if="recentRedemptions.length === 0" class="text-center text-muted py-4">Chưa có ai đổi voucher này</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>SĐT</th>
                <th>Ngày đổi</th>
                <th>Trạng thái</th>
                <th>Ngày sử dụng</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="redemption in recentRedemptions" :key="redemption.id">
                <td>{{ redemption.user?.phone }}</td>
                <td>
                  {{ formatDateTime(redemption.redeemed_at) }}
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(redemption.status)">
                    {{ getStatusText(redemption.status) }}
                  </span>
                </td>
                <td>
                  <span v-if="redemption.used_at">
                    {{ formatDateTime(redemption.used_at) }}
                  </span>
                  <span v-else class="text-muted">Chưa sử dụng</span>
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  voucher: Object,
  stats: Object,
  recentRedemptions: Array
})

const processing = ref(false)

const toggleStatus = () => {
  if (processing.value) return

  processing.value = true
  router.patch(
    route('admin.vouchers.toggle-status', props.voucher.id),
    {},
    {
      onFinish: () => {
        processing.value = false
      }
    }
  )
}

const deleteVoucher = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa voucher "${props.voucher.name}"?`)) {
    router.delete(route('admin.vouchers.destroy', props.voucher.id))
  }
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('vi-VN')
}

const getStatusText = (status) => {
  const statuses = {
    redeemed: 'Chưa sử dụng',
    used: 'Đã sử dụng',
    expired: 'Hết hạn'
  }
  return statuses[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    redeemed: 'badge-warning',
    used: 'badge-success',
    expired: 'badge-danger'
  }
  return classes[status] || 'badge-secondary'
}
</script>
