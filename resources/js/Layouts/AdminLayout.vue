<template>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button">
            <i class="fas fa-bars"></i>
          </a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <span class="nav-link text-muted">
            <i class="far fa-user mr-1"></i>
            {{ pageProps?.props?.auth?.user?.email }}
          </span>
        </li>
        <li class="nav-item">
          <button @click="logout" class="btn btn-link nav-link text-danger" style="border: none; background: none">
            <i class="fas fa-sign-out-alt mr-1"></i>
            Đăng xuất
          </button>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <Link :href="route('admin.dashboard')" class="brand-link">
        <span class="brand-text font-weight-light">MiniApp Admin</span>
      </Link>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <li class="nav-item">
              <Link
                :href="route('admin.dashboard')"
                class="nav-link"
                :class="{
                  active: pageProps?.component === 'Dashboard/Index'
                }"
              >
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </Link>
            </li>

            <!-- Apps (chỉ admin) -->
            <li v-if="isAdmin" class="nav-item">
              <Link
                :href="route('admin.apps.index')"
                class="nav-link"
                :class="{
                  active: pageProps?.component?.startsWith('Apps/')
                }"
              >
                <i class="nav-icon fas fa-mobile-alt"></i>
                <p>Quản lý Apps</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('admin.users.index')"
                class="nav-link"
                :class="{
                  active: pageProps?.component?.startsWith('Users/')
                }"
              >
                <i class="nav-icon fas fa-users"></i>
                <p>Người dùng</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('admin.categories.index')"
                class="nav-link"
                :class="{
                  active: pageProps?.component?.startsWith('Categories/')
                }"
              >
                <i class="nav-icon fas fa-tags"></i>
                <p>Danh mục</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('admin.vouchers.index')"
                class="nav-link"
                :class="{
                  active: pageProps?.component?.startsWith('Vouchers/')
                }"
              >
                <i class="nav-icon fas fa-gift"></i>
                <p>Vouchers</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('admin.policies.index')"
                class="nav-link"
                :class="{
                  active: pageProps?.component?.startsWith('Policies/')
                }"
              >
                <i class="nav-icon fas fa-file-contract"></i>
                <p>Chính sách</p>
              </Link>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper" style="height: calc(100vh - 57px); overflow-y: auto">
      <!-- Content Header -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">{{ title }}</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
                </li>
                <li v-if="breadcrumb" class="breadcrumb-item active">
                  {{ breadcrumb }}
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <slot />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const pageProps = usePage()

defineProps({
  title: String,
  breadcrumb: String
})

const isAdmin = computed(() => {
  return pageProps?.auth?.user?.roles?.includes('admin')
})

const logout = () => {
  router.post(route('admin.logout'))
}

// Watch for flash messages and show SweetAlert2
watch(
  () => pageProps?.flash,
  (flash) => {
    if (flash?.success) {
      Swal.fire({
        icon: 'success',
        title: 'Thành công!',
        text: flash.success,
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
    }

    if (flash?.error) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi!',
        text: flash.error,
        timer: 5000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      })
    }
  },
  { deep: true, immediate: true }
)
</script>
