document.addEventListener("DOMContentLoaded", function () {
    const filterSidebar = document.getElementById("filterSidebar");
    const filterBtn = document.getElementById("filterBtn");
    const closeFilter = document.querySelector(".close-filter");
    const cartSidebar = document.getElementById("cartSidebar");
    const cartBtn = document.getElementById("cartBtn");
    const closeCart = document.querySelector(".close-cart");
    const cartItems = document.getElementById("cartItems");
    const cartCount = document.getElementById("cartCount");
    const cartTotal = document.getElementById("cartTotal");
    const proceedToBuy = document.getElementById("proceedToBuy");
    const paymentPopup = document.getElementById("paymentPopup");
    const closePopup = document.querySelector(".close-popup");
    const confirmPayment = document.getElementById("confirmPayment");
    const upiInput = document.getElementById("upiID");

    // Toggle Cart Sidebar
    cartBtn.addEventListener("click", () => {
        cartSidebar.classList.toggle("open");
        loadCart(); // Load cart items from DB
    });

    closeCart.addEventListener("click", () => {
        cartSidebar.classList.remove("open");
    });

    // Add to Cart Function (DB-based)
    function addToCart(book_id, title, price) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `book_id=${book_id}&book_title=${encodeURIComponent(title)}&price=${price}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Book added to cart');
                loadCart(); // Refresh cart display
            } else {
                alert('Error: ' + data.error);
            }
        });
    }

    // Load Cart Items from DB
    function loadCart() {
        fetch('get_cart.php')
            .then(res => res.json())
            .then(items => {
                cartItems.innerHTML = "";
                let total = 0;
                items.forEach(item => {
                    total += parseFloat(item.price);
                    const div = document.createElement("div");
                    div.classList.add("cart-item");
                    div.innerHTML = `
                        <p>${item.book_title} - ₹${item.price}</p>
                        <button class="remove-btn" onclick="removeCartItem(${item.cart_id})">Remove</button>
                    `;
                    cartItems.appendChild(div);
                });
                cartTotal.innerText = total;
                cartCount.innerText = items.length;
            });
    }

    // Remove Cart Item from DB
    function removeCartItem(cart_id) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `cart_id=${cart_id}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadCart();
            } else {
                alert('Failed to remove item');
            }
        });
    }

    // Clear Cart from DB
    function clearCart() {
        fetch('clear_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                cartItems.innerHTML = "";
                cartTotal.innerText = "0";
                cartCount.innerText = "0";
            } else {
                alert('Failed to clear cart');
            }
        });
    }

    // Confirm Payment
    confirmPayment.addEventListener("click", () => {
        clearCart(); // Clear the cart from database
        alert("Payment Successful!");
        paymentPopup.style.display = "none";
    });

    // Proceed to Buy
    proceedToBuy.addEventListener("click", () => {
        paymentPopup.style.display = "block";
    });

    // Close Payment Popup
    closePopup.addEventListener("click", () => {
        paymentPopup.style.display = "none";
    });

    // Show UPI Input if Selected
    document.querySelectorAll("input[name='payment']").forEach(radio => {
        radio.addEventListener("change", function () {
            upiInput.style.display = this.value === "upi" ? "block" : "none";
        });
    });

    // Expose Add to Cart Globally
    window.addToCart = addToCart;
    window.removeCartItem = removeCartItem;

    // Show/Hide Filter Sidebar
    filterBtn.addEventListener("click", () => {
        filterSidebar.classList.toggle("open");
    });

    closeFilter.addEventListener("click", () => {
        filterSidebar.classList.remove("open");
    });

    // Fetch and Display Books
    fetch("fetch_books.php")
        .then(response => response.json())
        .then(data => {
            displayBooks(data);
        })
        .catch(error => console.log("Error fetching books:", error));
});

// Function to Display Books
function displayBooks(books) {
    const bookContainer = document.getElementById("bookContainer");
    bookContainer.innerHTML = "";

    books.forEach(book => {
        const bookCard = document.createElement("div");
        bookCard.classList.add("book-card");
        bookCard.innerHTML = `
            <img src="${book.image}" alt="${book.title}">
            <h3>${book.title}</h3>
            <p><strong>Author:</strong> ${book.author}</p>
            <p><strong>Condition:</strong> ${book.condition}</p>
            <p><strong>Price:</strong> ₹${book.price}</p>
            <div class="description">
            <p><strong>Description:</Strong><span class="short-desc">${book.description.slice(0, 50)}...</span>
            <span class="full-desc" style="display: none;">${book.description}</span>
            <button class="toggle-desc" onclick="toggleDescription(this)">Read More</button></p>
            </div>
            <button class="add-to-cart" onclick="addToCart(${book.id}, '${book.title.replace(/'/g, "\\'")}', ${book.price})">Add to Cart</button>
            <button class="buy-now" onclick="buyNow(${book.id})">Buy Now</button>
        `;
        bookContainer.appendChild(bookCard);
    });
}

// Function to handle Buy Now
function buyNow(id) {
    paymentPopup.style.display = "block";
}

// Function to Apply Filters
function applyFilters() {
    const condition = document.getElementById("conditionFilter").value;
    const minPrice = parseFloat(document.getElementById("minPrice").value) || 0;
    const maxPrice = parseFloat(document.getElementById("maxPrice").value) || Infinity;
    const category = document.getElementById("categoryFilter").value;

    fetch("fetch_books.php")
        .then(response => response.json())
        .then(data => {
            let filteredBooks = data.filter(book => {
                return (
                    (condition === "all" || book.condition_status === condition) &&
                    (category === "all" || book.category === category) &&
                    (book.price >= minPrice && book.price <= maxPrice)
                );
            });

            displayBooks(filteredBooks);
            document.getElementById("filterSidebar").classList.remove("open");
        })
        .catch(error => console.log("Error filtering books:", error));
}

// Function to Search Books
function searchBooks() {
    let searchQuery = document.getElementById("searchBox").value.toLowerCase();

    fetch("fetch_books.php")
        .then(response => response.json())
        .then(data => {
            let searchedBooks = data.filter(book =>
                book.title.toLowerCase().includes(searchQuery) ||
                book.author.toLowerCase().includes(searchQuery)
            );

            displayBooks(searchedBooks);
        })
        .catch(error => console.log("Error searching books:", error));
}

// Toggle Description
function toggleDescription(button) {
    const bookDescription = button.parentElement;
    const shortDesc = bookDescription.querySelector('.short-desc');
    const fullDesc = bookDescription.querySelector('.full-desc');

    if (fullDesc.style.display === 'none') {
        shortDesc.style.display = 'none';
        fullDesc.style.display = 'inline';
        button.textContent = 'Read Less';
    } else {
        shortDesc.style.display = 'inline';
        fullDesc.style.display = 'none';
        button.textContent = 'Read More';
    }
}

// Show username if logged in
window.onload = function() {
    fetch('get_username.php')
        .then(response => response.json())
        .then(data => {
            if (data.username) {
                document.getElementById('displayUsername').textContent = data.username;
            } else {
                window.location.href = 'login.html';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = 'login.html';
        });
};