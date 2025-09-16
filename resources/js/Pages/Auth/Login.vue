<template>
  <div class="login-page">
    <div class="login-box">
      <div class="login-logo"><b>MiniApp</b> Admin</div>

      <div class="card">
        <div class="card-body login-card-body">
          <p class="login-box-msg">Đăng nhập để bắt đầu phiên làm việc</p>

          <form @submit.prevent="submit">
            <div class="input-group mb-3">
              <input
                v-model="form.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': form.errors.email }"
                placeholder="Email"
                required
              />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
              <div v-if="form.errors.email" class="invalid-feedback">
                {{ form.errors.email }}
              </div>
            </div>

            <div class="input-group mb-3">
              <input
                v-model="form.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': form.errors.password }"
                placeholder="Mật khẩu"
                required
              />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
              <div v-if="form.errors.password" class="invalid-feedback">
                {{ form.errors.password }}
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2"></span>
                  Đăng nhập
                </button>
              </div>
            </div>
          </form>

          <div class="mt-3">
            <p class="text-muted text-center">
              <small>Tài khoản demo:</small><br />
              <small><strong>Admin:</strong> admin@miniapp.com / admin123</small><br />
              <small><strong>Coffee Owner:</strong> coffee@owner.com / coffee123</small><br />
              <small><strong>Beauty Owner:</strong> beauty@owner.com / beauty123</small>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  email: '',
  password: ''
})

const submit = () => {
  form.post(route('admin.login'), {
    onFinish: () => form.reset('password')
  })
}
</script>

<style scoped>
.login-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}
</style>
