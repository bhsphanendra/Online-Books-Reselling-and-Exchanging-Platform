let books = [];

        function addBook() {
            const title = document.getElementById('book-title-input').value;
            const author = document.getElementById('book-author-input').value;
            if (title && author) {
                const book = { title, author };
                books.push(book);
                displayBooks();
                document.getElementById('book-title-input').value = '';
                document.getElementById('book-author-input').value = '';
            } else {
                alert('Please enter both title and author.');
            }
        }

        function displayBooks() {
            const bookList = document.getElementById('book-list');
            bookList.innerHTML = '';
            books.forEach((book, index) => {
                bookList.innerHTML += `
                    <div class="book-card">
                        <div>
                            <h2>${book.title}</h2>
                            <p>by ${book.author}</p>
                        </div>
                        <div>
                            <button onclick="openDonationForm(${index})">Donate</button>
                            <button class="remove-button" onclick="removeBook(${index})">Remove</button>
                        </div>
                    </div>
                `;
            });
        }

        function removeBook(index) {
            books.splice(index, 1);
            displayBooks();
        }

        function openDonationForm(index) {
            document.getElementById('selected-book-title').innerText = books[index].title;
            document.getElementById('donation-form').style.display = 'block';
        }

        function confirmDonation() {
            const donationOption = document.getElementById('donation-option').value;
            if (donationOption) {
                alert('Thank you for donating!');
                document.getElementById('donation-form').style.display = 'none';
            } else {
                alert('Please select a donation option.');
            }
        }


        function addBook() {
    const title = document.getElementById("book-title-input").value;
    const author = document.getElementById("book-author-input").value;

    if (title && author) {
        const book = {
            type: "donate",
            title,
            author,
            image: "default-book.jpg" // Placeholder for image
        };

        // Save book to localStorage
        let books = JSON.parse(localStorage.getItem("books")) || [];
        books.push(book);
        localStorage.setItem("books", JSON.stringify(books));

        alert("Book added for donation!");
        window.location.href = "main.html"; // Redirect to main page
    } else {
        alert("Please enter both title and author.");
    }
}