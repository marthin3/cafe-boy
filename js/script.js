// js/script.js

// Konfirmasi sebelum menghapus
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alert setelah 3 detik
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 3000);
    });
    
    // Format rupiah otomatis untuk input harga
    const hargaInputs = document.querySelectorAll('input[name="harga"], input[name="bayar"]');
    hargaInputs.forEach(function(input) {
        input.addEventListener('keyup', function(e) {
            formatRupiah(this);
        });
    });
});

// Fungsi format rupiah
function formatRupiah(input) {
    let value = input.value.replace(/[^,\d]/g, '').toString();
    let split = value.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    
    input.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
}

// Fungsi untuk transaksi (jika menggunakan keranjang)
let cart = [];

function addToCart(id, nama, harga) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.jumlah++;
        existingItem.subtotal = existingItem.jumlah * existingItem.harga;
    } else {
        cart.push({
            id: id,
            nama: nama,
            harga: harga,
            jumlah: 1,
            subtotal: harga
        });
    }
    
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartTable = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    if (cartTable) {
        let html = '';
        let total = 0;
        
        cart.forEach((item, index) => {
            total += item.subtotal;
            html += `
                <tr>
                    <td>${item.nama}</td>
                    <td>Rp ${formatNumber(item.harga)}</td>
                    <td>
                        <input type="number" value="${item.jumlah}" min="1" 
                               onchange="updateCartItem(${index}, this.value)">
                    </td>
                    <td>Rp ${formatNumber(item.subtotal)}</td>
                    <td>
                        <button onclick="removeFromCart(${index})" class="btn btn-small btn-danger">Hapus</button>
                    </td>
                </tr>
            `;
        });
        
        cartTable.innerHTML = html;
        if (cartTotal) {
            cartTotal.innerHTML = `Rp ${formatNumber(total)}`;
        }
    }
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function updateCartItem(index, jumlah) {
    cart[index].jumlah = parseInt(jumlah);
    cart[index].subtotal = cart[index].jumlah * cart[index].harga;
    updateCartDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

// Validasi form sebelum submit
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required]');
    for (let input of inputs) {
        if (!input.value) {
            alert('Semua field yang wajib diisi harus diisi!');
            input.focus();
            return false;
        }
    }
    
    return true;
}