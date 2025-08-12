document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const errorElement = document.getElementById('error-message');
    
    // Basic client-side validation
    if (!username || !password) {
        errorElement.textContent = 'Please fill in all fields';
        return;
    }
    
    // If validation passes, submit the form
    this.submit();
});