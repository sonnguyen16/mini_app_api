<template>
  <div class="qr-scanner">
    <div v-if="!isScanning">
      <button @click="startScanning" class="btn btn-primary">
        <i class="fas fa-camera mr-2"></i>
        Quét QR
      </button>
    </div>

    <div v-if="isScanning" class="scanner-container">
      <qrcode-stream @detect="onDetect" @error="onError" @camera-on="onCameraOn" @camera-off="onCameraOff" />

      <div class="scanner-controls mt-3 text-center">
        <button @click="stopScanning" class="btn btn-secondary mr-2">
          <i class="fas fa-stop mr-2"></i>
          Dừng quét
        </button>
        <button @click="switchCamera" class="btn btn-info" v-if="canSwitchCamera">
          <i class="fas fa-sync-alt mr-2"></i>
          Đổi camera
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-danger mt-3">
      <i class="fas fa-exclamation-triangle mr-2"></i>
      {{ error }}
    </div>

    <div v-if="result" class="alert alert-success mt-3">
      <i class="fas fa-check mr-2"></i>
      Đã quét thành công
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { QrcodeStream } from 'vue-qrcode-reader'

const emit = defineEmits(['scanned', 'error'])

const isScanning = ref(false)
const error = ref('')
const result = ref('')
const canSwitchCamera = ref(false)
const currentCamera = ref('auto')

const startScanning = () => {
  isScanning.value = true
  error.value = ''
  result.value = ''
}

const stopScanning = () => {
  isScanning.value = false
}

const onDetect = (detectedCodes) => {
  if (detectedCodes.length > 0) {
    const qrData = detectedCodes[0].rawValue
    result.value = qrData
    emit('scanned', qrData)
    stopScanning()
  }
}

const onError = (err) => {
  error.value = 'Lỗi camera: ' + err.message
  emit('error', err)
}

const onCameraOn = () => {
  error.value = ''
  // Kiểm tra xem có thể switch camera không
  navigator.mediaDevices.enumerateDevices().then((devices) => {
    const videoDevices = devices.filter((device) => device.kind === 'videoinput')
    canSwitchCamera.value = videoDevices.length > 1
  })
}

const onCameraOff = () => {
  isScanning.value = false
}

const switchCamera = () => {
  currentCamera.value = currentCamera.value === 'auto' ? 'environment' : 'auto'
}
</script>

<style scoped>
.scanner-container {
  max-width: 400px;
  margin: 0 auto;
}

.qr-scanner .card {
  max-width: 500px;
  margin: 0 auto;
}
</style>
