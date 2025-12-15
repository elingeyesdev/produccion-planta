# Trazabilidad API - Postman Testing Guide

## 🚀 Backend Server

The Laravel backend is currently running on:
```
http://127.0.0.1:8001
```

## 📦 Import Postman Collection

1. Open Postman
2. Click **Import** button (top left)
3. Select the file: `Trazabilidad_API.postman_collection.json`
4. The collection will be imported with all endpoints ready to test

## 🔐 Authentication Flow

### Step 1: Register a New User
1. Open the **Authentication** folder in the collection
2. Run the **Register** request
3. The token will be automatically saved to the collection variable `{{token}}`

**Default Request Body:**
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Step 2: Login (Alternative)
If you already have a user, use the **Login** request instead:
```json
{
    "email": "test@example.com",
    "password": "password123"
}
```

### Step 3: Test Protected Endpoints
Once authenticated, the token is automatically applied to all requests via the collection-level Bearer Token authentication.

Try the **Get Current User** request to verify authentication is working.

## 📋 Available Endpoint Categories

### 1. **Authentication**
- Register
- Login
- Get Current User
- Logout

### 2. **Unit of Measures**
- List All, Create, Get One, Update, Delete

### 3. **Customers**
- List All, Create, Get One, Update, Delete

### 4. **Production Batches**
- List All, Create, Get One, Update, Delete

### 5. **Process Transformation**
- Register Form
- Get Form
- Get Batch Process

### 6. **Process Evaluation**
- Finalize
- Get Log

### 7. **Raw Materials**
- List All, Create, Get One, Update, Delete

### 8. **Storages**
- List All
- Get by Batch

### 9. **Material Movement Logs**
- List All
- Get by Material

## 🎯 Testing Workflow

### Basic CRUD Testing
1. **List All** - Check what data exists
2. **Create** - Add a new record
3. **Get One** - Retrieve the created record
4. **Update** - Modify the record
5. **Delete** - Remove the record

### Business Logic Testing
1. Create a **Customer Order**
2. Create a **Production Batch** linked to the order
3. Use **Process Transformation** to register machine variables
4. Use **Process Evaluation** to finalize the batch

## 🔧 Collection Variables

The collection uses two variables:
- `base_url`: `http://127.0.0.1:8001/api`
- `token`: Automatically set after login/register

You can view/edit these in Postman:
- Click on the collection name
- Go to the **Variables** tab

## 📝 Notes

- All protected endpoints require authentication (Bearer Token)
- The token is automatically managed by the collection
- Request bodies are pre-filled with example data
- Modify the example data as needed for your tests

## 🛑 Stop the Server

When done testing, stop the Laravel server:
```bash
# Press Ctrl+C in the terminal where the server is running
```

## 📚 Additional Resources

For more endpoints and details, check:
- `routes/api.php` - All API routes
- `app/Http/Controllers/Api/` - Controller implementations
