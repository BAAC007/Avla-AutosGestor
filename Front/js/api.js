// Front/js/api.js
const API_BASE = '/api'; // Mismo origen → sin problemas de CORS

async function apiRequest(endpoint, options = {}) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            // Si usas sesiones/cookies:
            // 'credentials': 'include'
        },
        ...options
    });
    
    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.error || `HTTP ${response.status}`);
    }
    
    return response.json();
}

// Ejemplo de uso:
// const vehicles = await apiRequest('/?action=get_vehicles');