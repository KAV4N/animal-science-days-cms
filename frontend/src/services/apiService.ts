import {api} from '@/plugins/axios';

export const apiService = {
  auth: {
    getCsrfCookie() {
      return api.get('/api/csrf-cookie');
    },
    
    login(email: string, password: string) {
      return this.getCsrfCookie().then(() => {
        return api.post('/api/auth/spa/login', { email, password });
      });
    },
    
    register(name: string, email: string, password: string, password_confirmation: string) {
      return this.getCsrfCookie().then(() => {
        return api.post('/api/auth/spa/register', { 
          name, 
          email, 
          password, 
          password_confirmation 
        });
      });
    },
    
    logout() {
      return api.post('/api/auth/spa/logout');
    },
    
    getCurrentUser() {
      return api.get('/api/user');
    }
  },
  
  access: {
    //TODO: test routes remove in future or update
    checkEditor() {
      return api.get('/api/access/editor');
    },
    
    checkAdmin() {
      return api.get('/api/access/admin');
    },
    
    checkSuperAdmin() {
      return api.get('/api/access/super-admin');
    },
    
    check(type: string) {
      return api.get(`/api/access/${type}`);
    }
  },
  
  get(url: string, config = {}) {
    return api.get(url, config);
  },
  
  post(url: string, data = {}, config = {}) {
    return api.post(url, data, config);
  },
  
  put(url: string, data = {}, config = {}) {
    return api.put(url, data, config);
  },
  
  delete(url: string, config = {}) {
    return api.delete(url, config);
  }
};


export default apiService;