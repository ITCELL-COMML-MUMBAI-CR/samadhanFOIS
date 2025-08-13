document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginButton = loginForm.querySelector('button[type="submit"]');
    const originalButtonText = loginButton.innerHTML;
    
    loginForm.addEventListener('submit', function() {
        loginButton.disabled = true;
        loginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Logging in...';
        
        // Re-enable after 5 seconds in case of issues
        setTimeout(function() {
            loginButton.disabled = false;
            loginButton.innerHTML = originalButtonText;
        }, 5000);
    });
    
    // Focus on login ID field
    document.getElementById('login_id').focus();
    
    // Show/hide password toggle
    const passwordField = document.getElementById('password');
    const showPasswordToggle = document.createElement('button');
    showPasswordToggle.type = 'button';
    showPasswordToggle.className = 'btn btn-outline-secondary btn-sm';
    showPasswordToggle.innerHTML = '<i class="fas fa-eye"></i>';
    showPasswordToggle.style.position = 'absolute';
    showPasswordToggle.style.right = '10px';
    showPasswordToggle.style.top = '50%';
    showPasswordToggle.style.transform = 'translateY(-50%)';
    showPasswordToggle.style.zIndex = '10';
    
    passwordField.parentElement.style.position = 'relative';
    passwordField.parentElement.appendChild(showPasswordToggle);
    
    showPasswordToggle.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        const icon = type === 'password' ? 'fa-eye' : 'fa-eye-slash';
        showPasswordToggle.innerHTML = `<i class="fas ${icon}"></i>`;
    });
});

