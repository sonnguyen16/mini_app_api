<template>
  <AdminLayout title="Quản lý Danh mục" breadcrumb="Danh sách danh mục">
    <!-- Filters -->
    <div class="card">
      <div class="card-body">
        <form @submit.prevent="search" class="row g-3">
          <div class="col-md-4">
            <input
              v-model="searchForm.search"
              type="text"
              class="form-control"
              placeholder="Tìm theo tên danh mục..."
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
          <div class="col-md-2 text-right">
            <Link :href="route('admin.categories.create')" class="btn btn-success">
              <i class="fas fa-plus mr-1"></i> Thêm danh mục
            </Link>
          </div>
        </form>
      </div>
    </div>

    <!-- Categories List -->
    <div class="card">
      <div class="card-body">
        <div v-if="categories.data.length === 0" class="text-center text-muted py-4">Không tìm thấy danh mục nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>App</th>
                <th>Số vouchers</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in categories.data" :key="category.id">
                <td>
                  <strong>{{ category.name }}</strong>
                </td>
                <td>
                  <span v-if="category.description" class="text-muted">
                    {{ truncateText(category.description, 50) }}
                  </span>
                  <span v-else class="text-muted">Không có mô tả</span>
                </td>
                <td>{{ category.app?.name }}</td>
                <td>
                  <span class="badge badge-info">
                    {{ category.vouchers.length || 0 }}
                    vouchers
                  </span>
                </td>
                <td>
                  <span class="badge" :class="category.active ? 'badge-success' : 'badge-danger'">
                    {{ category.active ? 'Hoạt động' : 'Tạm dừng' }}
                  </span>
                </td>
                <td>{{ formatDate(category.created_at) }}</td>
                <td>
                  <div class="btn-group" role="group">
                    <Link :href="route('admin.categories.edit', category.id)" class="btn btn-sm btn-warning">
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button @click="deleteCategory(category)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="categories.links" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination">
              <li
                v-for="link in categories.links"
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
  categories: Object,
  apps: Array,
  filters: Object,
  currentAppId: [String, Number]
})

const searchForm = reactive({
  search: props.filters.search || '',
  active: props.filters.active || '',
  app_id: props.filters.app_id || props.currentAppId || ''
})

const search = () => {
  router.get(route('admin.categories.index'), searchForm, {
    preserveState: true,
    replace: true
  })
}

const deleteCategory = (category) => {
  if (confirm(`Bạn có chắc chắn muốn xóa danh mục "${category.name}"?`)) {
    router.delete(route('admin.categories.destroy', category.id))
  }
}

const moveUp = (category) => {
  router.post(route('admin.categories.move-up', category.id))
}

const moveDown = (category) => {
  router.post(route('admin.categories.move-down', category.id))
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}

const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}
</script>
