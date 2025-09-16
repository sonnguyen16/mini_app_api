<template>
  <AdminLayout title="Quản lý Vouchers" breadcrumb="Danh sách vouchers">
    <!-- Filters -->
    <div class="card">
      <div class="card-body">
        <form @submit.prevent="search" class="row g-3">
          <div class="col-md-3">
            <input v-model="searchForm.search" type="text" class="form-control" placeholder="Tìm theo tên voucher..." />
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
              @change="onAppChange"
              class="form-control"
            >
              <option value="">Tất cả apps</option>
              <option v-for="app in apps" :key="app.id" :value="app.id">
                {{ app.name }}
              </option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="searchForm.category_id" class="form-control">
              <option value="">Tất cả danh mục</option>
              <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search"></i>
            </button>
          </div>
          <div class="col-md-2 text-right">
            <Link :href="route('admin.vouchers.create')" class="btn btn-success">
              <i class="fas fa-plus mr-1"></i> Thêm voucher
            </Link>
          </div>
        </form>
      </div>
    </div>

    <!-- Vouchers List -->
    <div class="card">
      <div class="card-body">
        <div v-if="vouchers.data.length === 0" class="text-center text-muted py-4">Không tìm thấy voucher nào</div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Hình ảnh</th>
                <th>Tên voucher</th>
                <th>Danh mục</th>
                <th>Điểm yêu cầu</th>
                <th>Số lượng</th>
                <th>Hạn sử dụng</th>
                <th>Trạng thái</th>
                <th>App</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="voucher in vouchers.data" :key="voucher.id">
                <td>
                  <img
                    v-if="voucher.image"
                    :src="`/storage/${voucher.image}`"
                    alt="Voucher image"
                    class="img-thumbnail"
                    style="width: 50px; height: 50px; object-fit: cover"
                  />
                  <span v-else class="text-muted">Không có ảnh</span>
                </td>
                <td>
                  <strong>{{ voucher.name }}</strong>
                  <br />
                  <small v-html="voucher.description" class="text-muted"></small>
                </td>
                <td>{{ voucher.category?.name || 'N/A' }}</td>
                <td>
                  <span class="badge badge-info"> {{ voucher.required_points }} điểm </span>
                </td>
                <td>
                  <span v-if="voucher.quantity" class="badge badge-secondary">
                    {{ voucher.quantity }}
                  </span>
                  <span v-else class="text-muted">Không giới hạn</span>
                </td>
                <td>
                  <span v-if="voucher.expire_at">
                    {{ formatDate(voucher.expire_at) }}
                  </span>
                  <span v-else class="text-muted">Không hạn chế</span>
                </td>
                <td>
                  <span class="badge" :class="voucher.active ? 'badge-success' : 'badge-danger'">
                    {{ voucher.active ? 'Hoạt động' : 'Tạm dừng' }}
                  </span>
                </td>
                <td>{{ voucher.app?.name }}</td>
                <td>
                  <div class="btn-group" role="group">
                    <Link :href="route('admin.vouchers.show', voucher.id)" class="btn btn-sm btn-info">
                      <i class="fas fa-eye"></i>
                    </Link>
                    <Link :href="route('admin.vouchers.edit', voucher.id)" class="btn btn-sm btn-warning">
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button @click="deleteVoucher(voucher)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.links" class="d-flex justify-content-center">
          <nav>
            <ul class="pagination">
              <li
                v-for="link in vouchers.links"
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
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  vouchers: Object,
  apps: Array,
  categories: Array,
  filters: Object,
  currentAppId: [String, Number]
})

const searchForm = reactive({
  search: props.filters.search || '',
  active: props.filters.active || '',
  app_id: props.filters.app_id || props.currentAppId || '',
  category_id: props.filters.category_id || ''
})

const filteredCategories = computed(() => {
  if (!searchForm.app_id) return []
  return props.categories.filter((cat) => cat.app_id == searchForm.app_id)
})

const search = () => {
  router.get(route('admin.vouchers.index'), searchForm, {
    preserveState: true,
    replace: true
  })
}

const onAppChange = () => {
  searchForm.category_id = ''
}

const deleteVoucher = (voucher) => {
  if (confirm(`Bạn có chắc chắn muốn xóa voucher "${voucher.name}"?`)) {
    router.delete(route('admin.vouchers.destroy', voucher.id))
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('vi-VN')
}
</script>
