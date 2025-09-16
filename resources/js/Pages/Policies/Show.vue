<template>
  <AdminLayout title="Chi tiết chính sách" breadcrumb="Thông tin chi tiết chính sách">
    <div class="row">
      <!-- Policy Info -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-file-contract mr-2"></i>
              {{ getPolicyTypeText(policy.type) }} - {{ policy.app?.name }}
            </h5>
            <div class="card-tools">
              <Link :href="route('admin.policies.edit', policy.id)" class="btn btn-sm btn-warning">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>App:</strong></td>
                    <td>{{ policy.app?.name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Loại chính sách:</strong></td>
                    <td>
                      <span class="badge" :class="getPolicyTypeBadgeClass(policy.type)">
                        {{ getPolicyTypeText(policy.type) }}
                      </span>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td><strong>Ngày tạo:</strong></td>
                    <td>{{ formatDateTime(policy.created_at) }}</td>
                  </tr>
                  <tr>
                    <td><strong>Cập nhật cuối:</strong></td>
                    <td>{{ formatDateTime(policy.updated_at) }}</td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- Content -->
            <div class="mt-4">
              <h6><strong>Nội dung chính sách:</strong></h6>
              <div class="policy-content border p-4 rounded bg-light" v-html="policy.content"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-tools mr-2"></i>
              Thao tác nhanh
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <Link :href="route('admin.policies.edit', policy.id)" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
              </Link>

              <button @click="copyContent" class="btn btn-info">
                <i class="fas fa-copy mr-1"></i> Sao chép nội dung
              </button>

              <button @click="printPolicy" class="btn btn-secondary">
                <i class="fas fa-print mr-1"></i> In chính sách
              </button>

              <button @click="deletePolicy" class="btn btn-danger">
                <i class="fas fa-trash mr-1"></i> Xóa chính sách
              </button>
            </div>
          </div>
        </div>

        <!-- Policy Info -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-info-circle mr-2"></i>
              Thông tin bổ sung
            </h5>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <i class="fas fa-lightbulb mr-2"></i>
              <strong>Mẹo:</strong> Chính sách này sẽ được hiển thị trong ứng dụng mobile khi người dùng đăng ký hoặc
              truy cập phần cài đặt.
            </div>

            <div class="mt-3">
              <h6>Các chính sách khác của app này:</h6>
              <div v-if="otherPolicies.length === 0" class="text-muted">Không có chính sách khác</div>
              <div v-else>
                <Link
                  v-for="otherPolicy in otherPolicies"
                  :key="otherPolicy.id"
                  :href="route('admin.policies.show', otherPolicy.id)"
                  class="btn btn-sm btn-outline-primary mr-2 mb-2"
                >
                  {{ getPolicyTypeText(otherPolicy.type) }}
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- API Information -->
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-code mr-2"></i>
          Thông tin API
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Ứng dụng mobile có thể truy cập chính sách này qua API endpoint:</p>
        <div class="bg-dark text-light p-3 rounded">
          <code>GET /api/v1/policies?type={{ policy.type }}</code>
        </div>
        <small class="text-muted mt-2 d-block">
          Yêu cầu header: <code>X-App-Id: {{ policy.app_id }}</code>
        </small>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  policy: Object,
  otherPolicies: Array
})

const getPolicyTypeText = (type) => {
  const types = {
    membership: 'Điều khoản thành viên',
    privacy: 'Chính sách bảo mật'
  }
  return types[type] || type
}

const getPolicyTypeBadgeClass = (type) => {
  const classes = {
    membership: 'badge-primary',
    privacy: 'badge-info'
  }
  return classes[type] || 'badge-secondary'
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('vi-VN')
}

const copyContent = () => {
  // Strip HTML tags for plain text copy
  const textContent = props.policy.content.replace(/<[^>]*>/g, '')
  navigator.clipboard
    .writeText(textContent)
    .then(() => {
      alert('Đã sao chép nội dung vào clipboard!')
    })
    .catch(() => {
      alert('Không thể sao chép. Vui lòng thử lại.')
    })
}

const printPolicy = () => {
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>${getPolicyTypeText(props.policy.type)} - ${props.policy.app?.name}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
          .content { line-height: 1.6; }
        </style>
      </head>
      <body>
        <h1>${getPolicyTypeText(props.policy.type)}</h1>
        <p><strong>Ứng dụng:</strong> ${props.policy.app?.name}</p>
        <p><strong>Ngày cập nhật:</strong> ${formatDateTime(props.policy.updated_at)}</p>
        <hr>
        <div class="content">${props.policy.content}</div>
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.print()
}

const deletePolicy = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa chính sách "${getPolicyTypeText(props.policy.type)}"?`)) {
    router.delete(route('admin.policies.destroy', props.policy.id))
  }
}
</script>

<style scoped>
.policy-content {
  max-height: 500px;
  overflow-y: auto;
  line-height: 1.6;
}

.policy-content h1,
.policy-content h2,
.policy-content h3,
.policy-content h4 {
  color: #495057;
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
}

.policy-content p {
  margin-bottom: 1rem;
}

.policy-content ul,
.policy-content ol {
  padding-left: 2rem;
  margin-bottom: 1rem;
}
</style>
