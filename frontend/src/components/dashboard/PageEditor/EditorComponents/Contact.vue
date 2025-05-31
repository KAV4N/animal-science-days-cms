<template>
  <Dialog 
    v-model:visible="localVisible" 
    header="Edit Contact Information" 
    :style="{ width: '95vw', maxWidth: '800px' }" 
    :modal="true"
    :maximizable="false"
    :closable="true"
    :breakpoints="{ '640px': '95vw' }"
  >
    <Card>
      <template #content>
        <div class="space-y-6 p-0">
          <div class="text-center mb-6">
            <div class="rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
              <i class="pi pi-phone text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold mb-4">Edit Contact Information</h2>
            <p>Configure contact details for your conference</p>
          </div>
          
          <div class="space-y-6">
            <!-- Component Name -->
            <div>
              <label for="contactComponentName" class="block text-sm font-medium mb-2">Component Name</label>
              <InputText 
                id="contactComponentName" 
                v-model="localComponentName" 
                class="w-full" 
                placeholder="Enter component name..."
              />
            </div>

            <!-- Contact Title -->
            <div>
              <label for="contactTitle" class="block text-sm font-medium mb-2">Contact Section Title</label>
              <InputText 
                id="contactTitle" 
                v-model="localContactData.title" 
                class="w-full" 
                placeholder="e.g., Contact Us, Get in Touch..."
              />
            </div>

            <!-- Contact Description -->
            <div>
              <label for="contactDescription" class="block text-sm font-medium mb-2">Description</label>
              <Textarea
                id="contactDescription"
                v-model="localContactData.description"
                :rows="3"
                class="w-full"
                placeholder="Brief description about contacting your conference..."
              />
            </div>

            <!-- Contact Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Email -->
              <div>
                <label for="contactEmail" class="block text-sm font-medium mb-2">Email Address</label>
                <InputText 
                  id="contactEmail" 
                  v-model="localContactData.email" 
                  class="w-full" 
                  placeholder="contact@conference.com"
                  type="email"
                />
              </div>

              <!-- Phone -->
              <div>
                <label for="contactPhone" class="block text-sm font-medium mb-2">Phone Number</label>
                <InputText 
                  id="contactPhone" 
                  v-model="localContactData.phone" 
                  class="w-full" 
                  placeholder="+1 (555) 123-4567"
                  type="tel"
                />
              </div>

              <!-- Website -->
              <div>
                <label for="contactWebsite" class="block text-sm font-medium mb-2">Website</label>
                <InputText 
                  id="contactWebsite" 
                  v-model="localContactData.website" 
                  class="w-full" 
                  placeholder="https://www.conference.com"
                  type="url"
                />
              </div>

              <!-- Office Hours -->
              <div>
                <label for="contactHours" class="block text-sm font-medium mb-2">Office Hours</label>
                <InputText 
                  id="contactHours" 
                  v-model="localContactData.hours" 
                  class="w-full" 
                  placeholder="Mon-Fri 9AM-5PM"
                />
              </div>
            </div>

            <!-- Address Section -->
            <Card>
              <template #title>
                <h4 class="text-lg font-semibold">Address Information</h4>
              </template>
              <template #content>
                <div class="space-y-4 p-0">
                  <div>
                    <label for="contactAddress" class="block text-sm font-medium mb-2">Street Address</label>
                    <InputText 
                      id="contactAddress" 
                      v-model="localContactData.address" 
                      class="w-full" 
                      placeholder="123 Conference Street"
                    />
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label for="contactCity" class="block text-sm font-medium mb-2">City</label>
                      <InputText 
                        id="contactCity" 
                        v-model="localContactData.city" 
                        class="w-full" 
                        placeholder="City"
                      />
                    </div>
                    
                    <div>
                      <label for="contactState" class="block text-sm font-medium mb-2">State/Province</label>
                      <InputText 
                        id="contactState" 
                        v-model="localContactData.state" 
                        class="w-full" 
                        placeholder="State"
                      />
                    </div>
                    
                    <div>
                      <label for="contactZip" class="block text-sm font-medium mb-2">ZIP/Postal Code</label>
                      <InputText 
                        id="contactZip" 
                        v-model="localContactData.zip" 
                        class="w-full" 
                        placeholder="12345"
                      />
                    </div>
                  </div>
                  
                  <div>
                    <label for="contactCountry" class="block text-sm font-medium mb-2">Country</label>
                    <InputText 
                      id="contactCountry" 
                      v-model="localContactData.country" 
                      class="w-full" 
                      placeholder="Country"
                    />
                  </div>
                </div>
              </template>
            </Card>

            <!-- Social Media Section -->
            <Card>
              <template #title>
                <h4 class="text-lg font-semibold">Social Media Links</h4>
              </template>
              <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-0">
                  <div>
                    <label for="contactFacebook" class="block text-sm font-medium mb-2">Facebook</label>
                    <InputText 
                      id="contactFacebook" 
                      v-model="localContactData.social.facebook" 
                      class="w-full" 
                      placeholder="https://facebook.com/conference"
                    />
                  </div>
                  
                  <div>
                    <label for="contactTwitter" class="block text-sm font-medium mb-2">Twitter/X</label>
                    <InputText 
                      id="contactTwitter" 
                      v-model="localContactData.social.twitter" 
                      class="w-full" 
                      placeholder="https://twitter.com/conference"
                    />
                  </div>
                  
                  <div>
                    <label for="contactLinkedin" class="block text-sm font-medium mb-2">LinkedIn</label>
                    <InputText 
                      id="contactLinkedin" 
                      v-model="localContactData.social.linkedin" 
                      class="w-full" 
                      placeholder="https://linkedin.com/company/conference"
                    />
                  </div>
                  
                  <div>
                    <label for="contactInstagram" class="block text-sm font-medium mb-2">Instagram</label>
                    <InputText 
                      id="contactInstagram" 
                      v-model="localContactData.social.instagram" 
                      class="w-full" 
                      placeholder="https://instagram.com/conference"
                    />
                  </div>
                </div>
              </template>
            </Card>
            
            <!-- Published Checkbox -->
            <Card>
              <template #content>
                <div class="flex items-center p-0">
                  <Checkbox v-model="localPublished" inputId="contactPublished" binary />
                  <label for="contactPublished" class="ml-4 text-sm font-medium">
                    Publish immediately
                  </label>
                </div>
                <p class="text-sm mt-3 ml-8">Published components are visible to conference attendees</p>
              </template>
            </Card>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
            <Button 
              label="Cancel" 
              icon="pi pi-times" 
              @click="handleCancel" 
              outlined
              class="w-full sm:w-auto"
            />
            <Button 
              label="Save Changes" 
              icon="pi pi-check" 
              @click="handleSave" 
              autofocus
              class="w-full sm:w-auto"
            />
          </div>
        </div>
      </template>
    </Card>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';

interface ContactData {
  title: string;
  description: string;
  email: string;
  phone: string;
  website: string;
  hours: string;
  address: string;
  city: string;
  state: string;
  zip: string;
  country: string;
  social: {
    facebook: string;
    twitter: string;
    linkedin: string;
    instagram: string;
  };
}

export default defineComponent({
  name: 'ContactComponent',
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    componentName: {
      type: String,
      default: ''
    },
    componentData: {
      type: Object as PropType<ContactData>,
      default: () => ({
        title: 'Contact Us',
        description: '',
        email: '',
        phone: '',
        website: '',
        hours: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        social: {
          facebook: '',
          twitter: '',
          linkedin: '',
          instagram: ''
        }
      })
    },
    isPublished: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:visible', 'save', 'cancel'],
  data() {
    return {
      localComponentName: '',
      localContactData: {
        title: 'Contact Us',
        description: '',
        email: '',
        phone: '',
        website: '',
        hours: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        social: {
          facebook: '',
          twitter: '',
          linkedin: '',
          instagram: ''
        }
      } as ContactData,
      localPublished: false,
      localVisible: false
    };
  },
  watch: {
    visible: {
      handler(newVal) {
        this.localVisible = newVal;
        if (newVal) {
          this.initializeData();
        }
      },
      immediate: true
    },
    localVisible(newVal) {
      if (!newVal && this.visible) {
        // Dialog was closed by X button or ESC
        this.handleCancel();
      }
    },
    componentName: {
      handler(newVal) {
        this.localComponentName = newVal;
      },
      immediate: true
    },
    componentData: {
      handler(newVal) {
        this.initializeData();
      },
      immediate: true,
      deep: true
    },
    isPublished: {
      handler(newVal) {
        this.localPublished = newVal;
      },
      immediate: true
    }
  },
  methods: {
    initializeData() {
      this.localComponentName = this.componentName;
      
      // Handle both direct data and wrapped data structures
      let contactData;
      if (this.componentData && typeof this.componentData === 'object') {
        contactData = this.componentData;
      } else {
        // Default data for new component
        contactData = {
          title: 'Contact Us',
          description: '',
          email: '',
          phone: '',
          website: '',
          hours: '',
          address: '',
          city: '',
          state: '',
          zip: '',
          country: '',
          social: {
            facebook: '',
            twitter: '',
            linkedin: '',
            instagram: ''
          }
        };
      }
      
      this.localContactData = { 
        title: contactData?.title || 'Contact Us',
        description: contactData?.description || '',
        email: contactData?.email || '',
        phone: contactData?.phone || '',
        website: contactData?.website || '',
        hours: contactData?.hours || '',
        address: contactData?.address || '',
        city: contactData?.city || '',
        state: contactData?.state || '',
        zip: contactData?.zip || '',
        country: contactData?.country || '',
        social: {
          facebook: contactData?.social?.facebook || '',
          twitter: contactData?.social?.twitter || '',
          linkedin: contactData?.social?.linkedin || '',
          instagram: contactData?.social?.instagram || ''
        }
      };
      
      this.localPublished = this.isPublished;
    },
    handleSave() {
      // Save only the raw contact data - no HTML generation
      this.$emit('save', {
        name: this.localComponentName,
        data: this.localContactData,
        isPublished: this.localPublished
      });
    },
    handleCancel() {
      this.$emit('cancel');
      this.$emit('update:visible', false);
    }
  }
});
</script>