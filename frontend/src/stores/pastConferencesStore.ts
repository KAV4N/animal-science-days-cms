import { defineStore } from 'pinia';
import { type Conference } from '@/types';

interface PastConferenceState {
  conferences: Conference[];
  loading: boolean;
  error: string | null;
  decades: string[];
  filters: {
    search: string;
    decade: string | null;
    university: string | null;
  };
}

export const usePastConferencesStore = defineStore('pastConferences', {
  state: (): PastConferenceState => ({
    conferences: [],
    loading: false,
    error: null,
    decades: ['2020s', '2010s', '2000s', '1990s'],
    filters: {
      search: '',
      decade: null,
      university: null
    }
  }),
  
  getters: {
    // Get the most recent conferences (limited to a specific count)
    recentConferences: (state) => (count: number = 3) => {
      return [...state.conferences]
        .sort((a, b) => new Date(b.startDate || 0).getTime() - new Date(a.startDate || 0).getTime())
        .slice(0, count);
    },
    
    // Get all universities from conferences
    availableUniversities: (state) => {
      const universities = state.conferences
        .map(conf => conf.university?.name)
        .filter((name): name is string => !!name);
      
      return [...new Set(universities)].sort();
    },
    
    // Get filtered conferences based on current filters
    filteredConferences: (state) => {
      let result = [...state.conferences];
      
      // Filter by search term
      if (state.filters.search) {
        const searchTerm = state.filters.search.toLowerCase();
        result = result.filter(conf => 
          conf.name.toLowerCase().includes(searchTerm) ||
          conf.description.toLowerCase().includes(searchTerm) ||
          conf.location.toLowerCase().includes(searchTerm) ||
          conf.university?.name.toLowerCase().includes(searchTerm)
        );
      }
      
      // Filter by decade
      if (state.filters.decade) {
        const decade = parseInt(state.filters.decade.substring(0, 4));
        result = result.filter(conf => {
          const year = new Date(conf.startDate || 0).getFullYear();
          return year >= decade && year < (decade + 10);
        });
      }
      
      // Filter by university
      if (state.filters.university) {
        result = result.filter(conf => 
          conf.university?.name === state.filters.university
        );
      }
      
      return result;
    },
    
    //Group conferences by decade
    conferencesByDecade: (state) => {
      const grouped: Record<string, Conference[]> = {};
      
      state.decades.forEach(decade => {
        const startYear = parseInt(decade.substring(0, 4));
        const endYear = startYear + 9;
        
        const confsInDecade = state.conferences.filter(conf => {
          const year = new Date(conf.startDate || 0).getFullYear();
          return year >= startYear && year <= endYear;
        });
        
        grouped[decade] = confsInDecade;
      });
      
      return grouped;
    },
    
    // Get conference by slug
    getConferenceBySlug: (state) => (slug: string) => {
      return state.conferences.find(conf => conf.slug === slug);
    }
  },
  
  actions: {
    // Fetch past conferences
    async fetchConferences() {
      this.loading = true;
      this.error = null;
      
      try {
        // Mock data for past conferences
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const currentYear = new Date().getFullYear();
        
        // Generate conferences for multiple decades
        const mockConferences: Conference[] = [];
        
        // Helper to generate conferences for a specific year
        const generateForYear = (year: number, count: number = 3) => {
          for (let i = 0; i < count; i++) {
            const id = `conf-${year}-${i}`;
            const month = Math.floor(Math.random() * 12);
            const day = Math.floor(Math.random() * 28) + 1;
            const startDate = new Date(year, month, day);
            
            // End date is 2-5 days after start date
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + Math.floor(Math.random() * 4) + 2);
            
            const universityIndex = Math.floor(Math.random() * 4);
            const universities = [
              { id: 'u1', name: 'University of Technology' },
              { id: 'u2', name: 'National Science University' },
              { id: 'u3', name: 'International University' },
              { id: 'u4', name: 'Technical University' }
            ];
            
            const topics = [
              'Computer Science', 'Artificial Intelligence', 'Biotechnology',
              'Environmental Science', 'Data Science', 'Quantum Computing',
              'Molecular Biology', 'Renewable Energy', 'Machine Learning',
              'Cybersecurity', 'Robotics', 'Nanotechnology'
            ];
            
            const randomTopic = topics[Math.floor(Math.random() * topics.length)];
            
            mockConferences.push({
              id,
              name: `${randomTopic} Conference ${year}`,
              slug: `${randomTopic.toLowerCase().replace(/\s+/g, '-')}-${year}`,
              title: `${randomTopic} ${year}`,
              description: `Annual conference on advances in ${randomTopic.toLowerCase()} research and applications.`,
              location: ['New York', 'London', 'Tokyo', 'Paris', 'Berlin', 'Sydney'][Math.floor(Math.random() * 6)],
              startDate,
              endDate,
              university: universities[universityIndex],
              isPublished: true,
              primaryColor: '#3B82F6',
              secondaryColor: '#10B981',
              editors: [],
              createdAt: new Date(year, 0, 1),
              updatedAt: new Date(year, 0, 1)
            });
          }
        };
        
        // Generate past conferences for multiple decades
        for (let year = currentYear - 1; year >= currentYear - 35; year--) {
          const count = year > currentYear - 10 ? 5 : (year > currentYear - 20 ? 3 : 2);
          generateForYear(year, count);
        }
        
        this.conferences = mockConferences;
      } catch (error: any) {
        this.error = error.message || 'Failed to fetch conferences';
      } finally {
        this.loading = false;
      }
    },
    
    // Set search filter
    setSearchFilter(term: string) {
      this.filters.search = term;
    },
    
    // Set decade filter
    setDecadeFilter(decade: string | null) {
      this.filters.decade = decade;
    },
    
    // Set university filter
    setUniversityFilter(university: string | null) {
      this.filters.university = university;
    },
    
    // Reset all filters
    resetFilters() {
      this.filters = {
        search: '',
        decade: null,
        university: null
      };
    }
  }
});