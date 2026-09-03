<template lang="pug">
.company-switcher(v-if="companies.length > 0")
  el-dropdown(@command="handleCompanyChange")
    span.company-trigger
      el-icon.mr-1
        OfficeBuilding
      span.font-medium {{ currentCompany?.name || 'Seleccionar Empresa' }}
      el-icon.el-icon--right
        ArrowDown
    template(#dropdown)
      el-dropdown-menu
        el-dropdown-item(
          v-for="c in companies"
          :key="c.id"
          :command="c.id"
          :divided="false"
          :disabled="c.id === currentCompany?.id"
        )
          .company-item
            span {{ c.name }}
            el-tag(size="small" type="info" class="ml-2") {{ c.taxId }}
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { useAuthStore } from '@/modules/auth/store/useAuthStore';
import { OfficeBuilding, ArrowDown } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const auth = useAuthStore();
const companies = computed(() => auth.user?.companies || []);
const currentCompany = computed(() => auth.activeCompany);

function handleCompanyChange(companyId: number) {
  auth.setActiveCompany(companyId);
  ElMessage.success('Empresa activa actualizada');
  // Recargar página para refrescar todas las llamadas y cachés
  window.location.reload();
}
</script>

<style scoped>
.company-switcher {
  display: flex;
  align-items: center;
  margin-right: 16px;
}
.company-trigger {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 6px 12px;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 14px;
  color: #1e293b;
  transition: background-color 0.2s;
}
.company-trigger:hover {
  background: #e2e8f0;
}
.company-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}
.mr-1 {
  margin-right: 4px;
}
.ml-2 {
  margin-left: 8px;
}
</style>
