<template>
  <Toast />
  <div class="conference-editor h-screen flex flex-col">
    <!-- Top Header -->
    <Card class="header shadow-sm p-4 flex-none rounded mb-2">
      <template #content>
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 p-0">
          <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold truncate">Conference Page Editor</h1>
            <p class="mt-2 text-sm sm:text-base hidden sm:block">Manage and edit conference pages and components</p>
          </div>

          <!-- Lock status and actions -->
          <div class="flex items-center gap-4 flex-wrap">
            <div v-if="pageMenuStore.lockStatus.isLocked" class="flex items-center gap-2 p-2 rounded-lg text-sm">
              <i class="pi pi-lock"></i>
              <span class="font-medium hidden sm:inline">Editing Mode Active</span>
              <span class="font-medium sm:hidden">Editing</span>
            </div>

            <div v-else-if="!pageMenuStore.loading" class="flex items-center gap-2 p-2 rounded-lg text-sm">
              <i class="pi pi-lock-open"></i>
              <span class="hidden sm:inline">Read Only Mode</span>
              <span class="sm:hidden">Read Only</span>
              <Button
                label="Enable"
                icon="pi pi-lock"
                @click="acquireLock"
                size="small"
                class="ml-2 text-xs"
              />
            </div>

            <Button
              icon="pi pi-times"
              class="hidden sm:inline-flex"
              label="Exit"
              @click="handleExit"
              outlined
              size="small"
            />
            <Button
              icon="pi pi-times"
              class="sm:hidden"
              @click="handleExit"
              outlined
              size="small"
            />

            <Button
              label="Live Preview"
              icon="pi pi-eye"
              @click="openLivePreview"
              class="hidden sm:inline-flex"
              outlined
              size="small"
            />
            <Button
              icon="pi pi-eye"
              @click="openLivePreview"
              class="sm:hidden"
              outlined
              size="small"
            />
          </div>
        </div>
      </template>
    </Card>

    <!-- Mobile Menu Toggle -->
    <div class="2xl:hidden p-4">
      <div class="flex justify-between items-center">
        <Button
          :icon="showLeftPanel ? 'pi pi-times' : 'pi pi-bars'"
          :label="showLeftPanel ? 'Close' : 'Overview'"
          @click="showLeftPanel = !showLeftPanel"
          outlined
          size="small"
        />
        <Button
          :icon="showRightPanel ? 'pi pi-times' : 'pi pi-list'"
          :label="showRightPanel ? 'Close' : 'Pages'"
          @click="showRightPanel = !showRightPanel"
          outlined
          size="small"
        />
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex overflow-hidden relative">
      <!-- Conference Overview Panel (Left) -->
      <div :class="[
        'flex flex-col transition-all duration-300 z-20 bg-surface-100 rounded shadow',
        '2xl:w-80 2xl:relative 2xl:translate-x-0',
        'absolute inset-y-0 left-0 w-80 max-w-[90vw]',
        showLeftPanel ? 'translate-x-0 shadow-lg' : '-translate-x-full'
      ]">
        <!-- Conference Info Card -->
        <Card class="m-4 flex-none">
          <template #title>
            <h2 class="text-lg font-semibold">Conference Overview</h2>
          </template>
          <template #content>
            <div class="space-y-3 text-sm p-0">
              <div class="flex items-center gap-2">
                <i class="pi pi-calendar"></i>
                <span>Conference ID: {{ conferenceId }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-file"></i>
                <span>{{ pageMenuStore.menus.length }} Pages Total</span>
              </div>
              <div v-if="conferenceStore.getCurrentPublicConference" class="flex items-center gap-2">
                <i class="pi pi-info-circle"></i>
                <span>{{ conferenceStore.getCurrentPublicConference.title }}</span>
              </div>
            </div>
          </template>
        </Card>

        <Card class="m-4 flex-none">
          <template #title>
            <h3 class="text-base font-semibold">Actions</h3>
          </template>
          <template #content>
            <div class="space-y-3 p-0">
              <Button
                label="Create New Page"
                icon="pi pi-plus"
                @click="openAddMenuDialog"
                :disabled="!pageMenuStore.isLocked"
                class="w-full text-sm"
                size="small"
              />
              <Button
                label="Add Component"
                icon="pi pi-plus-circle"
                @click="openAddComponentDialog"
                :disabled="!pageMenuStore.selectedMenu || !pageMenuStore.isLocked"
                outlined
                class="w-full text-sm"
                size="small"
              />
              <Button
                label="Manage Media"
                icon="pi pi-images"
                @click="showMediaManager = true"
                :disabled="!pageMenuStore.isLocked"
                outlined
                class="w-full text-sm"
                size="small"
              />
            </div>
          </template>
        </Card>

        <!-- Loading State -->
        <div v-if="pageMenuStore.loading && !loadingSelectedMenu" class="flex-1 flex items-center justify-center p-4">
          <Card class="w-full">
            <template #content>
              <div class="text-center p-0">
                <i class="pi pi-spin pi-spinner text-3xl mb-4"></i>
                <p class="text-sm">Loading conference data...</p>
              </div>
            </template>
          </Card>
        </div>

        <!-- Empty State -->
        <div v-else-if="conferenceStore.loading" class="flex-1 flex items-center justify-center p-4">
          <Card class="w-full">
            <template #content>
              <div class="text-center p-0">
                <i class="pi pi-spin pi-spinner text-3xl mb-4"></i>
                <p class="text-sm">Loading conference details...</p>
              </div>
            </template>
          </Card>
        </div>

        <!-- Empty State -->
        <div v-else-if="!pageMenuStore.hasMenus" class="flex-1 flex items-center justify-center p-4">
          <Card class="w-full">
            <template #content>
              <div class="text-center p-0">
                <i class="pi pi-file text-6xl mb-4"></i>
                <h3 class="text-lg font-medium mb-4">No Pages Created</h3>
                <p class="mb-4 text-sm">Get started by creating your first conference page</p>
                <Button
                  label="Create First Page"
                  icon="pi pi-plus"
                  @click="openAddMenuDialog"
                  :disabled="!pageMenuStore.isLocked"
                  size="small"
                />
              </div>
            </template>
          </Card>
        </div>

        <!-- Conference Stats -->
        <div v-else class="flex-1 flex flex-col justify-center p-4">
          <div class="space-y-4">
            <Card>
              <template #content>
                <div class="flex items-center gap-4 p-0">
                  <i class="pi pi-chart-bar text-2xl"></i>
                  <div>
                    <p class="font-semibold text-sm">Total Components</p>
                    <p class="text-2xl font-bold">
                      {{ pageMenuStore.selectedMenu?.page_data?.length || 0 }}
                    </p>
                  </div>
                </div>
              </template>
            </Card>

            <Card>
              <template #content>
                <div class="flex items-center gap-4 p-0">
                  <i class="pi pi-check-circle text-2xl"></i>
                  <div>
                    <p class="font-semibold text-sm">Published Pages</p>
                    <p class="text-2xl font-bold">
                      {{ pageMenuStore.menus.filter(m => m.is_published).length }}
                    </p>
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>
      </div>

      <!-- Main Editor Area (Center) -->
      <div class="flex-1 flex flex-col min-w-0 bg-surface-100 rounded mx-2 shadow">
        <!-- No Page Selected State -->
        <div v-if="!pageMenuStore.selectedMenu && !pageMenuStore.loading" class="flex-1 flex items-center justify-center p-4">
          <Card class="max-w-md">
            <template #content>
              <div class="text-center p-0">
                <i class="pi pi-arrow-right text-6xl mb-4"></i>
                <h3 class="text-xl font-medium mb-4">Select a Page to Edit</h3>
                <p class="text-base">Choose a page from the pages panel to start editing its content and components</p>
              </div>
            </template>
          </Card>
        </div>

        <!-- Loading Selected Menu -->
        <div v-else-if="loadingSelectedMenu" class="flex-1 flex items-center justify-center">
          <Card>
            <template #content>
              <div class="text-center p-0">
                <i class="pi pi-spin pi-spinner text-4xl mb-4"></i>
                <h3 class="text-lg font-medium mb-4">Loading Page Content</h3>
                <p class="text-sm">Please wait while we load the page data...</p>
              </div>
            </template>
          </Card>
        </div>

        <!-- Selected Page Editor -->
        <div v-else-if="pageMenuStore.selectedMenu" class="flex-1 flex flex-col min-h-0 p-4 gap-2">
          <!-- Page Header Card -->
          <Card class="flex-none">
            <template #content>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-0">
                <div class="min-w-0">
                  <h2 class="text-2xl font-bold truncate">{{ pageMenuStore.selectedMenu.title }}</h2>
                  <div class="flex flex-wrap items-center gap-4 mt-3">
                    <Badge
                      :value="pageMenuStore.selectedMenu.is_published ? 'Published' : 'Draft'"
                      :severity="pageMenuStore.selectedMenu.is_published ? 'success' : 'warning'"
                    />
                    <span class="text-sm">Slug: /{{ pageMenuStore.selectedMenu.slug }}</span>
                    <span class="text-sm">Order: {{ pageMenuStore.selectedMenu.order }}</span>
                  </div>
                </div>
                <Button
                  icon="pi pi-cog"
                  class="hidden sm:inline-flex"
                  label="Page Settings"
                  @click="handleEditMenuTitle(pageMenuStore.selectedMenu.id)"
                  :disabled="!pageMenuStore.isLocked"
                  outlined
                  size="small"
                />
              </div>
            </template>
          </Card>

          <!-- Components Section -->
          <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Components Header Card -->
            <Card class="flex-none">
              <template #content>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-0">
                  <h3 class="text-lg font-semibold">Page Components</h3>
                  <div class="flex items-center justify-between gap-4">
                    <span class="text-sm">
                      {{ pageMenuStore.selectedMenu.page_data?.length || 0 }} components
                    </span>
                    <Button
                      icon="pi pi-plus"
                      class="hidden sm:inline-flex"
                      label="Add Component"
                      @click="openAddComponentDialog"
                      :disabled="!pageMenuStore.isLocked"
                      size="small"
                    />
                    <Button
                      icon="pi pi-plus"
                      class="sm:hidden"
                      @click="openAddComponentDialog"
                      :disabled="!pageMenuStore.isLocked"
                      size="small"
                    />
                  </div>
                </div>
              </template>
            </Card>

            <!-- Empty Components State -->
            <div v-if="!pageMenuStore.selectedMenu.page_data || pageMenuStore.selectedMenu.page_data.length === 0"
                 class="flex-1 flex items-center justify-center p-4">
              <Card class="max-w-md">
                <template #content>
                  <div class="text-center p-0">
                    <i class="pi pi-plus-circle text-6xl mb-4"></i>
                    <h3 class="text-xl font-medium mb-4">No Components Yet</h3>
                    <p class="mb-6 text-base">Start building your page by adding components like text editors, images, or other content blocks</p>
                    <Button
                      label="Add Your First Component"
                      icon="pi pi-plus"
                      @click="openAddComponentDialog"
                      :disabled="!pageMenuStore.isLocked"
                      size="small"
                    />
                  </div>
                </template>
              </Card>
            </div>

            <!-- Components List -->
            <div v-else class="flex-1 overflow-y-auto">
              <Divider />
              <div class="space-y-4">
                <Card v-for="(component, index) in pageMenuStore.selectedMenu.page_data"
                     :key="component.id"
                     class="group hover:shadow-md transition-all duration-200">

                  <!-- Component Header -->
                  <template #header>
                    <div class="p-2">
                      <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0 flex-1">
                          <div class="rounded-lg p-2 flex-shrink-0">
                            <i :class="getComponentIcon(component.component_type)" class="text-lg"></i>
                          </div>
                          <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-base truncate">
                              {{ getComponentName(component.component_type) }}: {{ component.tag || 'Unnamed Component' }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-3 mt-2">
                              <Badge
                                :value="component.is_published ? 'Published' : 'Draft'"
                                :severity="component.is_published ? 'success' : 'warning'"
                                class="text-xs"
                              />
                              <span class="text-xs">Position: {{ index + 1 }}</span>
                            </div>
                          </div>
                        </div>

                        <!-- Component Actions -->
                        <div class="flex items-center gap-2 opacity-100 transition-opacity flex-shrink-0">
                          <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex gap-2">
                              <Button
                                icon="pi pi-arrow-up"
                                size="small"
                                :disabled="isFirstComponent(component.id) || !pageMenuStore.isLocked"
                                @click="moveComponentUp(component.id)"
                                outlined
                                class="p-2"
                              />
                              <Button
                                icon="pi pi-arrow-down"
                                size="small"
                                :disabled="isLastComponent(component.id) || !pageMenuStore.isLocked"
                                @click="moveComponentDown(component.id)"
                                outlined
                                class="p-2"
                              />
                            </div>
                            <div class="flex gap-2">
                              <Button
                                icon="pi pi-pencil"
                                size="small"
                                :disabled="!pageMenuStore.isLocked"
                                @click="editComponent(component.id)"
                                outlined
                                class="p-2"
                              />
                              <Button
                                icon="pi pi-trash"
                                size="small"
                                :disabled="!pageMenuStore.isLocked"
                                @click="confirmDeleteComponent(component.id)"
                                outlined
                                class="p-2"
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                </Card>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pages Sidebar (Right) -->
      <div :class="[
        'flex flex-col transition-all duration-300 z-10 bg-surface-100 rounded shadow',
        '2xl:w-96 2xl:relative 2xl:translate-x-0',
        'absolute inset-y-0 right-0 w-80 max-w-[90vw]',
        showRightPanel ? 'translate-x-0 shadow-lg' : 'translate-x-full'
      ]">
        <!-- Sidebar Header Card -->
        <Card class="m-4 flex-none">
          <template #title>
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-bold">Pages</h3>
              <Button
                icon="pi pi-plus"
                size="small"
                @click="openAddMenuDialog"
                :disabled="!pageMenuStore.isLocked"
                class="rounded-full"
              />
            </div>
          </template>
          <template #subtitle>
            <p class="text-sm">Manage your conference pages</p>
          </template>
        </Card>
        <Divider />
        <!-- Pages List -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="pageMenuStore.loading && !loadingSelectedMenu" class="flex items-center justify-center h-32">
            <Card>
              <template #content>
                <div class="text-center p-0">
                  <i class="pi pi-spin pi-spinner text-2xl mb-4"></i>
                  <p class="text-sm">Loading pages...</p>
                </div>
              </template>
            </Card>
          </div>

          <div v-else-if="pageMenuStore.menus.length === 0" class="flex items-center justify-center h-32">
            <Card>
              <template #content>
                <div class="text-center p-0">
                  <i class="pi pi-file text-4xl mb-4"></i>
                  <p class="text-sm">No pages created yet</p>
                </div>
              </template>
            </Card>
          </div>

          <div v-else class="space-y-4">
            <Card v-for="menu in pageMenuStore.menus"
                 :key="menu.id"
                 :class="[
                   'group cursor-pointer transition-all duration-200 hover:shadow-md',
                   pageMenuStore.selectedMenu?.id === menu.id
                     ? 'shadow-md border-l-4 border-primary'
                     : ''
                 ]"
                 @click="selectMenu(menu.id)">

              <template #content>
                <div class="p-0">
                  <div class="flex justify-between items-start mb-4">
                    <div class="flex-1 min-w-0">
                      <h4 class="font-semibold truncate text-base">{{ menu.title }}</h4>
                      <p class="text-sm mt-2 truncate">/{{ menu.slug }}</p>
                    </div>
                    <div class="flex items-center gap-2 opacity-100 transition-opacity ml-4 flex-shrink-0">
                      <Button
                        icon="pi pi-pencil"
                        size="small"
                        :disabled="!pageMenuStore.isLocked"
                        @click.stop="handleEditMenuTitle(menu.id)"
                        text
                        class="p-2"
                      />
                      <Button
                        icon="pi pi-trash"
                        size="small"
                        :disabled="!pageMenuStore.isLocked"
                        @click.stop="confirmDeleteMenu(menu.id)"
                        text
                        class="p-2"
                      />
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                      <Badge
                        :value="menu.is_published ? 'Published' : 'Draft'"
                        :severity="menu.is_published ? 'success' : 'warning'"
                        class="text-xs"
                      />
                      <span class="text-xs">{{ menu.page_data?.length || 0 }} components</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <Button
                        icon="pi pi-arrow-up"
                        size="small"
                        :disabled="isFirstMenu(menu.id) || !pageMenuStore.isLocked"
                        @click.stop="moveMenuUp(menu.id)"
                        text
                        class="p-2"
                      />
                      <Button
                        icon="pi pi-arrow-down"
                        size="small"
                        :disabled="isLastMenu(menu.id) || !pageMenuStore.isLocked"
                        @click.stop="moveMenuDown(menu.id)"
                        text
                        class="p-2"
                      />
                      <span class="text-xs ml-2">{{ menu.order }}</span>
                    </div>
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>
      </div>

      <!-- Mobile Overlay -->
      <div
        v-if="(showLeftPanel || showRightPanel)"
        @click="showLeftPanel = false; showRightPanel = false"
        class="absolute inset-0 z-5 2xl:hidden"
      ></div>
    </div>

    <!-- Add Menu Dialog -->
    <Dialog
      v-model:visible="showAddMenuDialog"
      header="Create New Page"
      :style="{ width: '95vw', maxWidth: '600px' }"
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
                <i class="pi pi-file-plus text-2xl"></i>
              </div>
              <h2 class="text-3xl font-bold mb-4">Create New Page</h2>
              <p>Add a new page to your conference website</p>
            </div>

            <div class="space-y-6">
              <div>
                <label for="newMenuTitle" class="block text-sm font-medium mb-2">Page Title *</label>
                <InputText
                  id="newMenuTitle"
                  v-model="newMenuTitle"
                  autofocus
                  class="w-full"
                  placeholder="Enter page title..."
                />
                <small v-if="titleError" class="mt-2 block">{{ titleError }}</small>
              </div>

              <div>
                <label for="newMenuSlug" class="block text-sm font-medium mb-2">Page Slug</label>
                <InputText
                  id="newMenuSlug"
                  v-model="newMenuSlug"
                  class="w-full"
                  placeholder="Auto-generated from title"
                />
                <small class="mt-2 block">URL-friendly version of the title (auto-generated if left empty)</small>
                <small v-if="slugError" class="mt-2 block">{{ slugError }}</small>
              </div>

              <Card>
                <template #content>
                  <div class="flex items-center p-0">
                    <Checkbox v-model="newMenuPublished" inputId="newMenuPublished" binary />
                    <label for="newMenuPublished" class="ml-4 text-sm font-medium">
                      Publish immediately
                    </label>
                  </div>
                  <p class="text-sm mt-3 ml-8">Published pages are visible to conference attendees</p>
                </template>
              </Card>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelAddMenu"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Create Page"
                icon="pi pi-check"
                @click="handleAddMenu"
                autofocus
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>

    <!-- Edit Menu Dialog -->
    <Dialog
      v-model:visible="showEditMenuDialog"
      header="Edit Page Settings"
      :style="{ width: '95vw', maxWidth: '600px' }"
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
                <i class="pi pi-cog text-2xl"></i>
              </div>
              <h2 class="text-3xl font-bold mb-4">Edit Page Settings</h2>
              <p>Update your page configuration and settings</p>
            </div>

            <div class="space-y-6">
              <div>
                <label for="editMenuTitle" class="block text-sm font-medium mb-2">Page Title *</label>
                <InputText
                  id="editMenuTitle"
                  v-model="editMenuTitle"
                  class="w-full"
                  placeholder="Enter page title..."
                />
                <small v-if="titleError" class="mt-2 block">{{ titleError }}</small>
              </div>

              <div>
                <label for="editMenuSlug" class="block text-sm font-medium mb-2">Page Slug</label>
                <InputText
                  id="editMenuSlug"
                  v-model="editMenuSlug"
                  class="w-full"
                />
                <small class="mt-2 block">URL-friendly version of the title</small>
                <small v-if="slugError" class="mt-2 block">{{ slugError }}</small>
              </div>

              <Card>
                <template #content>
                  <div class="flex items-center p-0">
                    <Checkbox v-model="editMenuPublished" inputId="editMenuPublished" binary />
                    <label for="editMenuPublished" class="ml-4 text-sm font-medium">
                      Published
                    </label>
                  </div>
                  <p class="text-sm mt-3 ml-8">Controls whether this page is visible to attendees</p>
                </template>
              </Card>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelEditMenu"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Save Changes"
                icon="pi pi-check"
                @click="saveEditMenu"
                autofocus
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>

    <!-- Add Component Dialog -->
    <Dialog
      v-model:visible="showAddComponentDialog"
      header="Add New Component"
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
                <i class="pi pi-plus-circle text-2xl"></i>
              </div>
              <h2 class="text-3xl font-bold mb-4">Add New Component</h2>
              <p>Create a new content component for your page</p>
            </div>

            <div class="space-y-6">
              <div>
                <label for="componentName" class="block text-sm font-medium mb-2">Component Name</label>
                <InputText
                  id="componentName"
                  v-model="newComponentName"
                  class="w-full"
                  placeholder="Enter component name..."
                />
              </div>

              <div>
                <label for="componentType" class="block text-sm font-medium mb-2">Component Type</label>
                <Select
                  id="componentType"
                  v-model="newComponentType"
                  :options="availableComponentTypes"
                  option-label="label"
                  option-value="value"
                  placeholder="Select component type"
                  class="w-full"
                  @change="updateComponentData"
                >
                  <template #option="slotProps">
                    <div class="flex items-center gap-2">
                      <i :class="slotProps.option.icon"></i>
                      <span>{{ slotProps.option.label }}</span>
                    </div>
                  </template>
                </Select>
              </div>

              <Card>
                <template #content>
                  <div class="flex items-center p-0">
                    <Checkbox v-model="newComponentPublished" inputId="newComponentPublished" binary />
                    <label for="newComponentPublished" class="ml-4 text-sm font-medium">
                      Publish immediately
                    </label>
                  </div>
                  <p class="text-sm mt-3 ml-8">Published components are visible to attendees</p>
                </template>
              </Card>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelAddComponent"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Add Component"
                icon="pi pi-check"
                @click="handleAddComponent"
                autofocus
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>

    <!-- Confirm Delete Dialog -->
    <Dialog
      v-model:visible="showConfirmDeleteDialog"
      header="Confirm Deletion"
      :style="{ width: '95vw', maxWidth: '500px' }"
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
                <i class="pi pi-exclamation-triangle text-2xl"></i>
              </div>
              <h2 class="text-3xl font-bold mb-4">Confirm Deletion</h2>
              <p>This action cannot be undone</p>
            </div>

            <Card>
              <template #content>
                <p class="text-center p-0">{{ confirmDeleteMessage }}</p>
              </template>
            </Card>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelDelete"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Delete"
                icon="pi pi-trash"
                @click="confirmDelete"
                autofocus
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>

    <!-- Dynamic Component Dialog -->
    <component
      :is="currentComponentEditor"
      v-if="currentComponentEditor"
      v-model:visible="showComponentDialog"
      :component-name="editComponentName"
      :component-data="editComponentData"
      :is-published="editComponentPublished"
      :conference-id="conferenceId"
      @save="handleSaveComponent"
      @cancel="handleCancelComponent"
    />

    <MediaManager
      v-model:visible="showMediaManager"
      :conference-id="conferenceId"
      :is-locked="pageMenuStore.isLocked"
    />

  </div>

</template>

<script lang="ts">
import { defineComponent, ref, shallowRef } from 'vue';
import { usePageMenuStore } from '@/stores/pageMenuStore';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useRoute } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import apiService from '@/services/apiService';
import MediaManager from '@/components/dashboard/PageEditor/MediaManager.vue';
import {
  componentRegistry,
  getAvailableComponentTypes,
  getComponentIcon as getRegistryComponentIcon,
  getComponentDefaultData,
  getComponentDefinition
} from '@/utils/componentRegistry';
import Toast from 'primevue/toast';

export default defineComponent({
  name: 'ConferenceEditor',
  components: {
    MediaManager,
    Toast
  },
  setup() {
    const currentComponentEditor = shallowRef(null);
    const toast = useToast();

    return {
      currentComponentEditor,
      toast
    };
  },
  data() {
    return {
      availableComponentTypes: getAvailableComponentTypes(),

      showAddMenuDialog: false,
      newMenuTitle: '',
      newMenuSlug: '',
      newMenuPublished: false,
      showMediaManager: false,

      showEditMenuDialog: false,
      editMenuId: 0,
      editMenuTitle: '',
      editMenuSlug: '',
      editMenuPublished: false,

      showAddComponentDialog: false,
      newComponentType: '',
      newComponentName: '',
      newComponentData: {} as any,
      newComponentPublished: false,

      // Component editing
      showComponentDialog: false,
      editComponentId: 0,
      editComponentType: '',
      editComponentName: '',
      editComponentData: {} as any,
      editComponentPublished: false,

      titleError: '',
      slugError: '',

      showConfirmDeleteDialog: false,
      deleteType: '',
      deleteId: 0,
      confirmDeleteMessage: '',

      loadingSelectedMenu: false,

      // Mobile panel states
      showLeftPanel: false,
      showRightPanel: false,
    };
  },
  computed: {
    pageMenuStore() {
      return usePageMenuStore();
    },
    conferenceStore() {
      return useConferenceStore();
    },
    conferenceId(): number {
      return Number(this.$route.params.id);
    },
  },
  async created() {
    this.pageMenuStore.setConferenceId(this.conferenceId);
    await this.fetchConferenceData();
    await this.fetchMenus();
  },
  beforeMount() {

  },
  beforeUnmount() {
    this.pageMenuStore.resetState();
  },
  methods: {
    async fetchConferenceData() {
      try {
        await this.conferenceStore.fetchConference(this.conferenceId);
      } catch (error) {
        console.error('Error fetching conference data:', error);
      }
    },
    async fetchMenus() {
      await this.pageMenuStore.fetchMenus();
      this.pageMenuStore.selectedMenu = null;
      if (!this.pageMenuStore.isLocked) {
        await this.checkLock();
      }
    },
    async checkLock() {
      try {
        const response = await apiService.get(`/v1/conference-management/conferences/${this.conferenceId}/lock`);
        if (response.data.payload.is_locked) {
          const currentUser = await apiService.get('/v1/user-management/users/me');
          const userId = currentUser.data.payload.id;
          this.pageMenuStore.lockStatus = {
            isLocked: response.data.payload.lock_info.user_id === userId,
            lockInfo: response.data.payload.lock_info
          };
        }
      } catch (error) {
        console.error('Error checking lock:', error);
      }
    },
    async acquireLock() {
      try {
        const status = await this.pageMenuStore.acquireLock();
        if (!status){
            this.toast.add({
              severity: 'error',
              summary: 'Unable to Acquire Lock',
              detail: 'Failed to enable editing mode. The conference may be locked by another user or there was a connection issue.',
              life: 5000
            });
        }
      } catch (error) {
        console.error('Error acquiring lock:', error);
        
        // Show error toast instead of error card
        this.toast.add({
          severity: 'error',
          summary: 'Unable to Acquire Lock',
          detail: 'Failed to enable editing mode. The conference may be locked by another user or there was a connection issue.',
          life: 5000
        });
      }
    },
    async openLivePreview() {
      try {
        const conference = this.conferenceStore.getConferences.find(c => c.id === this.conferenceId);
        if (!conference || !conference.slug) {
          console.error('Conference not found or missing slug');
          return;
        }

        const previewUrl = `/preview/conferences/${conference.slug}`;

        if (this.pageMenuStore.selectedMenu) {
          const pagePreviewUrl = `/preview/conferences/${conference.slug}/pages/${this.pageMenuStore.selectedMenu.slug}`;
          window.open(pagePreviewUrl, '_blank', 'noopener,noreferrer');
        } else {
          window.open(previewUrl, '_blank', 'noopener,noreferrer');
        }
      } catch (error) {
        console.error('Error opening live preview:', error);
      }
    },
    async handleExit() {
      if (this.pageMenuStore.isLocked) {
        await this.pageMenuStore.releaseLock();
      }
      this.$router.push('/dashboard/conferences');
    },
    generateSlug(title: string): string {
      return title
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .trim();
    },
    getComponentIcon(componentType: string): string {
      return getRegistryComponentIcon(componentType);
    },
    getComponentName(componentType: string): string {
      const definition = getComponentDefinition(componentType);
      return definition?.name || componentType;
    },
    updateComponentData() {
      if (this.newComponentType) {
        this.newComponentData = { ...getComponentDefaultData(this.newComponentType) };
      }
    },
    openAddMenuDialog() {
      this.newMenuTitle = '';
      this.newMenuSlug = '';
      this.newMenuPublished = false;
      this.titleError = '';
      this.slugError = '';
      this.showAddMenuDialog = true;
      this.showLeftPanel = false;
      this.showRightPanel = false;
    },
    cancelAddMenu() {
      this.showAddMenuDialog = false;
    },
    async handleAddMenu() {
      this.titleError = '';
      this.slugError = '';
      if (!this.newMenuTitle.trim()) {
        this.titleError = 'Title is required';
        return;
      }
      const slug = this.newMenuSlug.trim() || this.generateSlug(this.newMenuTitle);
      try {
        const currentMenus = this.pageMenuStore.menus;
        const maxOrder = currentMenus.length > 0
          ? Math.max(...currentMenus.map(m => m.order ?? 0))
          : -1;
        await this.pageMenuStore.createMenu({
          title: this.newMenuTitle.trim(),
          slug: slug,
          is_published: this.newMenuPublished,
          order: maxOrder + 1
        });
        this.showAddMenuDialog = false;
      } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          if (errors.title) this.titleError = errors.title[0];
          if (errors.slug) this.slugError = errors.slug[0];
        }
      }
    },
    handleEditMenuTitle(menuId: number) {
      const menu = this.pageMenuStore.getMenuById(menuId);
      if (!menu) return;
      this.editMenuId = menuId;
      this.editMenuTitle = menu.title;
      this.editMenuSlug = menu.slug;
      this.editMenuPublished = menu.is_published;
      this.titleError = '';
      this.slugError = '';
      this.showEditMenuDialog = true;
      this.showLeftPanel = false;
      this.showRightPanel = false;
    },
    cancelEditMenu() {
      this.showEditMenuDialog = false;
    },
    async saveEditMenu() {
      this.titleError = '';
      this.slugError = '';
      if (!this.editMenuTitle.trim()) {
        this.titleError = 'Title is required';
        return;
      }
      try {
        await this.pageMenuStore.updateMenu(this.editMenuId, {
          title: this.editMenuTitle.trim(),
          slug: this.editMenuSlug.trim(),
          is_published: this.editMenuPublished
        });
        // Re-fetch the selected menu to ensure components load correctly
        if (this.pageMenuStore.selectedMenu?.id === this.editMenuId) {
          await this.pageMenuStore.fetchMenu(this.editMenuId);
        }
        this.showEditMenuDialog = false;
      } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          if (errors.title) this.titleError = errors.title[0];
          if (errors.slug) this.slugError = errors.slug[0];
        }
      }
    },
    async selectMenu(menuId: number) {
      this.loadingSelectedMenu = true;
      this.showLeftPanel = false;
      this.showRightPanel = false;
      try {
        await this.pageMenuStore.fetchMenu(menuId);
      } catch (error) {
        console.error('Error loading menu data:', error);
      } finally {
        this.loadingSelectedMenu = false;
      }
    },
    confirmDeleteMenu(menuId: number) {
      this.deleteType = 'menu';
      this.deleteId = menuId;
      this.confirmDeleteMessage = 'Are you sure you want to delete this page? This action cannot be undone.';
      this.showConfirmDeleteDialog = true;
      this.showLeftPanel = false;
      this.showRightPanel = false;
    },
    openAddComponentDialog() {
      if (!this.pageMenuStore.selectedMenu) return;
      this.newComponentType = 'wysiwyg';
      this.newComponentName = '';
      this.updateComponentData();
      this.newComponentPublished = false;
      this.showAddComponentDialog = true;
      this.showLeftPanel = false;
      this.showRightPanel = false;
    },
    cancelAddComponent() {
      this.showAddComponentDialog = false;
      this.newComponentType = '';
    },
    async handleAddComponent() {
      if (!this.pageMenuStore.selectedMenu || !this.newComponentType) return;
      try {
        const currentComponents = this.pageMenuStore.selectedMenu.page_data || [];
        const maxOrder = currentComponents.length > 0
          ? Math.max(...currentComponents.map(c => c.order))
          : -1;

        await this.pageMenuStore.createPageData(
          this.pageMenuStore.selectedMenu.id,
          {
            component_type: this.newComponentType,
            order: maxOrder + 1,
            tag: this.newComponentName,
            data: this.newComponentData,
            is_published: this.newComponentPublished
          }
        );
        this.showAddComponentDialog = false;
        this.newComponentType = '';
      } catch (error) {
        console.error('Error adding component:', error);
      }
    },
    async editComponent(componentId: number) {
      const component = this.pageMenuStore.selectedMenu?.page_data?.find(c => c.id === componentId);
      if (component) {
        this.editComponentId = componentId;
        this.editComponentType = component.component_type;
        this.editComponentName = component.tag || '';
        this.editComponentData = { ...component.data };
        this.editComponentPublished = component.is_published;

        const definition = getComponentDefinition(component.component_type);
        if (definition?.edit) {
          try {
            const componentModule = await definition.edit();
            this.currentComponentEditor = componentModule.default;
            this.showComponentDialog = true;
          } catch (error) {
            console.error('Error loading component editor:', error);
          }
        }
      }
    },
    handleSaveComponent(componentData: any) {
      this.saveComponent({
        tag: componentData.name,
        data: componentData.data,
        is_published: componentData.isPublished
      });
      this.showComponentDialog = false;
      this.currentComponentEditor = null;
    },
    handleCancelComponent() {
      this.showComponentDialog = false;
      this.currentComponentEditor = null;
    },
    async saveComponent(updateData: any) {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        await this.pageMenuStore.updatePageData(
          this.pageMenuStore.selectedMenu.id,
          this.editComponentId,
          updateData
        );
      } catch (error) {
        console.error('Error updating component:', error);
      }
    },
    confirmDeleteComponent(componentId: number) {
      this.deleteType = 'component';
      this.deleteId = componentId;
      this.confirmDeleteMessage = 'Are you sure you want to delete this component? This action cannot be undone.';
      this.showConfirmDeleteDialog = true;
    },
    cancelDelete() {
      this.showConfirmDeleteDialog = false;
      this.deleteType = '';
      this.deleteId = 0;
    },
    async confirmDelete() {
      if (this.deleteType === 'menu') {
        await this.pageMenuStore.deleteMenu(this.deleteId);
      } else if (this.deleteType === 'component' && this.pageMenuStore.selectedMenu) {
        await this.pageMenuStore.deletePageData(
          this.pageMenuStore.selectedMenu.id,
          this.deleteId
        );
      }
      this.showConfirmDeleteDialog = false;
      this.deleteType = '';
      this.deleteId = 0;
    },
    async moveMenuUp(menuId: number) {
      try {
        await this.pageMenuStore.moveMenuUp(menuId);
      } catch (error) {
        console.error('Error moving menu up:', error);
      }
    },
    async moveMenuDown(menuId: number) {
      try {
        await this.pageMenuStore.moveMenuDown(menuId);
      } catch (error) {
        console.error('Error moving menu down:', error);
      }
    },
    async moveComponentUp(componentId: number) {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        await this.pageMenuStore.movePageDataUp(this.pageMenuStore.selectedMenu.id, componentId);
      } catch (error) {
        console.error('Error moving component up:', error);
      }
    },
    async moveComponentDown(componentId: number) {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        await this.pageMenuStore.movePageDataDown(this.pageMenuStore.selectedMenu.id, componentId);
      } catch (error) {
        console.error('Error moving component down:', error);
      }
    },
    isFirstComponent(componentId: number): boolean {
      if (!this.pageMenuStore.selectedMenu || !this.pageMenuStore.selectedMenu.page_data) return false;
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (components.length === 0) return false;
      return componentId === components[0].id;
    },
    isLastComponent(componentId: number): boolean {
      if (!this.pageMenuStore.selectedMenu || !this.pageMenuStore.selectedMenu.page_data) return false;
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (components.length === 0) return false;
      return componentId === components[components.length - 1].id;
    },
    isFirstMenu(menuId: number): boolean {
      const menus = this.pageMenuStore.menus;
      if (menus.length === 0) return false;
      return menuId === menus[0].id;
    },
    isLastMenu(menuId: number): boolean {
      const menus = this.pageMenuStore.menus;
      if (menus.length === 0) return false;
      return menuId === menus[menus.length - 1].id;
    }
  }
});
</script>