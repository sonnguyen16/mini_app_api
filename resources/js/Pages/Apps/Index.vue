<template>
  <AdminLayout title="Quản lý Apps" breadcrumb="Danh sách ứng dụng">
    <!-- Filters -->
    <div class="card">
      <div class="card-body">
        <form @submit.prevent="search" class="row g-3">
          <div class="col-md-4">
            <input v-model="searchForm.search" type="text" class="form-control" placeholder="Tìm theo tên app..." />
          </div>
          <div class="col-md-2">
            <select v-model="searchForm.active" class="form-control">
              <option value="">Tất cả trạng thái</option>
              <option value="1">Hoạt động</option>
              <option value="0">Tạm dừng</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Tìm kiếm</button>
          </div>
          <div class="col-md-4 text-right">
            <Link :href="route('admin.apps.create')" class="btn btn-success" v-if="canCreateApp">
              <i class="fas fa-plus mr-1"></i> Thêm app mới
            </Link>
          </div>
        </form>
      </div>
    </div>

    <!-- Apps List -->
    <div class="card">
      <div class="card-body">
        <div v-if="apps.data.length === 0" class="text-center text-muted py-4">Không tìm thấy app nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Logo</th>
                <th>Tên App</th>
                <th>Mini App ID</th>
                <th>Chủ sở hữu</th>
                <th>Người dùng</th>
                <th>Vouchers</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="app in apps.data" :key="app.id">
                <td>
                  <img
                    v-if="app.logo"
                    :src="`/storage/${app.logo}`"
                    alt="App logo"
                    class="img-thumbnail"
                    style="width: 40px; height: 40px; object-fit: cover"
                  />
                  <span v-else class="text-muted">
                    <i class="fas fa-mobile-alt fa-2x"></i>
                  </span>
                </td>
                <td>
                  <strong>{{ app.name }}</strong>
                  <br />
                  <small class="text-muted">{{ app.description }}</small>
                </td>
                <td>
                  <code>{{ app.mini_app_id }}</code>
                </td>
                <td>
                  <span v-if="app.owner">
                    {{ app.owner_email }}
                    <br />
                    <small class="text-muted">{{ app.owner_name }}</small>
                  </span>
                  <span v-else class="text-muted">Chưa có</span>
                </td>
                <td>
                  <span class="badge badge-info">
                    {{ app?.user_profiles?.length || 0 }}
                    người dùng
                  </span>
                </td>
                <td>
                  <span class="badge badge-secondary">
                    {{ app?.vouchers?.length || 0 }}
                    vouchers
                  </span>
                </td>
                <td>
                  <span class="badge" :class="app.active ? 'badge-success' : 'badge-danger'">
                    {{ app.active ? 'Hoạt động' : 'Tạm dừng' }}
                  </span>
                </td>
                <td>{{ formatDate(app.created_at) }}</td>
                <td>
                  <div class="btn-group" role="group">
                    <Link :href="route('admin.apps.show', app.id)" class="btn btn-sm btn-info">
                      <i class="fas fa-eye"></i>
                    </Link>
                    <Link v-if="canEdit(app)" :href="route('admin.apps.edit', app.id)" class="btn btn-sm btn-warning">
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button v-if="canDelete(app)" @click="deleteApp(app)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="apps.links" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination">
              <li
                v-for="link in apps.links"
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
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  apps: Object,
  filters: Object,
  auth: Object
})

const searchForm = reactive({
  search: props.filters.search || '',
  active: props.filters.active || ''
})

const canCreateApp = computed(() => {
  return props.auth?.user?.roles?.includes('admin')
})

const canEdit = (app) => {
  const user = props.auth?.user
  return user?.roles?.includes('admin') || (user?.roles?.includes('app_owner') && user?.owned_app_id === app.id)
}

const canDelete = (app) => {
  return props.auth?.user?.roles?.includes('admin')
}

const canResetPassword = (app) => {
  return props.auth?.user?.roles?.includes('admin')
}

const search = () => {
  router.get(route('admin.apps.index'), searchForm, {
    preserveState: true,
    replace: true
  })
}

const deleteApp = (app) => {
  if (confirm(`Bạn có chắc chắn muốn xóa app "${app.name}"? Tất cả dữ liệu liên quan sẽ bị xóa!`)) {
    router.delete(route('admin.apps.destroy', app.id))
  }
}

const resetOwnerPassword = (app) => {
  if (confirm(`Bạn có chắc chắn muốn reset mật khẩu cho chủ app "${app.name}"?`)) {
    router.post(
      route('admin.apps.reset-owner-password', app.id),
      {},
      {
        onSuccess: (page) => {
          if (page.props.flash?.success) {
            alert('Đã reset mật khẩu thành công!')
          }
        }
      }
    )
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}
</script>
