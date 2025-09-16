<template>
  <AdminLayout title="Dashboard" breadcrumb="Tổng quan">
    <div class="row">
      <!-- Stats Cards -->
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ stats.total_users }}</h3>
            <p>Người dùng</p>
          </div>
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ stats.total_vouchers }}</h3>
            <p>Vouchers</p>
          </div>
          <div class="icon">
            <i class="fas fa-gift"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ stats.total_transactions }}</h3>
            <p>Giao dịch</p>
          </div>
          <div class="icon">
            <i class="fas fa-exchange-alt"></i>
          </div>
        </div>
      </div>

      <div v-if="userRole === 'admin'" class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{ stats.total_apps }}</h3>
            <p>Apps</p>
          </div>
          <div class="icon">
            <i class="fas fa-mobile-alt"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Current App Info -->
    <div v-if="currentApp" class="row">
      <div class="col-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">App hiện tại</h3>
          </div>
          <div class="card-body">
            <h4>{{ currentApp.name }}</h4>
            <p>{{ currentApp.description }}</p>
            <span class="badge" :class="currentApp.active ? 'badge-success' : 'badge-danger'">
              {{ currentApp.active ? 'Hoạt động' : 'Tạm dừng' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Giao dịch gần đây</h3>
          </div>
          <div class="card-body">
            <div v-if="recentTransactions.length === 0" class="text-center text-muted">Chưa có giao dịch nào</div>
            <div v-else class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Thời gian</th>
                    <th>Người dùng</th>
                    <th>Voucher</th>
                    <th>Loại</th>
                    <th>Trạng thái</th>
                    <th v-if="userRole === 'admin'">App</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="transaction in recentTransactions" :key="transaction.id">
                    <td>{{ formatDate(transaction.created_at) }}</td>
                    <td>{{ transaction.user?.email || 'N/A' }}</td>
                    <td>{{ transaction.voucher?.name || 'N/A' }}</td>
                    <td>
                      <span class="badge" :class="transaction.type === 'redeem' ? 'badge-primary' : 'badge-info'">
                        {{ transaction.type === 'redeem' ? 'Đổi' : 'Sử dụng' }}
                      </span>
                    </td>
                    <td>
                      <span class="badge" :class="transaction.status === 'success' ? 'badge-success' : 'badge-danger'">
                        {{ transaction.status === 'success' ? 'Thành công' : 'Thất bại' }}
                      </span>
                    </td>
                    <td v-if="userRole === 'admin'">{{ transaction.app?.name || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  stats: Object,
  recentTransactions: Array,
  userRole: String,
  currentApp: Object
})

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('vi-VN')
}
</script>
