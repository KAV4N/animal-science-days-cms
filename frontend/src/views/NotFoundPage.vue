<template>
  <div class="min-h-screen bg-surface-200 flex flex-col justify-center items-center px-4">
    <div class="max-w-md w-full text-center">
      <Card class="shadow-lg">
        <template #content>
          <div class="py-8 px-6">
            <!-- 404 Icon/Number -->
            <div class="mb-6">
              <div class="text-6xl font-bold text-primary-500 mb-2 animate-fade-in">404</div>
              <div class="w-20 h-1 bg-primary-500 mx-auto rounded-full"></div>
            </div>

            <!-- Error Message -->
            <div class="mb-6">
              <h1 class="text-2xl font-bold text-gray-900 mb-2">Page Not Found</h1>
              <p class="text-gray-600 leading-relaxed">
                Sorry, we couldn't find the page you're looking for. 
                The page may have been moved, deleted, or the URL might be incorrect.
              </p>
            </div>

            <!-- Current URL Display -->
            <div class="mb-6 p-3 bg-gray-50 rounded-lg border">
              <p class="text-sm text-gray-500 mb-1">Requested URL:</p>
              <code class="text-sm text-gray-800 break-all">{{ currentUrl }}</code>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
              <Button 
                label="Go Home" 
                icon="pi pi-home" 
                @click="goHome"
                class="w-full hover-lift"
                size="large"
              />
              
              <div class="flex gap-2">
                <Button 
                  label="Go Back" 
                  icon="pi pi-arrow-left" 
                  @click="goBack"
                  outlined
                  class="flex-1 hover-lift"
                />
                <Button 
                  label="Archive" 
                  icon="pi pi-calendar" 
                  @click="goToArchive"
                  outlined
                  class="flex-1 hover-lift"
                />
              </div>
            </div>

            <!-- Help Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">What can you do?</h3>
              <div class="text-left space-y-2">
                <div class="flex items-start gap-2">
                  <i class="pi pi-check-circle text-green-500 mt-1 text-sm"></i>
                  <span class="text-sm text-gray-600">Check the URL for typos</span>
                </div>
                <div class="flex items-start gap-2">
                  <i class="pi pi-check-circle text-green-500 mt-1 text-sm"></i>
                  <span class="text-sm text-gray-600">Browse our conference archive</span>
                </div>
                <div class="flex items-start gap-2">
                  <i class="pi pi-check-circle text-green-500 mt-1 text-sm"></i>
                  <span class="text-sm text-gray-600">Return to the homepage</span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Additional Information -->
    <div class="mt-8 text-center">
      <p class="text-sm text-gray-500">
        Error Code: 404 • {{ formatCurrentTime() }}
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'NotFoundPage',
  
  data() {
    return {
      searchQuery: '',
      currentUrl: ''
    };
  },
  
  mounted() {
    // Capture the current URL when component mounts
    this.currentUrl = window.location.href;
    
    // Set page title
    document.title = 'Page Not Found | Conference Management';
    
    // Optional: Log 404 error for analytics
    this.logPageNotFound();
  },
  
  methods: {
    goHome(): void {
      this.$router.push({ name: 'HomePage' });
    },
    
    goBack(): void {
      // Check if there's history to go back to
      if (window.history.length > 1) {
        this.$router.go(-1);
      } else {
        // Fallback to home if no history
        this.goHome();
      }
    },
    
    goToArchive(): void {
      this.$router.push({ name: 'archive' });
    },
    
    
    formatCurrentTime(): string {
      return new Date().toLocaleString();
    },
    
    logPageNotFound(): void {
      // Log 404 error for analytics or debugging
      console.warn('404 Page Not Found:', {
        url: this.currentUrl,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        referrer: document.referrer || 'Direct access'
      });
      
      // You can add analytics tracking here
      // Example: gtag('event', '404_error', { page_location: this.currentUrl });
    }
  }
});
</script>

<style scoped>
/* Custom styles for 404 page */
.break-all {
  word-break: break-all;
}

/* Animation for the 404 number */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeInUp 0.6s ease-out;
}

/* Hover effect for buttons */
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Responsive text sizing */
@media (max-width: 640px) {
  .text-6xl {
    font-size: 3.5rem;
  }
}
</style>