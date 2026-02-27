document.addEventListener('DOMContentLoaded', () => {
    const categoryContainer = document.getElementById('category-container');
    const productsWrapper = document.getElementById('products-wrapper');

    const whatsappNumber = "5492235772165";

    // Render Categories
    function renderCategories() {
        // Add "All" category
        const allBtn = document.createElement('div');
        allBtn.classList.add('category-item', 'active');
        allBtn.textContent = "Todos los Productos";
        allBtn.dataset.category = "all";
        allBtn.addEventListener('click', handleCategoryClick);
        categoryContainer.appendChild(allBtn);

        // Add SANYI title label
        const sanyiLabel = document.createElement('div');
        sanyiLabel.classList.add('category-label');
        sanyiLabel.textContent = "SANYI";
        categoryContainer.appendChild(sanyiLabel);

        catalogData.categories.forEach(cat => {
            const catBtn = document.createElement('div');
            catBtn.classList.add('category-item');
            catBtn.textContent = cat;
            catBtn.dataset.category = cat;
            catBtn.addEventListener('click', handleCategoryClick);
            categoryContainer.appendChild(catBtn);
        });
    }

    // Render Products
    function renderProducts(categoryFilter = 'all') {
        productsWrapper.innerHTML = ''; // Clear current products

        const filteredProducts = categoryFilter === 'all'
            ? catalogData.products
            : catalogData.products.filter(p => p.category === categoryFilter);

        if (filteredProducts.length === 0) {
            productsWrapper.innerHTML = '<p>No hay productos en esta categoría.</p>';
            return;
        }

        filteredProducts.forEach(product => {
            // WhatsApp Message Encoding
            const message = `Hola Laserdreams! Me interesa consultar precio y disponibilidad del producto: ${product.name} (${product.category}).`;
            const encodedMessage = encodeURIComponent(message);
            const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;

            const card = document.createElement('div');
            card.classList.add('product-card');
            card.innerHTML = `
        <img src="${product.image}" alt="${product.name}" class="product-image" loading="lazy">
        <h3 class="product-name" style="text-align: center; font-size: 1.2rem; margin-top: 10px;">${product.name}</h3>
        <a href="${whatsappURL}" target="_blank" class="btn btn-primary">Consultar</a>
      `;
            productsWrapper.appendChild(card);
        });
    }

    // Handle Category Filter Click
    function handleCategoryClick(e) {
        // Remove active class from all
        document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
        // Add active class to clicked
        const clickedEl = e.target;
        clickedEl.classList.add('active');

        // Re-render products
        const selectedCategory = clickedEl.dataset.category;
        renderProducts(selectedCategory);
    }

    // Init
    renderCategories();
    renderProducts('all');
});
