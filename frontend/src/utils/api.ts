import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost/api',
    withCredentials: true, // Important for cookies to be sent with requests
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Request interceptor to handle errors
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Handle unauthorized error (redirect to login, etc.)
            console.error('Unauthorized access');
            // You might want to redirect to login page or dispatch a logout action
        }
        return Promise.reject(error);
    }
);

// Function to get CSRF token before making requests that require authentication
export const getCsrfToken = async () => {
    await axios.get('http://localhost/sanctum/csrf-cookie', {
        withCredentials: true
    });
};

export default api;