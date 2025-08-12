document.getElementById("signupForm").addEventListener("submit", function(event) {
    event.preventDefault();

    // Get form values
    const firstName = document.getElementById("firstName").value;
    const lastName = document.getElementById("lastName").value;
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const gender = document.getElementById("gender").value;
    const birthdate = document.getElementById("birthdate").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Validate password
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        alert("Password does not meet requirements!");
        return;
    }

    // Check if passwords match
    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return;
    }

    // Check if username or email already exists
    const users = JSON.parse(localStorage.getItem("users")) || [];
    if (users.some(user => user.username === username)) {
        alert("Username already exists!");
        return;
    }
    if (users.some(user => user.email === email)) {
        alert("Email already registered!");
        return;
    }

    // Create user object
    const user = {
        id: Date.now(),
        firstName,
        lastName,
        username,
        email,
        gender,
        birthdate,
        password,
        role: "user",
        dateJoined: new Date().toISOString()
    };

    // Save user
    users.push(user);
    localStorage.setItem("users", JSON.stringify(users));

    alert("Sign up successful! Please login.");
    window.location.href = "login.html";
});