/**
 * ApiClient - Centralized API communication layer
 * Core service - Handles all HTTP requests
 */
class ApiClient {
    constructor() {
        this.baseUrl = this.getBaseUrl();
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    /**
     * Get base URL for API requests
     */
    getBaseUrl() {
        // Return empty string to use absolute paths from root
        return '';
    }

    /**
     * Generic request handler
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            ...options,
            headers: {
                ...this.defaultHeaders,
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, config);
            
            // Handle HTTP errors
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            // Parse JSON response
            const data = await response.json();
            return data;
            
        } catch (error) {
            console.error('API Request Error:', error);
            throw this.handleError(error);
        }
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        
        return await this.request(url, {
            method: 'GET'
        });
    }

    /**
     * POST request with JSON body
     */
    async post(endpoint, data = {}) {
        return await this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * POST request with FormData
     */
    async postFormData(endpoint, formData) {
        return await this.request(endpoint, {
            method: 'POST',
            headers: {}, // Let browser set Content-Type for FormData
            body: formData
        });
    }

    /**
     * POST request with URL-encoded data (legacy support)
     */
    async postUrlEncoded(endpoint, data = {}) {
        return await this.request(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data).toString()
        });
    }

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return await this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * PATCH request
     */
    async patch(endpoint, data = {}) {
        return await this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(endpoint, data = {}) {
        return await this.request(endpoint, {
            method: 'DELETE',
            body: JSON.stringify(data)
        });
    }

    /**
     * Handle API errors
     */
    handleError(error) {
        // Network error
        if (error instanceof TypeError && error.message.includes('fetch')) {
            return new Error('Network error. Please check your connection.');
        }

        // HTTP error
        if (error.message.includes('HTTP')) {
            return new Error(`Server error: ${error.message}`);
        }

        // Generic error
        return error;
    }

    /**
     * Set authorization token
     */
    setAuthToken(token) {
        this.defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    /**
     * Clear authorization token
     */
    clearAuthToken() {
        delete this.defaultHeaders['Authorization'];
    }

    /**
     * Upload file
     */
    async uploadFile(endpoint, file, additionalData = {}) {
        const formData = new FormData();
        formData.append('file', file);
        
        // Add additional data
        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });

        return await this.postFormData(endpoint, formData);
    }

    /**
     * Download file
     */
    async downloadFile(endpoint, filename) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`);
            const blob = await response.blob();
            
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.click();
            
            // Cleanup
            window.URL.revokeObjectURL(url);
            
            return true;
        } catch (error) {
            console.error('Download error:', error);
            throw error;
        }
    }

    /**
     * Batch requests
     */
    async batch(requests) {
        try {
            const promises = requests.map(req => 
                this.request(req.endpoint, req.options)
            );
            return await Promise.all(promises);
        } catch (error) {
            console.error('Batch request error:', error);
            throw error;
        }
    }

    /**
     * Check API health
     */
    async healthCheck() {
        try {
            await this.get('/api/health');
            return true;
        } catch (error) {
            return false;
        }
    }
}

// Create singleton instance
const apiClient = new ApiClient();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ApiClient;
} else {
    window.ApiClient = ApiClient;
    window.apiClient = apiClient;
    // Backward compatibility
    window.apiService = apiClient;
}
