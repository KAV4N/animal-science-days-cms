<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="New Conference" icon="pi pi-plus" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" outlined @click="confirmDeleteSelected" :disabled="!selectedConferences || !selectedConferences.length" />
                </template>

                <template #end>
                    <FileUpload mode="basic" accept=".json" :maxFileSize="1000000" label="Import" customUpload chooseLabel="Import" class="mr-2" auto :chooseButtonProps="{ severity: 'secondary' }" />
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedConferences"
                :value="conferences"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} conferences"
            >
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Conferences</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
                <Column field="slug" header="Slug" sortable style="min-width: 12rem"></Column>
                <Column field="year" header="Year" sortable style="min-width: 6rem"></Column>
                <Column field="location" header="Location" sortable style="min-width: 12rem"></Column>
                <Column field="isPublished" header="Status" sortable style="min-width: 8rem">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.isPublished ? 'Published' : 'Draft'" :severity="getStatusLabel(slotProps.data.isPublished)" />
                    </template>
                </Column>
                <Column field="updatedAt" header="Updated" sortable style="min-width: 10rem">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.updatedAt) }}
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editConference(slotProps.data)" />
                        <Button icon="pi pi-cog" outlined rounded class="mr-2" @click="manageSubpages(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteConference(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="conferenceDialog" :style="{ width: '650px' }" header="Conference Details" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3">Conference Name</label>
                    <InputText id="name" v-model.trim="conference.name" required="true" autofocus :invalid="submitted && !conference.name" fluid />
                    <small v-if="submitted && !conference.name" class="text-red-500">Conference name is required.</small>
                </div>
                <div>
                    <label for="slug" class="block font-bold mb-3">Slug (URL)</label>
                    <InputText id="slug" v-model.trim="conference.slug" required="true" :invalid="submitted && !conference.slug" fluid />
                    <small v-if="submitted && !conference.slug" class="text-red-500">Slug is required.</small>
                </div>
                <div>
                    <label for="title" class="block font-bold mb-3">Title</label>
                    <InputText id="title" v-model.trim="conference.title" fluid />
                </div>
                <div>
                    <label for="location" class="block font-bold mb-3">Location</label>
                    <InputText id="location" v-model.trim="conference.location" fluid />
                </div>
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-6">
                        <label for="year" class="block font-bold mb-3">Year</label>
                        <InputNumber id="year" v-model="conference.year" integeronly fluid />
                    </div>
                    <div class="col-span-6">
                        <label for="isPublished" class="block font-bold mb-3">Status</label>
                        <div class="flex items-center gap-2">
                            <InputSwitch v-model="conference.isPublished" />
                            <span>{{ conference.isPublished ? 'Published' : 'Draft' }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-6">
                        <label for="primaryColor" class="block font-bold mb-3">Primary Color</label>
                        <ColorPicker v-model="conference.primaryColor" />
                    </div>
                    <div class="col-span-6">
                        <label for="secondaryColor" class="block font-bold mb-3">Secondary Color</label>
                        <ColorPicker v-model="conference.secondaryColor" />
                    </div>
                </div>

                <div>
                    <label for="editors" class="block font-bold mb-3">Editors</label>
                    <MultiSelect id="editors" v-model="conference.editors" :options="availableEditors" 
                                optionLabel="name" placeholder="Select Editors" display="chip" fluid>
                        <template #option="slotProps">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-user"></i>
                                <div>{{ slotProps.option.name }}</div>
                            </div>
                        </template>
                    </MultiSelect>
                </div>

                <div>
                    <label for="additionalFields" class="block font-bold mb-3">Additional Fields</label>
                    <div v-for="(field, index) in conference.additionalFields" :key="index" class="flex gap-2 mb-2">
                        <InputText v-model="field.key" placeholder="Key" class="w-1/2" />
                        <InputText v-model="field.value" placeholder="Value" class="w-1/2" />
                        <Button icon="pi pi-times" outlined rounded severity="danger" @click="removeAdditionalField(index)" />
                    </div>
                    <Button label="Add Field" icon="pi pi-plus" text @click="addAdditionalField" class="p-0" />
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveConference" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteConferenceDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="conference"
                    >Are you sure you want to delete <b>{{ conference.name }}</b
                    >?</span
                >
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteConferenceDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteConference" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteConferencesDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span>Are you sure you want to delete the selected conferences?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteConferencesDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedConferences" />
            </template>
        </Dialog>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';

interface Editor {
    id: string;
    name: string;
    email: string;
}

interface AdditionalField {
    key: string;
    value: string;
}

interface Conference {
    id?: string;
    name?: string;
    slug?: string;
    title?: string;
    location?: string;
    year?: number;
    isPublished?: boolean;
    primaryColor?: string;
    secondaryColor?: string;
    editors?: Editor[];
    additionalFields?: AdditionalField[];
    createdAt?: Date;
    updatedAt?: Date;
}

export default defineComponent({
    data() {
        return {
            conferences: [] as Conference[],
            conferenceDialog: false,
            deleteConferenceDialog: false,
            deleteConferencesDialog: false,
            conference: {} as Conference,
            selectedConferences: null as Conference[] | null,
            filters: {} as any,
            submitted: false,
            availableEditors: [
                { id: '1', name: 'John Doe', email: 'john@example.com' },
                { id: '2', name: 'Jane Smith', email: 'jane@example.com' },
                { id: '3', name: 'Mike Johnson', email: 'mike@example.com' },
                { id: '4', name: 'Sarah Williams', email: 'sarah@example.com' },
                { id: '5', name: 'David Brown', email: 'david@example.com' }
            ] as Editor[],
            conferenceService: {
                getConferencesData(): Conference[] {
                    return [
                        {
                            id: '1',
                            name: 'International Conference on Computer Science',
                            slug: 'iccs-2025',
                            title: 'ICCS 2025',
                            location: 'Boston, MA',
                            year: 2025,
                            isPublished: true,
                            primaryColor: '#4CAF50',
                            secondaryColor: '#2196F3',
                            editors: [
                                { id: '1', name: 'John Doe', email: 'john@example.com' },
                                { id: '2', name: 'Jane Smith', email: 'jane@example.com' }
                            ],
                            additionalFields: [
                                { key: 'registration_date', value: '2025-02-15' },
                                { key: 'submission_deadline', value: '2024-12-31' }
                            ],
                            createdAt: new Date('2024-03-15'),
                            updatedAt: new Date('2024-04-01')
                        },
                        {
                            id: '2',
                            name: 'Artificial Intelligence Summit',
                            slug: 'ai-summit-2025',
                            title: 'AI Summit 2025',
                            location: 'San Francisco, CA',
                            year: 2025,
                            isPublished: true,
                            primaryColor: '#9C27B0',
                            secondaryColor: '#FF9800',
                            editors: [
                                { id: '3', name: 'Mike Johnson', email: 'mike@example.com' }
                            ],
                            additionalFields: [
                                { key: 'registration_date', value: '2025-03-10' },
                                { key: 'submission_deadline', value: '2025-01-15' }
                            ],
                            createdAt: new Date('2024-03-20'),
                            updatedAt: new Date('2024-03-25')
                        },
                        {
                            id: '3',
                            name: 'Web Development Conference',
                            slug: 'webdev-2025',
                            title: 'WebDev 2025',
                            location: 'Chicago, IL',
                            year: 2025,
                            isPublished: false,
                            primaryColor: '#F44336',
                            secondaryColor: '#3F51B5',
                            editors: [
                                { id: '4', name: 'Sarah Williams', email: 'sarah@example.com' },
                                { id: '5', name: 'David Brown', email: 'david@example.com' }
                            ],
                            additionalFields: [
                                { key: 'registration_date', value: '2025-04-01' },
                                { key: 'submission_deadline', value: '2025-02-28' }
                            ],
                            createdAt: new Date('2024-03-25'),
                            updatedAt: new Date('2024-04-05')
                        }
                    ];
                },
                getConferences(): Promise<Conference[]> {
                    return Promise.resolve(this.getConferencesData());
                }
            }
        };
    },
    created() {
        this.initFilters();
    },
    mounted() {
        this.conferenceService.getConferences().then((data) => (this.conferences = data));
    },
    methods: {
        formatDate(value: Date): string {
            if (value) {
                return new Date(value).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit'
                });
            }
            return '';
        },
        openNew() {
            this.conference = {
                isPublished: false,
                year: new Date().getFullYear(),
                primaryColor: '#4CAF50',
                secondaryColor: '#2196F3',
                editors: [],
                additionalFields: []
            };
            this.submitted = false;
            this.conferenceDialog = true;
        },
        hideDialog() {
            this.conferenceDialog = false;
            this.submitted = false;
        },
        saveConference() {
            this.submitted = true;

            if (this.conference?.name?.trim() && this.conference?.slug?.trim()) {
                const now = new Date();
                
                if (this.conference.id) {
                    this.conference.updatedAt = now;
                    this.conferences[this.findIndexById(this.conference.id)] = this.conference;
                    this.$toast.add({severity:'success', summary: 'Successful', detail: 'Conference Updated', life: 3000});
                }
                else {
                    this.conference.id = this.createId();
                    this.conference.createdAt = now;
                    this.conference.updatedAt = now;
                    this.conferences.push(this.conference);
                    this.$toast.add({severity:'success', summary: 'Successful', detail: 'Conference Created', life: 3000});
                }

                this.conferenceDialog = false;
                this.conference = {} as Conference;
            }
        },
        editConference(conference: Conference) {
            this.conference = { ...conference };
            // Ensure additionalFields array exists
            if (!this.conference.additionalFields) {
                this.conference.additionalFields = [];
            }
            this.conferenceDialog = true;
        },
        manageSubpages(conference: Conference) {
            // This would navigate to the subpages management for this conference
            // For now, just show a toast notification since this is a future feature
            this.$toast.add({
                severity: 'info', 
                summary: 'Subpage Management', 
                detail: `Subpage management for ${conference.name} will be available in a future update`, 
                life: 3000
            });
        },
        confirmDeleteConference(conference: Conference) {
            this.conference = conference;
            this.deleteConferenceDialog = true;
        },
        deleteConference() {
            this.conferences = this.conferences.filter(val => val.id !== this.conference.id);
            this.deleteConferenceDialog = false;
            this.conference = {} as Conference;
            this.$toast.add({severity:'success', summary: 'Successful', detail: 'Conference Deleted', life: 3000});
        },
        findIndexById(id: string): number {
            let index = -1;
            for (let i = 0; i < this.conferences.length; i++) {
                if (this.conferences[i].id === id) {
                    index = i;
                    break;
                }
            }
            return index;
        },
        createId(): string {
            let id = '';
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            for (let i = 0; i < 8; i++) {
                id += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return id;
        },
        exportCSV() {
            (this.$refs.dt as any).exportCSV();
        },
        confirmDeleteSelected() {
            this.deleteConferencesDialog = true;
        },
        deleteSelectedConferences() {
            if (this.selectedConferences) {
                this.conferences = this.conferences.filter(val => !this.selectedConferences?.includes(val));
                this.deleteConferencesDialog = false;
                this.selectedConferences = null;
                this.$toast.add({severity:'success', summary: 'Successful', detail: 'Conferences Deleted', life: 3000});
            }
        },
        initFilters() {
            this.filters = {
                'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
            };
        },
        getStatusLabel(isPublished: boolean): string {
            return isPublished ? 'success' : 'warning';
        },
        addAdditionalField() {
            if (!this.conference.additionalFields) {
                this.conference.additionalFields = [];
            }
            this.conference.additionalFields.push({ key: '', value: '' });
        },
        removeAdditionalField(index: number) {
            if (this.conference.additionalFields) {
                this.conference.additionalFields.splice(index, 1);
            }
        }
    }
});
</script>