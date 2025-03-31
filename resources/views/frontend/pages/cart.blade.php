@extends('frontend.layouts.master')
@section('title','Cart Page')
@section('main-content')
	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="#">Beranda<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="">Keranjang</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->

	<!-- Shopping Cart -->
	<div class="shopping-cart section">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<!-- Shopping Summery -->
					<table class="table shopping-summery">
						<thead>
							<tr class="main-hading">
								<th>PRODUK</th>
								<th>NAMA PRODUK</th>
								<th class="text-center">HARGA PRODUK</th>
								<th class="text-center">JUMLAH</th>
								<th class="text-center">TOTAL</th>
								<th class="text-center"><i class="ti-trash remove-icon"></i></th>
							</tr>
						</thead>
						<tbody id="cart_item_list">
							<!-- Cart items will be dynamically populated here -->
						</tbody>
					</table>
					<!--/ End Shopping Summery -->
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<!-- Total Amount -->
					<div class="total-amount">
						<div class="row">
							<div class="col-lg-4 col-md-7 col-12">
								<div class="right">
									<ul>
										<li class="order_subtotal" id="cart-subtotal">Sub Total Produk<span>Rp 0.00</span></li>
										<!-- <li class="last" id="order_total_price">Total B<span>Rp 0.00</span></li> -->
									</ul>
									<div class="button5">
                                        <button id="update-cart-btn" class="btn">Update Keranjang</button>
                                        <button class="btn checkout-button" onclick="window.location.href='{{ route('checkout') }}'">Checkout</button>
                                        <span id="checkout-warning" style="color: red; display: none;">Sesuaikan jumlah produk lalu Klik "Update Cart"!!! 
                                        </span>
                                        <a href="{{route('produk.grids')}}" class="btn">Lanjut Belanja</a>
                                    </div>
								</div>
							</div>
						</div>
					</div>
					<!--/ End Total Amount -->
				</div>
			</div>
		</div>
	</div>
	<!--/ End Shopping Cart -->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }

    function getApiBaseUrl() {
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }

    async function fetchCart() {
        try {
            const jwtToken = getJwtToken();

            if (!jwtToken) {
                throw new Error('Token JWT tidak valid. Silakan login kembali.');
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${jwtToken}`
            };

            console.log('Request Headers:', headers);

            const cartResponse = await fetch(`${getApiBaseUrl()}/api/keranjang`, {
                method: 'GET',
                headers: headers
            });

            if (!cartResponse.ok) {
                throw new Error('Failed to fetch cart');
            }

            const cartData = await cartResponse.json();
            renderCart(cartData.cart);
        } catch (error) {
            console.error('Cart fetch error:', error);
            // alert(error.message);
        }
    }

    function renderCart(cart) {
        const cartItemList = document.getElementById('cart_item_list');
        cartItemList.innerHTML = ''; // Clear existing items

        cart.detail_pemesanan.forEach((item, key) => {
            const row = document.createElement('tr');
            const photo = item.produk_variasi.gambar ? item.produk_variasi.gambar.split(',')[0] : '';

            row.innerHTML = `
                <td class="image" data-title="No">
                    <img src="${photo}" alt="${item.produk_variasi.variasi}">
                </td>
                <td class="product-des" data-title="Description">
                    <p class="product-name" style="font-size: 1.1em;">${item.produk_variasi.nama_produk}</p>
                    <p class="product-variasi" style="font-size: 0.8em; color: gray;">${item.produk_variasi.variasi}</p>
                </td>
                <td class="price" data-title="Price">
                    <span>Rp${item.produk_variasi.harga.toLocaleString('id-ID')}</span>
                </td>
                <td class="qty" data-title="Qty">
                    <div class="input-group">
                        <div class="button minus">
                            <button type="button" class="btn btn-primary btn-number decrease-qty" 
                                    data-type="minus" 
                                    data-field="quant[${key}]" 
                                    data-id="${item.id_detail_pemesanan}">
                                <i class="ti-minus"></i>
                            </button>
                        </div>
                        <input type="text" 
                            name="quant[${key}]" 
                            class="input-number qty-input" 
                            data-min="1" 
                            data-max="100" 
                            data-id="${item.id_detail_pemesanan}"
                            value="${item.jumlah}">
                        <div class="button plus">
                            <button type="button" 
                                    class="btn btn-primary btn-number increase-qty" 
                                    data-type="plus" 
                                    data-field="quant[${key}]" 
                                    data-id="${item.id_detail_pemesanan}">
                                <i class="ti-plus"></i>
                            </button>
                        </div>
                    </div>
                    <span class="stock-message" data-id="${item.id_detail_pemesanan}"></span>
                </td>
                <td class="total-amount cart_single_price" data-title="Total">
                    <span class="money">Rp ${(item.produk_variasi.harga * item.jumlah).toLocaleString('id-ID')}</span>
                </td>
                <td class="action" data-title="Remove">
                    <button class="remove-item" data-id="${item.id_detail_pemesanan}">
                        <i class="ti-trash remove-icon"></i>
                    </button>
                </td>
            `;
            cartItemList.appendChild(row);
        });

        // Update subtotal and total
        document.getElementById('cart-subtotal').querySelector('span').textContent = `Rp${cart.total_harga.toLocaleString('id-ID')}`;
        // document.getElementById('order_total_price').querySelector('span').textContent = `Rp${cart.total_harga.toLocaleString('id-ID')}`;

        attachCartEventListeners(cart);
        updateStockMessages(cart); // Validasi stok saat pertama kali render
        validateCart(cart); // Validasi seluruh keranjang dan update tombol checkout
    }

    function attachCartEventListeners(cart) {
        // Quantity increase
        document.querySelectorAll('.increase-qty').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.input-group').querySelector('.input-number');
                const itemId = input.dataset.id;
                const item = cart.detail_pemesanan.find(item => item.id_detail_pemesanan == itemId);
                const stock = item.produk_variasi.stok;

                if (parseInt(input.value) < stock) {
                    input.value = parseInt(input.value) + 1;
                    updateStockMessage(item, input);
                    validateCart(cart); // Validasi keranjang setelah perubahan
                }
            });
        });

        // Quantity decrease
        document.querySelectorAll('.decrease-qty').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.input-group').querySelector('.input-number');
                if (input.value > 1) {
                    input.value = parseInt(input.value) - 1;
                    const itemId = input.dataset.id;
                    const item = cart.detail_pemesanan.find(item => item.id_detail_pemesanan == itemId);
                    updateStockMessage(item, input);
                    validateCart(cart); // Validasi keranjang setelah perubahan
                }
            });
        });

        // Update cart
        document.getElementById('update-cart-btn').addEventListener('click', updateCart);

        // Remove item
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', async () => {
                const itemId = btn.dataset.id;
                await deleteCartItem(itemId);
                fetchCart(); // Refresh keranjang setelah menghapus item
            });
        });
    }

    function updateStockMessages(cart) {
        cart.detail_pemesanan.forEach(item => {
            const input = document.querySelector(`.qty-input[data-id="${item.id_detail_pemesanan}"]`);
            input.addEventListener('input', (e) => updateStockMessage(item, e.target)); // Real-time listener
            updateStockMessage(item, input); // Initial check
        });
    }

    function updateStockMessage(item, input) {
        const stockMessage = document.querySelector(`.stock-message[data-id="${item.id_detail_pemesanan}"]`);
        const currentQty = parseInt(input.value);
        const stock = item.produk_variasi.stok;

        if (currentQty > stock) {
            stockMessage.textContent = `Stok tersisa: ${stock}`;
            stockMessage.style.color = 'red';
        } else if (currentQty < 1) {
            stockMessage.textContent = 'Jumlah tidak boleh 0';
            stockMessage.style.color = 'red';
        } else {
            stockMessage.textContent = '';
        }
    }

    function validateCart(cart) {
        const checkoutButton = document.querySelector('.checkout-button');
        const warningMessage = document.getElementById('checkout-warning');
        let isCartValid = true;

        cart.detail_pemesanan.forEach(item => {
            const currentQty = item.jumlah;
            const stock = item.produk_variasi.stok;

            if (currentQty > stock || currentQty === 0) {
                isCartValid = false;
            }
        });

        // Aktifkan atau nonaktifkan tombol checkout berdasarkan validasi
        checkoutButton.disabled = !isCartValid;
        if (!isCartValid) {
            warningMessage.style.display = 'inline';
        } else {
            warningMessage.style.display = 'none';
        }
    }

    async function updateCart() {
        try {
            const jwtToken = getJwtToken();
            const inputs = document.querySelectorAll('.qty-input');
            
            const updates = Array.from(inputs).map(input => ({
                id_detail_pemesanan: input.dataset.id,
                jumlah: parseInt(input.value)
            }));

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${jwtToken}`
            };

            const updatePromises = updates.map(update => 
                fetch(`${getApiBaseUrl()}/api/keranjang/update/${update.id_detail_pemesanan}`, {
                    method: 'PUT',
                    headers: headers,
                    body: JSON.stringify({ jumlah: update.jumlah })
                })
            );

            await Promise.all(updatePromises);
            fetchCart(); // Reload cart after updates
        } catch (error) {
            console.error('Update cart error:', error);
            Swal.fire({
                title: "Gagal!",
                text: "Gagal Untuk mengupdate item dari keranjang, Silakan Coba Lagi",
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    }

    async function deleteCartItem(itemId) {
        try {
            const jwtToken = getJwtToken();
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${jwtToken}`
            };

            const response = await fetch(`${getApiBaseUrl()}/api/keranjang/delete/${itemId}`, {
                method: 'DELETE',
                headers: headers
            });

            if (!response.ok) {
                throw new Error('Failed to delete item from cart');
            }

            fetchCart(); // Reload cart after deletion
        } catch (error) {
            console.error('Delete item error:', error);
            Swal.fire({
                title: "Gagal!",
                text: "Gagal Untuk menghapus item dari keranjang, Silakan Coba Lagi",
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    }
    // updateStockMessage();
    // Initial cart fetch
    fetchCart();
});
</script>
@endpush

@push('styles')
<style>
    li.shipping{
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}
		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}
		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}
		.form-select {
			height: 30px;
			width: 100%;
		}
		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}
		.list li{
			margin-bottom:0 !important;
		}
		.list li:hover{
			background:#F7941D !important;
			color:white !important;
		}
		.form-select .nice-select::after {
			top: 14px;
		}
</style>
@endpush