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
							<li><a href="#">Home<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="">Cart</a></li>
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
								<th>PRODUCT</th>
								<th>NAME</th>
								<th class="text-center">UNIT PRICE</th>
								<th class="text-center">QUANTITY</th>
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
							<!-- <div class="col-lg-8 col-md-5 col-12">
								<div class="left">
									<div class="coupon">
										<form id="coupon-form">
											<input name="code" placeholder="Enter Your Coupon">
											<button class="btn" type="submit">Apply</button>
										</form>
									</div>
								</div>
							</div> -->
							<div class="col-lg-4 col-md-7 col-12">
								<div class="right">
									<ul>
										<li class="order_subtotal" id="cart-subtotal">Cart Subtotal<span>$0.00</span></li>
										<li class="last" id="order_total_price">You Pay<span>$0.00</span></li>
									</ul>
									<div class="button5">
										<button id="update-cart-btn" class="btn">Update Cart</button>
										<a href="{{route('checkout')}}" class="btn">Checkout</a>
										<a href="{{route('produk.grids')}}" class="btn">Continue shopping</a>
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

            const cartResponse = await fetch('/api/keranjang', {
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
                    <p class="product-name">${item.produk_variasi.variasi}</p>
                </td>
                <td class="price" data-title="Price">
                    <span>$${item.produk_variasi.harga.toFixed(2)}</span>
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
        document.getElementById('cart-subtotal').querySelector('span').textContent = `$${cart.total_harga.toFixed(2)}`;
        document.getElementById('order_total_price').querySelector('span').textContent = `$${cart.total_harga.toFixed(2)}`;

        attachCartEventListeners();
    }

    function attachCartEventListeners() {
        // Quantity increase
        document.querySelectorAll('.increase-qty').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.input-group').querySelector('.input-number');
                input.value = parseInt(input.value) + 1;
            });
        });

        // Quantity decrease
        document.querySelectorAll('.decrease-qty').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.input-group').querySelector('.input-number');
                if (input.value > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            });
        });

        // Update cart
        document.getElementById('update-cart-btn').addEventListener('click', updateCart);

        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', async () => {
                const itemId = btn.dataset.id;
                await deleteCartItem(itemId);
            });
        });
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
                fetch(`/api/keranjang/update/${update.id_detail_pemesanan}`, {
                    method: 'PUT',
                    headers: headers,
                    body: JSON.stringify({ jumlah: update.jumlah })
                })
            );

            await Promise.all(updatePromises);
            fetchCart(); // Reload cart after updates
        } catch (error) {
            console.error('Update cart error:', error);
            alert('Failed to update cart');
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

            const response = await fetch(`/api/keranjang/delete/${itemId}`, {
                method: 'DELETE',
                headers: headers
            });

            if (!response.ok) {
                throw new Error('Failed to delete item from cart');
            }

            fetchCart(); // Reload cart after deletion
        } catch (error) {
            console.error('Delete item error:', error);
            alert('Failed to delete item from cart');
        }
    }
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