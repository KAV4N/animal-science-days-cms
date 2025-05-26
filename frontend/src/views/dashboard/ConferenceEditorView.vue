<template>
  <div class="conference-editor-view h-screen overflow-hidden">
    <ConferenceEditor />
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

<style scoped>
.conference-editor-view {
  /* Remove any padding/margins to ensure full-screen experience */
  margin: 0;
  padding: 0;
}
</style>