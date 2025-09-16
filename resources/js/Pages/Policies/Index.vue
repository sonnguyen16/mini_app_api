<template>
  <AdminLayout title="Quản lý Chính sách" breadcrumb="Danh sách chính sách ứng dụng">
    <!-- Filters -->
    <div class="card">
      <div class="card-body">
        <form @submit.prevent="search" class="row g-3">
          <div class="col-md-3">
            <select v-model="searchForm.type" class="form-control">
              <option value="">Tất cả loại chính sách</option>
              <option value="membership">Điều khoản thành viên</option>
              <option value="privacy">Chính sách bảo mật</option>
            </select>
          </div>
          <div v-if="apps.length > 0" class="col-md-3">
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
          <div class="col-md-4 text-right">
            <Link :href="route('admin.policies.create')" class="btn btn-success">
              <i class="fas fa-plus mr-1"></i> Thêm chính sách
            </Link>
          </div>
        </form>
      </div>
    </div>

    <!-- Policies List -->
    <div class="card">
      <div class="card-body">
        <div v-if="policies.data.length === 0" class="text-center text-muted py-4">Không tìm thấy chính sách nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>App</th>
                <th>Loại chính sách</th>
                <th>Nội dung</th>
                <th>Ngày tạo</th>
                <th>Cập nhật cuối</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="policy in policies.data" :key="policy.id">
                <td>{{ policy.app?.name }}</td>
                <td>
                  <span class="badge" :class="getPolicyTypeBadgeClass(policy.type)">
                    {{ getPolicyTypeText(policy.type) }}
                  </span>
                </td>
                <td>
                  <div class="content-preview" v-html="truncateHtml(policy.content, 100)"></div>
                </td>
                <td>{{ formatDate(policy.created_at) }}</td>
                <td>{{ formatDate(policy.updated_at) }}</td>
                <td>
                  <div class="btn-group" role="group">
                    <Link :href="route('admin.policies.edit', policy.id)" class="btn btn-sm btn-warning">
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button @click="deletePolicy(policy)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="policies.links" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination">
              <li
                v-for="link in policies.links"
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
import { reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  policies: Object,
  apps: Array,
  filters: Object,
  currentAppId: [String, Number]
})

const searchForm = reactive({
  type: props.filters.type || '',
  app_id: props.filters.app_id || props.currentAppId || ''
})

const search = () => {
  router.get(route('admin.policies.index'), searchForm, {
    preserveState: true,
    replace: true
  })
}

const deletePolicy = (policy) => {
  if (
    confirm(`Bạn có chắc chắn muốn xóa chính sách "${getPolicyTypeText(policy.type)}" của app "${policy.app?.name}"?`)
  ) {
    router.delete(route('admin.policies.destroy', policy.id))
  }
}

const getPolicyTypeText = (type) => {
  const types = {
    membership_policy: 'Điều khoản thành viên',
    privacy_policy: 'Chính sách bảo mật'
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

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}

const truncateHtml = (html, length) => {
  if (!html) return ''
  // Strip HTML tags for preview
  const text = html.replace(/<[^>]*>/g, '')
  return text.length > length ? text.substring(0, length) + '...' : text
}
</script>

<style scoped>
.content-preview {
  max-width: 300px;
  word-wrap: break-word;
}
</style>
