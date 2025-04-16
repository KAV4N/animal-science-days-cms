// stores/conferenceStore.ts
import { defineStore } from 'pinia';
import { type Conference, type Editor, type University } from '@/types';

export const useConferenceStore = defineStore('conference', {
  state: () => ({
    conferences: [] as Conference[],
    selectedConferences: [] as Conference[],
    currentConference: null as Conference | null,
    conferenceDialog: false,
    deleteConferenceDialog: false,
    deleteConferencesDialog: false,
    editorSelectorDialog: false,
    activeTabIndex: "0",
    submitted: false,
    universities: [
      { id: 'u1', name: 'University of Technology' },
      { id: 'u2', name: 'National Science University' },
      { id: 'u3', name: 'International University' },
      { id: 'u4', name: 'Technical University' }
    ] as University[],
    editors: [
      { id: 'e1', name: 'Emma Johnson', email: 'emma.j@example.com', role: 'editor' },
      { id: 'e2', name: 'Michael Chen', email: 'michael.c@example.com', role: 'editor' },
      { id: 'e3', name: 'Sophia Rodriguez', email: 'sophia.r@example.com', role: 'editor' },
      { id: 'e4', name: 'James Wilson', email: 'james.w@example.com', role: 'editor' },
      { id: 'e5', name: 'Olivia Smith', email: 'olivia.s@example.com', role: 'editor' },
      { id: 'e6', name: 'David Brown', email: 'david.b@example.com', role: 'editor' },
      { id: 'e7', name: 'Emily Davis', email: 'emily.d@example.com', role: 'editor' },
      { id: 'e8', name: 'William Jones', email: 'william.j@example.com', role: 'editor' },
      { id: 'e9', name: 'Amy Martin', email: 'amy.m@example.com', role: 'editor' },
      { id: 'e10', name: 'Robert Taylor', email: 'robert.t@example.com', role: 'editor' }
    ] as Editor[],
    loading: false,
    selectedEditorsForDialog: [] as Editor[]
  }),
  
  getters: {
    getConferenceById: (state) => (id: string) => {
      return state.conferences.find(conf => conf.id === id);
    },
    
    availableEditors: (state) => {
      if (state.currentConference) {
        const currentEditorIds = state.currentConference.editors.map(e => e.id);
        return state.editors.filter(editor => !currentEditorIds.includes(editor.id));
      }
      return state.editors;
    }
  },
  
  actions: {
    async fetchConferences() {
      this.loading = true;
      try {
        // Mock API call with setTimeout
        const mockData = [
          {
            id: 'c1',
            name: 'International Conference on Computer Science',
            slug: 'iccs-2025',
            title: 'ICCS 2025',
            description: 'A premier forum for presenting the latest research and development in computer science and related fields.',
            location: 'Berlin, Germany',
            startDate: new Date(2025, 5, 15),
            endDate: new Date(2025, 5, 18),
            university: { id: 'u1', name: 'University of Technology' },
            isPublished: true,
            primaryColor: '#3B82F6',
            secondaryColor: '#10B981',
            editors: [
              { id: 'e1', name: 'Emma Johnson', email: 'emma.j@example.com', role: 'editor' }
            ],
            createdAt: new Date(2024, 10, 5),
            updatedAt: new Date(2025, 0, 10)
          },
          {
            id: 'c2',
            name: 'Advanced Machine Learning Symposium',
            slug: 'amls-2025',
            title: 'AMLS 2025',
            description: 'Focused on the latest advances in machine learning theory, algorithms, and applications with leading researchers.',
            location: 'Tokyo, Japan',
            startDate: new Date(2025, 7, 10),
            endDate: new Date(2025, 7, 12),
            university: { id: 'u2', name: 'National Science University' },
            isPublished: true,
            primaryColor: '#3B82F6',
            secondaryColor: '#10B981',
            editors: [
              { id: 'e2', name: 'Michael Chen', email: 'michael.c@example.com', role: 'editor' },
              { id: 'e3', name: 'Sophia Rodriguez', email: 'sophia.r@example.com', role: 'editor' }
            ],
            createdAt: new Date(2024, 11, 15),
            updatedAt: new Date(2025, 1, 20)
          },
          {
            id: 'c3',
            name: 'Web Technologies Conference',
            slug: 'wtc-2025',
            title: 'WTC 2025',
            description: 'Exploring the latest technologies, trends, and best practices in web development and design.',
            location: 'San Francisco, USA',
            startDate: new Date(2025, 9, 5),
            endDate: new Date(2025, 9, 8),
            university: { id: 'u3', name: 'International University' },
            isPublished: false,
            primaryColor: '#3B82F6',
            secondaryColor: '#10B981',
            editors: [],
            createdAt: new Date(2025, 0, 25),
            updatedAt: new Date(2025, 0, 25)
          }
        ];
        
        // Simulate network delay
        await new Promise(resolve => setTimeout(resolve, 500));
        
        this.conferences = mockData;
      } catch (error) {
        console.error('Error fetching conferences:', error);
      } finally {
        this.loading = false;
      }
    },
    
    openNewConference() {
      this.currentConference = this.getEmptyConference();
      this.conferenceDialog = true;
      this.submitted = false;
      this.activeTabIndex = "0";
    },
    
    editConference(conference: Conference) {
      this.currentConference = { ...conference };
      this.conferenceDialog = true;
      this.submitted = false;
      this.activeTabIndex = "0";
    },
    
    hideConferenceDialog() {
      this.conferenceDialog = false;
      this.submitted = false;
    },
    
    confirmDeleteConference(conference: Conference) {
      this.currentConference = conference;
      this.deleteConferenceDialog = true;
    },
    
    confirmDeleteSelected() {
      this.deleteConferencesDialog = true;
    },
    
    validateConference(): boolean {
      this.submitted = true;
      
      if (!this.currentConference) return false;
      
      const conf = this.currentConference;
      
      if (!conf.name?.trim()) {
        this.activeTabIndex = "0";
        return false;
      }
      
      if (!conf.slug?.trim()) {
        this.activeTabIndex = "0";
        return false;
      }
      
      if (!conf.title?.trim()) {
        this.activeTabIndex = "0";
        return false;
      }
      
      if (!conf.university) {
        this.activeTabIndex = "0";
        return false;
      }
      
      if (!conf.location?.trim()) {
        this.activeTabIndex = "1";
        return false;
      }
      
      if (!conf.startDate) {
        this.activeTabIndex = "1";
        return false;
      }
      
      if (!conf.endDate) {
        this.activeTabIndex = "1";
        return false;
      }
      
      if (conf.startDate > conf.endDate) {
        this.activeTabIndex = "1";
        return false;
      }
      
      return true;
    },
    
    saveConference() {
      if (!this.validateConference() || !this.currentConference) return false;
      
      const now = new Date();
      
      if (this.currentConference.id) {
        // Update existing conference
        const index = this.conferences.findIndex(c => c.id === this.currentConference!.id);
        if (index !== -1) {
          const updatedConference = {
            ...this.currentConference,
            updatedAt: now
          };
          this.conferences[index] = updatedConference;
        }
      } else {
        // Add new conference
        const newConference = {
          ...this.currentConference,
          id: this.generateId(),
          createdAt: now,
          updatedAt: now
        };
        this.conferences.push(newConference);
      }
      
      this.conferenceDialog = false;
      this.submitted = false;
      return true;
    },
    
    getEmptyConference(): Conference {
      return {
        id: '',
        name: '',
        slug: '',
        title: '',
        description: '',
        location: '',
        venueDetails: '',
        startDate: null,
        endDate: null,
        university: null,
        isPublished: false,
        primaryColor: '#3B82F6',
        secondaryColor: '#10B981',
        editors: [],
        createdAt: new Date(),
        updatedAt: new Date()
      };
    },
    
    deleteConference(id: string) {
      this.conferences = this.conferences.filter(c => c.id !== id);
      if (this.currentConference && this.currentConference.id === id) {
        this.currentConference = null;
      }
      this.deleteConferenceDialog = false;
    },
    
    deleteSelectedConferences() {
      if (this.selectedConferences.length) {
        const ids = this.selectedConferences.map(c => c.id);
        this.conferences = this.conferences.filter(c => !ids.includes(c.id));
        this.selectedConferences = [];
      }
      this.deleteConferencesDialog = false;
    },
    
    generateId(): string {
      let id = '';
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      for (let i = 0; i < 8; i++) {
        id += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return id;
    },
    
    // Editor management
    openEditorSelector() {
      this.selectedEditorsForDialog = [];
      this.editorSelectorDialog = true;
    },
    
    closeEditorSelector() {
      this.editorSelectorDialog = false;
    },
    
    addSelectedEditors() {
      if (!this.currentConference || !this.selectedEditorsForDialog.length) return;
      
      const updatedConference = { ...this.currentConference };
      
      // Add selected editors without duplicates
      this.selectedEditorsForDialog.forEach(editor => {
        if (!updatedConference.editors.some(e => e.id === editor.id)) {
          updatedConference.editors.push(editor);
        }
      });
      
      this.currentConference = updatedConference;
      this.editorSelectorDialog = false;
    },
    
    removeEditor(index: number) {
      if (!this.currentConference) return;
      
      if (index >= 0 && index < this.currentConference.editors.length) {
        const updatedConference = { ...this.currentConference };
        updatedConference.editors.splice(index, 1);
        this.currentConference = updatedConference;
      }
    },
    
    // Theme colors
    updateConferenceColors(primaryColor: string, secondaryColor: string) {
      if (this.currentConference) {
        this.currentConference = {
          ...this.currentConference,
          primaryColor,
          secondaryColor
        };
      }
    },
    
    setPublishedStatus(status: boolean) {
      if (this.currentConference) {
        this.currentConference = {
          ...this.currentConference,
          isPublished: status
        };
      }
    },
    
    setActiveTab(tabIndex: string) {
      this.activeTabIndex = tabIndex;
    }
  }
});