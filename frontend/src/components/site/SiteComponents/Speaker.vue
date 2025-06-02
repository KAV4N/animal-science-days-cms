<template>
  <Card class="w-full cursor-pointer shadow-none" @click="toggleExpanded">
    <template #content>
      <div class="p-2 flex flex-col">
        <!-- Unified Header -->
        <div class="flex space-x-3" :class="[expanded ? 'items-center' : 'flex-grow items-center']">
          <Avatar
            v-if="data.avatar"
            :image="data.avatar"
            size="large"
            shape="circle"
          />
          <Avatar
            v-else
            :label="getInitials(data.name)"
            size="large"
            shape="circle"
            class="bg-blue-500 text-white"
          />
          <div class="flex-1 min-w-0">
            <h3 class="text-base font-semibold text-gray-900 truncate m-0">
              {{ data.name || 'Speaker Name' }}
            </h3>
            <p v-if="data.title" class="text-sm text-gray-600 truncate m-0">
              {{ data.title }}
            </p>
          </div>
          <transition name="fade-icon" mode="out-in">
            <i v-if="expanded" key="up" class="pi pi-chevron-up text-gray-400 text-sm"></i>
            <i v-else key="down" class="pi pi-chevron-down text-gray-400 text-xs"></i>
          </transition>
        </div>

        <!-- Expanded View -->
        <transition name="expand-speaker">
          <div v-if="expanded">
            <div class="overflow-hidden space-y-3 pt-2">
              <!-- Company Info -->
              <p v-if="data.company" class="text-sm text-gray-500 m-0">
                {{ data.company }}
              </p>

              <!-- Contact Info -->
              <div class="space-y-2">
              <div v-if="data.email" class="flex items-center space-x-2">
                <i class="pi pi-envelope text-blue-600 text-sm"></i>
                <a
                  :href="`mailto:${data.email}`"
                  class="text-sm text-blue-600 hover:text-blue-800 truncate flex-1"
                  @click.stop
                >
                  {{ data.email }}
                </a>
              </div>

              <div v-if="data.phone" class="flex items-center space-x-2">
                <i class="pi pi-phone text-green-600 text-sm"></i>
                <a
                  :href="`tel:${data.phone}`"
                  class="text-sm text-green-600 hover:text-green-800"
                  @click.stop
                >
                  {{ data.phone }}
                </a>
              </div>

              <div v-if="data.website" class="flex items-center space-x-2">
                <i class="pi pi-globe text-purple-600 text-sm"></i>
                <a
                  :href="formatUrl(data.website)"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-sm text-purple-600 hover:text-purple-800 truncate flex-1"
                  @click.stop
                >
                  {{ data.website }}
                </a>
              </div>
            </div>

            <!-- Bio -->
            <div v-if="data.bio" class="pt-2 border-t border-gray-200">
              <p class="text-xs text-gray-600 leading-relaxed">
                {{ data.bio }}
              </p>
            </div>

            <!-- Social Media & Actions -->
            <div class="pt-2 border-t border-gray-200">
              <!-- Social Icons -->
              <div v-if="hasSocialMedia" class="flex justify-center space-x-1 mb-3">
                <Button
                  v-if="data.social?.linkedin"
                  :href="formatUrl(data.social.linkedin)"
                  as="a"
                  target="_blank"
                  rel="noopener noreferrer"
                  icon="pi pi-linkedin"
                  rounded
                  text
                  size="small"
                  class="text-blue-700 hover:bg-blue-50"
                  @click.stop
                />
                <Button
                  v-if="data.social?.twitter"
                  :href="formatUrl(data.social.twitter)"
                  as="a"
                  target="_blank"
                  rel="noopener noreferrer"
                  icon="pi pi-twitter"
                  rounded
                  text
                  size="small"
                  class="text-sky-500 hover:bg-sky-50"
                  @click.stop
                />
                <Button
                  v-if="data.social?.github"
                  :href="formatUrl(data.social.github)"
                  as="a"
                  target="_blank"
                  rel="noopener noreferrer"
                  icon="pi pi-github"
                  rounded
                  text
                  size="small"
                  class="text-gray-700 hover:bg-gray-50"
                  @click.stop
                />
              </div>

              <!-- Action Buttons -->
              <div v-if="data.email || data.phone" class="flex gap-2">
                <Button
                  v-if="data.email"
                  :href="`mailto:${data.email}`"
                  as="a"
                  icon="pi pi-envelope"
                  label="Email"
                  size="small"
                  class="flex-1 text-xs"
                  @click.stop
                />
                <Button
                  v-if="data.phone"
                  :href="`tel:${data.phone}`"
                  as="a"
                  icon="pi pi-phone"
                  label="Call"
                  outlined
                  size="small"
                  class="flex-1 text-xs"
                  @click.stop
                />
              </div>
            </div>
            </div>
          </div>
        </transition>
      </div>
    </template>
  </Card>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';

interface SpeakerData {
  name: string;
  title?: string;
  company?: string;
  email?: string;
  phone?: string;
  website?: string;
  bio?: string;
  avatar?: string;
  social?: {
    linkedin?: string;
    twitter?: string;
    github?: string;
  };
}

export default defineComponent({
  name: 'CompactSpeakerCard',
  components: {
    Card,
    Avatar,
    Button
  },
  props: {
    data: {
      type: Object as PropType<SpeakerData>,
      default: () => ({
        name: 'Speaker Name',
        title: '',
        company: '',
        email: '',
        phone: '',
        website: '',
        bio: '',
        avatar: '',
        social: {
          linkedin: '',
          twitter: '',
          github: ''
        }
      })
    }
  },
  data() {
    return {
      expanded: false
    };
  },
  computed: {
    hasSocialMedia(): boolean {
      return !!(
        this.data.social?.linkedin ||
        this.data.social?.twitter ||
        this.data.social?.github
      );
    }
  },
  methods: {
    toggleExpanded() {
      this.expanded = !this.expanded;
    },

    formatUrl(url: string): string {
      if (!url) return '';

      if (!url.match(/^https?:\/\//)) {
        return `https://${url}`;
      }

      return url;
    },

    getInitials(name: string): string {
      if (!name) return '?';

      return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .substring(0, 2)
        .toUpperCase();
    }
  }
});
</script>

<style scoped>
:deep(.p-avatar-image) {
  object-fit: cover;
}

.expand-speaker-enter-active,
.expand-speaker-leave-active {
  display: grid;
  transition: grid-template-rows 0.4s ease-in-out, opacity 0.4s ease-in-out;
}

.expand-speaker-enter-from,
.expand-speaker-leave-to {
  grid-template-rows: 0fr;
  opacity: 0;
}

.expand-speaker-enter-to,
.expand-speaker-leave-from {
  grid-template-rows: 1fr;
  opacity: 1;
}
.fade-icon-enter-active,
.fade-icon-leave-active {
  transition: opacity 0.4s ease-in-out;
}
.fade-icon-enter-from,
.fade-icon-leave-to {
  opacity: 0;
}
.fade-icon-enter-to,
.fade-icon-leave-from {
  opacity: 1;
}
</style>
