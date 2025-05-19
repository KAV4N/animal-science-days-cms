<template>
  <div class="dashboard-container">
    <div class="header bg-white shadow-sm p-4 mb-4">
      <h1 class="text-2xl font-bold">Conference Page Editor</h1>
    </div>
    
    <div class="content px-4">
      <div class="bg-white rounded-lg shadow-sm p-4">
        <ConferenceEditor />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ConferenceEditor from '@/components/dashboard/ConferenceEditor.vue';

export default defineComponent({
  name: 'ConferenceEditorView',
  components: {
    ConferenceEditor
  }
});
</script>