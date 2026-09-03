<template lang="pug">
.data-table-wrapper
  .table-toolbar(v-if="$slots.toolbar || searchable")
    .search-box(v-if="searchable")
      el-input(
        v-model="searchQuery"
        :placeholder="searchPlaceholder"
        clearable
        @clear="onSearch"
        @keyup.enter="onSearch"
        style="width: 280px"
      )
        template(#prefix)
          el-icon
            Search
    .toolbar-actions
      slot(name="toolbar")

  el-table(
    v-loading="loading"
    :data="data"
    stripe
    style="width: 100%"
    @sort-change="onSortChange"
  )
    slot

  .table-pagination(v-if="totalItems > 0")
    el-pagination(
      v-model:current-page="currentPage"
      v-model:page-size="pageSize"
      :page-sizes="[10, 20, 50, 100]"
      layout="total, sizes, prev, pager, next, jumper"
      :total="totalItems"
      @size-change="onSizeChange"
      @current-change="onPageChange"
    )
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { Search } from '@element-plus/icons-vue';

const props = withDefaults(
  defineProps<{
    data: any[];
    loading?: boolean;
    totalItems?: number;
    searchable?: boolean;
    searchPlaceholder?: string;
  }>(),
  {
    loading: false,
    totalItems: 0,
    searchable: true,
    searchPlaceholder: 'Buscar...',
  }
);

const emit = defineEmits<{
  (e: 'update:page', page: number): void;
  (e: 'update:pageSize', size: number): void;
  (e: 'search', query: string): void;
  (e: 'sort', sort: { prop: string; order: string }): void;
}>();

const currentPage = ref(1);
const pageSize = ref(10);
const searchQuery = ref('');

function onSearch() {
  currentPage.value = 1;
  emit('search', searchQuery.value);
}

function onPageChange(page: number) {
  emit('update:page', page);
}

function onSizeChange(size: number) {
  emit('update:pageSize', size);
}

function onSortChange(sort: { prop: string; order: string }) {
  emit('sort', sort);
}
</script>

<style scoped>
.data-table-wrapper {
  background: #ffffff;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}
.table-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
