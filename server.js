const express = require('express');
const cors = require('cors');
const path = require('path');
const app = express();
const port = 3000;

app.use(cors());
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.json()); // Enable JSON body parsing
// Serve the directory root as static allowing access to preview.html
app.use(express.static(__dirname));

// Mock Database
const users = [
    { id: 1, name: "Test User", email: "user@example.com", password: "password", role: "renter" },
    { id: 2, name: "Landlord John", email: "landlord@example.com", password: "password", role: "owner" }
];

const properties = [
    {
        id: 1,
        title: "Luxury Downtown Loft",
        type: "apartment",
        price: 3500,
        city: "New York",
        address: "123 Broadway",
        bedrooms: 2,
        bathrooms: 2,
        area: 1200,
        image: "https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
        is_featured: true
    },
    {
        id: 2,
        title: "Modern Family Home",
        type: "house",
        price: 5200,
        city: "Los Angeles",
        address: "456 Sunset Blvd",
        bedrooms: 4,
        bathrooms: 3,
        area: 2500,
        image: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
        is_featured: true
    },
    {
        id: 3,
        title: "Cozy City Studio",
        type: "studio",
        price: 1800,
        city: "Chicago",
        address: "789 Michigan Ave",
        bedrooms: 1,
        bathrooms: 1,
        area: 500,
        image: "https://images.unsplash.com/photo-1554995207-c18c203602cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
        is_featured: false
    },
    {
        id: 4,
        title: "Beachfront Villa",
        type: "villa",
        price: 8500,
        city: "Miami",
        address: "101 Ocean Dr",
        bedrooms: 5,
        bathrooms: 4,
        area: 4000,
        image: "https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
        is_featured: true
    },
    {
        id: 5,
        title: "Suburban Retreat",
        type: "house",
        price: 2800,
        city: "Austin",
        address: "202 Congress Ave",
        bedrooms: 3,
        bathrooms: 2,
        area: 1800,
        image: "https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
        is_featured: false
    }
];

// API Endpoints
app.get('/api/properties', (req, res) => {
    let results = properties;
    
    // Filtering
    if (req.query.city) {
        results = results.filter(p => p.city.toLowerCase().includes(req.query.city.toLowerCase()));
    }
    
    if (req.query.type) {
        results = results.filter(p => p.type.toLowerCase() === req.query.type.toLowerCase());
    }
    
    res.json(results);
});

app.get('/api/properties/:id', (req, res) => {
    const property = properties.find(p => p.id === parseInt(req.params.id));
    if (property) {
        res.json(property);
    } else {
        res.status(404).json({ error: "Property not found" });
    }
});

app.post('/api/login', (req, res) => {
    const { email, password } = req.body;
    const user = users.find(u => u.email === email && u.password === password);
    
    if (user) {
        // Return user info (mocking a token response)
        res.json({ 
            success: true, 
            user: { id: user.id, name: user.name, email: user.email, role: user.role },
            token: "mock-jwt-token-" + Date.now()
        });
    } else {
        res.status(401).json({ success: false, error: "Invalid credentials" });
    }
});

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'dynamic.html'));
});

app.listen(port, () => {
    console.log(`Dynamic server running at http://localhost:${port}`);
});
