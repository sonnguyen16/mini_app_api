<template>
  <AdminLayout title="Chi tiết người dùng" breadcrumb="Thông tin chi tiết người dùng">
    <div class="row">
      <!-- User Profile -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-user mr-2"></i>
              Thông tin cá nhân
            </h5>
            <div class="card-tools">
              <Link :href="route('admin.users.edit', profile.id)" class="btn btn-sm btn-warning">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>Họ tên:</strong></td>
                    <td>{{ profile.name }}</td>
                  </tr>
                  <tr>
                    <td style="white-space: nowrap"><strong>Số điện thoại:</strong></td>
                    <td>
                      {{ profile.user?.phone || 'N/A' }}
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Ngày sinh:</strong></td>
                    <td>
                      {{ formatDate(profile.birthday) }}
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Địa chỉ:</strong></td>
                    <td>
                      {{ profile.address || 'N/A' }}
                    </td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>Giới tính:</strong></td>
                    <td>
                      {{ formatGender(profile.gender) }}
                    </td>
                  </tr>
                  <tr>
                    <td><strong>App:</strong></td>
                    <td>{{ profile.app?.name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Trạng thái:</strong></td>
                    <td>
                      <span class="badge" :class="profile.active ? 'bg-success' : 'bg-danger'">
                        {{ profile.active ? 'Hoạt động' : 'Tạm dừng' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Ngày tham gia:</strong></td>
                    <td>
                      {{ formatDate(profile.created_at) }}
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Points Summary -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-coins mr-2"></i>
              Điểm tích lũy
            </h5>
          </div>
          <div class="card-body text-center">
            <h2 class="text-primary">{{ profile.points_total }}</h2>
            <p class="text-muted">điểm hiện có</p>

            <div class="mt-3">
              <small class="text-muted">
                Tổng điểm đã tích:
                {{ pointsStats?.total_earned || 0 }}<br />
                Tổng điểm đã dùng:
                {{ pointsStats?.total_spent || 0 }}
              </small>
            </div>
          </div>
        </div>

        <!-- Voucher Stats -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-ticket-alt mr-2"></i>
              Voucher
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <h4 class="text-info">
                  {{ voucherStats?.redeemed || 0 }}
                </h4>
                <small class="text-muted">Đã đổi</small>
              </div>
              <div class="col-6">
                <h4 class="text-success">
                  {{ voucherStats?.used || 0 }}
                </h4>
                <small class="text-muted">Đã sử dụng</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Points History -->
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-history mr-2"></i>
          Lịch sử điểm
        </h5>
      </div>
      <div class="card-body">
        <div v-if="pointsHistory?.length === 0" class="text-center text-muted py-4">Chưa có lịch sử điểm nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Ngày</th>
                <th>Loại</th>
                <th>Điểm</th>
                <th>Mô tả</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in pointsHistory" :key="entry.id">
                <td>{{ formatDateTime(entry.created_at) }}</td>
                <td>
                  <span class="badge" :class="entry.amount > 0 ? 'badge-success' : 'badge-danger'">
                    {{ entry.amount > 0 ? 'Cộng điểm' : 'Trừ điểm' }}
                  </span>
                </td>
                <td>
                  <span :class="entry.amount > 0 ? 'text-success' : 'text-danger'">
                    {{ entry.amount > 0 ? '+' : '' }}{{ entry.amount }}
                  </span>
                </td>
                <td>{{ entry.reason }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Voucher Wallet -->
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-wallet mr-2"></i>
          Ví voucher
        </h5>
      </div>
      <div class="card-body">
        <div v-if="voucherWallet?.length === 0" class="text-center text-muted py-4">Chưa có voucher nào trong ví</div>
        <div v-else class="row">
          <div v-for="wallet in voucherWallet" :key="wallet.id" class="col-md-6 mb-3">
            <div class="card border">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="card-title">
                      {{ wallet.voucher?.name }}
                    </h6>
                    <p class="card-text text-muted small">
                      {{ wallet.voucher?.description }}
                    </p>
                  </div>
                  <span class="badge" :class="getStatusBadgeClass(wallet.status)">
                    {{ getStatusText(wallet.status) }}
                  </span>
                </div>
                <div class="mt-2">
                  <small class="text-muted">
                    Đổi lúc:
                    {{ formatDateTime(wallet.redeemed_at) }}<br />
                    <span v-if="wallet.used_at">
                      Sử dụng lúc:
                      {{ formatDateTime(wallet.used_at) }}
                    </span>
                    <span v-else-if="wallet.voucher?.expire_at">
                      Hết hạn:
                      {{ formatDate(wallet.voucher.expire_at) }}
                    </span>
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  profile: Object,
  pointsHistory: Array,
  voucherWallet: Array,
  pointsStats: Object,
  voucherStats: Object
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('vi-VN')
}

const formatGender = (gender) => {
  const genders = {
    male: 'Nam',
    female: 'Nữ',
    other: 'Khác'
  }
  return genders[gender] || 'Chưa cập nhật'
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
