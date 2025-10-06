/**
 * API Service
 * Handles all HTTP requests to the backend
 * Provides a centralized way to make API calls
 */
class APIService {
    constructor() {
        this.basePath = this.getBasePath();
    }

    /**
     * Get base path from current URL
     */
    getBasePath() {
        const path = window.location.pathname;
        const parts = path.split('/').filter(p => p);
        
        // Find the base path up to /admin, /faculty, or /student
        let baseParts = [];
        for (let i = 0; i < parts.length; i++) {
            baseParts.push(parts[i]);
            if (parts[i] === 'admin' || parts[i] === 'faculty' || parts[i] === 'student') {
                break;
            }
        }
        
        // If we're in a nested structure like /exam-main/public/admin/dashboard
        // we want to return /exam-main/public/admin
        return '/' + baseParts.join('/');
    }

    /**
     * Make GET request
     */
    async get(endpoint) {
        try {
            const url = this.basePath + endpoint;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('GET request failed:', error);
            throw error;
        }
    }

    /**
     * Make POST request
     */
    async post(endpoint, data) {
        try {
            const url = this.basePath + endpoint;
            console.log('🔵 POST Request:', url, data);
            
            // Convert data to FormData if it's an object
            let body;
            if (data instanceof FormData) {
                body = data;
            } else {
                body = new FormData();
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        // Skip null/undefined values
                        if (data[key] !== null && data[key] !== undefined) {
                            body.append(key, data[key]);
                        }
                    }
                }
            }

            console.log('📤 FormData entries:', Array.from(body.entries()));

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('POST request failed:', error);
            throw error;
        }
    }

    /**
     * Make PUT request
     */
    async put(endpoint, data) {
        try {
            const url = this.basePath + endpoint;
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('PUT request failed:', error);
            throw error;
        }
    }

    /**
     * Make DELETE request
     */
    async delete(endpoint) {
        try {
            const url = this.basePath + endpoint;
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('DELETE request failed:', error);
            throw error;
        }
    }

    /**
     * Handle API response
     * Deals with both JSON and HTML responses (for redirects)
     */
    async handleResponse(response) {
        // Check if response was redirected (HTML returned)
        if (response.redirected) {
            return {
                success: true,
                redirected: true,
                status: 'success'
            };
        }

        // Get response text first
        const text = await response.text();

        // Check if response is HTML (server redirected)
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
            return {
                success: true,
                redirected: true,
                status: 'success'
            };
        }

        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            // If not JSON, return as text
            return {
                success: response.ok,
                data: text,
                status: response.ok ? 'success' : 'error'
            };
        }
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APIService;
} else {
    window.APIService = APIService;
}
