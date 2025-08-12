document.addEventListener("DOMContentLoaded", function () {
    fetchBooks();
});

function fetchBooks() {
    fetch('fetch_books.php')
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then(data => {
            console.log("Fetched Books Data:", data); // Debugging
            displayBooks(data.sale, 'saleBooks');
            displayBooks(data.exchange, 'exchangeBooks');
            displayBooks(data.donation, 'donationBooks');
        })
        .catch(error => console.error("Error fetching books:", error));
}

function displayBooks(books, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = "";

    if (!books || books.length === 0) {
        container.innerHTML = "<p>No books available</p>";
        return;
    }

    books.forEach(book => {
        const bookCard = document.createElement("div");
        bookCard.classList.add("book-card");
        bookCard.innerHTML = `
            <img src="uploads/${book.image}" alt="${book.title}" class="book-image">
            <div class="book-info">
                <h3>${book.title}</h3>
                <p><strong>Author:</strong> ${book.author}</p>
                <p><strong>Condition:</strong> ${book.condition}</p>
                ${book.price ? `<p><strong>Price:</strong> ₹${book.price}</p>` : ""}
                <button class="buy-btn">View Details</button>
            </div>
        `;
        container.appendChild(bookCard);
    });
}

window.onload = function() {
    fetch('get_username.php')
        .then(response => response.json())
        .then(data => {
            if (data.username) {
                document.getElementById('displayUsername').textContent = data.username;
            } else {
                window.location.href = 'login.html'; // Redirect to login if not logged in
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = 'login.html';
        });
};

// Toggle mobile menu
function toggleMenu() {
    const menuButton = document.querySelector('.menu-button');
    const mobileMenu = document.getElementById('mobileMenu');
    
    menuButton.classList.toggle('active');
    mobileMenu.classList.toggle('active');
}
