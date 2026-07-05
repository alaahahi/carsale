<template>
  <AuthenticatedLayout>
    <Head :title="$t('system_settings')" />

    <div class="py-4">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('system_settings') }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                  {{ $t('system_settings_desc') }}
                </p>
              </div>
              <button @click="goBack" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
                {{ $t('return') }}
              </button>
            </div>

            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-800">
              <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <button
                  @click="activeTab = 'branding'"
                  :class="tabClass('branding')"
                >
                  {{ $t('branding_tab') }}
                </button>
                <button
                  @click="activeTab = 'migrations'"
                  :class="tabClass('migrations')"
                >
                  {{ $t('migrations') }}
                </button>
                <button
                  @click="activeTab = 'logs'"
                  :class="tabClass('logs')"
                >
                  {{ $t('logs') }}
                </button>
                <button
                  @click="activeTab = 'import'"
                  :class="tabClass('import')"
                >
                  {{ $t('import_excel') }}
                </button>
              </nav>
            </div>

            <!-- BRANDING -->
            <div v-show="activeTab === 'branding'" class="space-y-4">
              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('branding_tab') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $t('branding_desc') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('logo_image_label') }}</label>
                    <input v-model="brandingForm.logo_image" type="text"
                           placeholder="logo-color1.png"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $t('logo_upload_hint') }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('login_bg_image_label') }}</label>
                    <input v-model="brandingForm.login_bg_image" type="text"
                           placeholder="logo-color1.png"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $t('login_bg_upload_hint') }}</p>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $t('logo_preview') }}</div>
                    <img v-if="brandingPreview.logo_url" :src="brandingPreview.logo_url" :alt="brandingPreview.company_name"
                         class="max-h-40 mx-auto object-contain rounded" />
                  </div>
                  <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-3 min-h-[120px]"
                       :style="{ backgroundImage: `url('${brandingPreview.login_bg_url || ''}')`, backgroundSize: 'cover', backgroundPosition: 'center' }">
                    <div class="text-xs text-gray-500 dark:text-gray-400 bg-white/80 dark:bg-gray-900/80 inline-block px-2 py-1 rounded">{{ $t('login_bg_preview') }}</div>
                  </div>
                </div>

                <div class="mt-4 flex justify-end">
                  <button @click="saveBranding"
                          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold"
                          :disabled="savingBranding">
                    {{ savingBranding ? $t('saving') : $t('save') }}
                  </button>
                </div>
              </div>
            </div>

            <!-- MIGRATIONS -->
            <div v-show="activeTab === 'migrations'" class="space-y-4">
              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('migrations') }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      {{ migrationsUiEnabled ? $t('migrations_enabled') : $t('migrations_disabled') }}
                    </p>
                  </div>
                  <div class="flex gap-2">
                    <button
                      @click="loadMigrationsList"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                      :disabled="loadingMigrations"
                    >
                      {{ loadingMigrations ? $t('loading') : $t('refresh') }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Data warning -->
              <div v-if="migrationsWarning && (migrationsWarning.has_other_tables || migrationsWarning.has_nonempty_tables)"
                   class="border border-yellow-300 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                <div class="text-sm text-yellow-900 dark:text-yellow-200 font-semibold mb-1">
                  {{ $t('migrations_data_warning_title') }}
                </div>
                <div class="text-sm text-yellow-800 dark:text-yellow-200">
                  {{ $t('migrations_data_warning_desc') }}
                  <span v-if="migrationsWarning.example_table"> (مثال: {{ migrationsWarning.example_table }})</span>
                </div>
              </div>

              <!-- Migrations list -->
              <div class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                  <div class="text-sm text-gray-600 dark:text-gray-300">
                    <span v-if="migrationsDatabase">DB: {{ migrationsDatabase }}</span>
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatNumber(migrations.length) }} {{ $t('items') }}
                  </div>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                      <tr>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('migration') }}</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('status') }}</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('batch') }}</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('execute') }}</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                      <tr v-for="(m, idx) in migrations" :key="m.name" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ idx + 1 }}</td>
                        <td class="px-3 py-2 whitespace-nowrap font-mono text-xs text-gray-900 dark:text-white">
                          {{ m.name }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                          <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                            :class="m.status === 'ran'
                              ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                              : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300'"
                          >
                            {{ m.status === 'ran' ? $t('ran') : $t('pending') }}
                          </span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ m.batch ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                          <button
                            @click="runOne(m)"
                            class="px-3 py-1 rounded-lg text-xs font-semibold"
                            :class="m.status === 'pending' && migrationsUiEnabled
                              ? 'bg-blue-600 hover:bg-blue-700 text-white'
                              : 'bg-gray-200 text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-500'"
                            :disabled="m.status !== 'pending' || !migrationsUiEnabled || runningMigrations"
                          >
                            {{ $t('run_one') }}
                          </button>
                        </td>
                      </tr>
                      <tr v-if="migrations.length === 0">
                        <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ $t('no_data') }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ $t('output') }}</h3>
                  <span class="text-xs text-gray-500 dark:text-gray-400" v-if="migrationsDatabase">
                    DB: {{ migrationsDatabase }}
                  </span>
                </div>
                <textarea
                  class="w-full h-64 font-mono text-xs px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 rounded-lg"
                  readonly
                  :value="migrationsOutput"
                />
              </div>
            </div>

            <!-- LOGS -->
            <div v-show="activeTab === 'logs'" class="space-y-4">
              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('logs') }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      {{ logsPath ? ( $t('log_file') + ': ' + logsPath ) : $t('log_file') }}
                    </p>
                  </div>
                  <div class="flex gap-2">
                    <button
                      @click="loadLogs"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                      :disabled="loadingLogs"
                    >
                      {{ loadingLogs ? $t('loading') : $t('refresh') }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      {{ $t('confirm_text_clear') }}
                    </label>
                    <input
                      v-model="clearConfirm"
                      type="text"
                      placeholder="CLEAR"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm"
                    />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                      {{ $t('clear_logs_note') }}
                    </p>
                  </div>
                  <div class="flex items-end">
                    <button
                      @click="clearLogs"
                      class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold"
                      :disabled="clearingLogs"
                    >
                      {{ clearingLogs ? $t('running') : $t('clear_logs') }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ $t('output') }}</h3>
                  <span class="text-xs text-gray-500 dark:text-gray-400" v-if="logsSize !== null">
                    {{ $t('size') }}: {{ formatNumber(logsSize) }} bytes
                  </span>
                </div>
                <textarea
                  class="w-full h-80 font-mono text-xs px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 rounded-lg"
                  readonly
                  :value="logsContent"
                />
              </div>
            </div>

            <!-- IMPORT -->
            <div v-show="activeTab === 'import'" class="space-y-4">
              <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('import_excel') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $t('import_excel_desc') }}</p>
                <a
                  :href="importCarsUrl"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold"
                >
                  {{ $t('open_import_page') }}
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const props = defineProps({
  importCarsUrl: String,
  migrationsUiEnabled: Boolean
})

const activeTab = ref('migrations')

const loadingMigrations = ref(false)
const runningMigrations = ref(false)
const migrationsOutput = ref('')
const migrationsDatabase = ref('')
const migrations = ref([])
const migrationsWarning = ref(null)

const loadingLogs = ref(false)
const clearingLogs = ref(false)
const logsContent = ref('')
const logsPath = ref('')
const logsSize = ref(null)
const clearConfirm = ref('')

const loadingBranding = ref(false)
const savingBranding = ref(false)
const brandingForm = ref({
  logo_image: '',
  login_bg_image: '',
})
const brandingPreview = ref({
  logo_url: '/img/logo-color1.png',
  login_bg_url: '/img/logo-color1.png',
  company_name: '',
})

const tabClass = (tab) => {
  const base = 'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm'
  if (activeTab.value === tab) {
    return `${base} border-blue-600 text-blue-600 dark:text-blue-400`
  }
  return `${base} border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300`
}

const formatNumber = (n) => Math.round(Number(n || 0)).toLocaleString('en-US')

const goBack = () => window.history.back()

const resolvePreviewUrl = (filename, fallback = 'logo-color1.png') => {
  const name = (filename || fallback).split(/[/\\]/).pop() || fallback
  return `/img/${name}`
}

const loadBranding = async () => {
  loadingBranding.value = true
  try {
    const res = await axios.get(route('system-settings.branding.get'))
    const b = res.data.branding || {}
    brandingForm.value = {
      logo_image: b.logo_image || '',
      login_bg_image: b.login_bg_image || '',
    }
    brandingPreview.value = {
      logo_url: b.logo_url || resolvePreviewUrl(brandingForm.value.logo_image),
      login_bg_url: b.login_bg_url || resolvePreviewUrl(brandingForm.value.login_bg_image || brandingForm.value.logo_image),
      company_name: b.company_name || '',
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'فشل جلب إعدادات الشعار')
  } finally {
    loadingBranding.value = false
  }
}

const saveBranding = async () => {
  savingBranding.value = true
  try {
    const res = await axios.post(route('system-settings.branding.update'), brandingForm.value)
    const b = res.data.branding || {}
    brandingPreview.value = {
      logo_url: b.logo_url,
      login_bg_url: b.login_bg_url,
      company_name: b.company_name || '',
    }
    toast.success(res.data.message || 'تم الحفظ')
  } catch (e) {
    toast.error(e.response?.data?.message || 'فشل حفظ إعدادات الشعار')
  } finally {
    savingBranding.value = false
  }
}

const loadMigrationsList = async () => {
  loadingMigrations.value = true
  try {
    const res = await axios.get(route('system-settings.migrations.list'))
    migrationsDatabase.value = res.data.database || ''
    migrationsWarning.value = res.data.warning || null
    migrations.value = res.data.migrations || []
  } catch (e) {
    toast.error(e.response?.data?.message || 'حدث خطأ في جلب قائمة المايغريشن')
  } finally {
    loadingMigrations.value = false
  }
}

const runOne = async (m) => {
  if (!props.migrationsUiEnabled || !m?.name) return
  runningMigrations.value = true
  try {
    const res = await axios.post(route('system-settings.migrations.run-one'), {
      migration: m.name,
    })
    migrationsDatabase.value = res.data.database || ''
    migrationsOutput.value = res.data.output || ''
    toast.success('تم تشغيل المايغريشن')
    await loadMigrationsList()
  } catch (e) {
    toast.error(e.response?.data?.message || 'فشل تشغيل المايغريشن')
  } finally {
    runningMigrations.value = false
  }
}

const loadLogs = async () => {
  loadingLogs.value = true
  try {
    const res = await axios.get(route('system-settings.logs.get'))
    logsContent.value = res.data.content || ''
    logsPath.value = res.data.path || ''
    logsSize.value = res.data.size ?? null
  } catch (e) {
    toast.error(e.response?.data?.message || 'فشل جلب اللوج')
  } finally {
    loadingLogs.value = false
  }
}

const clearLogs = async () => {
  clearingLogs.value = true
  try {
    await axios.post(route('system-settings.logs.clear'), { confirm: clearConfirm.value })
    toast.success('تم تفريغ اللوج')
    clearConfirm.value = ''
    await loadLogs()
  } catch (e) {
    toast.error(e.response?.data?.message || 'فشل تفريغ اللوج')
  } finally {
    clearingLogs.value = false
  }
}

onMounted(async () => {
  await loadBranding()
  await loadMigrationsList()
  await loadLogs()
})
</script>

