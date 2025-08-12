function checkLoginStatus() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    const loginBtn = document.querySelector('.login-btn');
    const userMenu = document.getElementById('userMenu');
    const username = document.getElementById('username');

    if (currentUser) {
        loginBtn.style.display = 'none';
        userMenu.style.display = 'flex';
        username.textContent = currentUser.username;
    } else {
        loginBtn.style.display = 'block';
        userMenu.style.display = 'none';
    }
}

function logout() {
    localStorage.removeItem('currentUser');
    window.location.reload();
}

// Load and display books by type
function displayBooksByType(type, containerId) {
    const books = JSON.parse(localStorage.getItem('books')) || [];
    const container = document.getElementById(containerId);
    const filteredBooks = books.filter(book => book.type === type).slice(0, 4);

    container.innerHTML = filteredBooks.length ? '' : '<p>No books available</p>';

    filteredBooks.forEach(book => {
        const bookCard = document.createElement('div');
        bookCard.classList.add('book-card');
        bookCard.innerHTML = `
            <img src="${book.image}" alt="${book.title}">
            <div class="book-info">
                <h3>${book.title}</h3>
                <p class="author">by ${book.author}</p>
                ${type === 'sell' ? `<p class="price">₹${formatIndianPrice(book.price)}</p>` : ''}
                <p class="condition">${book.condition}</p>
                ${type === 'exchange' ? `<p class="preference">Looking for: ${book.exchangePreference}</p>` : ''}
            </div>
        `;
        container.appendChild(bookCard);
    });
}

function formatIndianPrice(number) {
    const num = parseFloat(number);
    const parts = num.toFixed(2).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
}

// Initialize
checkLoginStatus();
displayBooksByType('sell', 'saleBooks');
displayBooksByType('exchange', 'exchangeBooks');
displayBooksByType('donate', 'donationBooks');

// Toggle mobile menu
function toggleMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('active');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const mobileMenu = document.getElementById('mobileMenu');
    const menuButton = document.querySelector('.menu-button');
    
    if (!mobileMenu.contains(event.target) && !menuButton.contains(event.target)) {
        mobileMenu.classList.remove('active');
    }
});

// Load books for different sections
async function loadBooks() {
    try {
        // Load books for sale
        const saleResponse = await fetch('api/books.php?type=sale');
        const saleBooks = await saleResponse.json();
        displayBooks('saleBooks', saleBooks);

        // Load books for exchange
        const exchangeResponse = await fetch('api/books.php?type=exchange');
        const exchangeBooks = await exchangeResponse.json();
        displayBooks('exchangeBooks', exchangeBooks);

        // Load books for donation
        const donationResponse = await fetch('api/books.php?type=donation');
        const donationBooks = await donationResponse.json();
        displayBooks('donationBooks', donationBooks);
    } catch (error) {
        console.error('Error loading books:', error);
    }
}

// Display books in the grid
function displayBooks(containerId, books) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = books.map(book => `
        <div class="book-card">
            <img src="${book.image_url}" alt="${book.title}">
            <h3>${book.title}</h3>
            <p class="author">${book.author}</p>
            <p class="price">${book.price ? `$${book.price}` : 'Free'}</p>
            <button onclick="viewBookDetails(${book.id})">View Details</button>
        </div>
    `).join('');
}

// View book details
function viewBookDetails(bookId) {
    window.location.href = `book_details.php?id=${bookId}`;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadBooks();
});