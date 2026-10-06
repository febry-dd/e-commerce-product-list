// ==========================================
// DATA PRODUK
// ==========================================

const products = [
    {
        name: "Laptop ASUS",
        price: 8500000,
        description: "Laptop untuk kebutuhan kerja dan belajar.",
        image: "images/laptop.svg",
        category: "Elektronik"
    },
    {
        name: "Smartphone Samsung",
        price: 4500000,
        description: "Smartphone dengan performa tinggi dan kamera berkualitas.",
        image: "images/smartphone.svg",
        category: "Elektronik"
    },
    {
        name: "Sepatu Sneakers",
        price: 750000,
        description: "Sepatu sneakers nyaman untuk aktivitas sehari-hari.",
        image: "images/sepatu.svg",
        category: "Fashion"
    },
    {
        name: "Tas Backpack",
        price: 450000,
        description: "Tas backpack dengan desain modern dan nyaman digunakan.",
        image: "images/tas.svg",
        category: "Fashion"
    },
    {
        name: "Smart Watch",
        price: 1200000,
        description: "Smart watch dengan berbagai fitur kesehatan dan olahraga.",
        image: "images/smartwatch.svg",
        category: "Aksesoris"
    },
    {
        name: "Headphone Wireless",
        price: 850000,
        description: "Headphone wireless dengan kualitas suara jernih.",
        image: "images/headphone.svg",
        category: "Aksesoris"
    }
];

// ==========================================
// FORMAT HARGA
// ==========================================

function formatPrice(price) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0
    }).format(price);
}

// ==========================================
// MENAMPILKAN PRODUK
// ==========================================

function displayProducts(productList) {
    const productContainer = document.getElementById("product-list");

    productContainer.innerHTML = "";

    productList.forEach(function(product) {
        const productCard = `
            <div class="product-card">
                <img src="${product.image}" alt="${product.name}">
                <div class="product-content">
                    <div class="product-category">${product.category}</div>
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-price">${formatPrice(product.price)}</div>
                </div>
            </div>
        `;

        productContainer.innerHTML += productCard;
    });
}

// ==========================================
// FILTER BERDASARKAN KATEGORI
// ==========================================

function filterProducts(category) {
    if (category === "Semua") {
        displayProducts(products);
        return;
    }

    const filteredProducts = products.filter(function(product) {
        return product.category === category;
    });

    displayProducts(filteredProducts);
}

// Tampilkan semua produk saat halaman dibuka
displayProducts(products);
