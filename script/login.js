document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    
    // Function to show error messages
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.add('active');
        
        // Hide the error message after 5 seconds
        setTimeout(() => {
            errorMessage.classList.remove('active');
        }, 5000);
    }
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Login response:', data); // Debugging: Log the response
            
            if (data.success) {
                // Save user data in localStorage
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Show success message
                showSuccess('Login successful! Redirecting...');
                
                // Redirect based on user role
                setTimeout(() => {
                    switch(data.user.role) {
                        case 'admin':
                            window.location.replace('admin/admin_dashboard.php');
                            break;
                        case 'seller':
                            window.location.replace('seller.php');
                            break;
                        default: // Regular user
                            window.location.replace('user.html');
                            break;
                    }
                }, 1000); // Short delay for the success message to be visible
                
            } else {
                // Show error message
                showError(data.message || 'Invalid username or password');
            }
        } catch (error) {
            console.error('Login error:', error);
            showError('An error occurred during login. Please try again.');
        }
    });
    
    // Function to show success messages
    function showSuccess(message) {
        errorMessage.textContent = message;
        errorMessage.style.backgroundColor = '#e6f7e6';
        errorMessage.style.color = '#28a745';
        errorMessage.classList.add('active');
    }
    
    // Handle social login buttons (placeholder functionality)
    const socialButtons = document.querySelectorAll('.social-button');
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            showError('Social login is not implemented yet');
        });
    });
});