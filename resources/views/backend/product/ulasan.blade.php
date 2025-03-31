@extends('backend.layouts.master')

@section('title', 'Daftar Penilaian Produk')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Penilaian - <span id="product-name"></span></h6>
        <div>
            <a href="{{route ('index.produk') }}" class="btn btn-primary btn-sm">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <!-- Rating breakdown -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="font-weight-bold">Rating Breakdown</h6>
                <div id="rating-breakdown">
                    <!-- Rating bars will be inserted here -->
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Filter by Rating</h6>
                <div class="btn-group" role="group" aria-label="Filter by rating">
                    <button type="button" class="btn btn-outline-secondary active" data-rating="all">All</button>
                    <button type="button" class="btn btn-outline-secondary" data-rating="5">5 ⭐</button>
                    <button type="button" class="btn btn-outline-secondary" data-rating="4">4 ⭐</button>
                    <button type="button" class="btn btn-outline-secondary" data-rating="3">3 ⭐</button>
                    <button type="button" class="btn btn-outline-secondary" data-rating="2">2 ⭐</button>
                    <button type="button" class="btn btn-outline-secondary" data-rating="1">1 ⭐</button>
                </div>
                <span class="badge badge-primary" id="avg-rating">Rating: 0</span>
                <span class="badge badge-info" id="total-reviews">Total: 0</span>
            </div>
        </div>

        <!-- Reviews container -->
        <div class="row" id="reviews-container">
            <!-- Reviews will be inserted here -->
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">Balas Ulasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="review-id">
                <div class="form-group">
                    <label for="reply-text">Balasan:</label>
                    <textarea class="form-control" id="reply-text" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="submit-reply">Kirim Balasan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }
    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Get product ID from URL
        const productId = window.location.pathname.split('/').pop();
        
        // Store all reviews for filtering
        let allReviews = [];
        
        // Fetch and display reviews
        fetchReviews(productId);
        
        // Setup reply submission handler
        document.getElementById('submit-reply').addEventListener('click', submitReply);
        
        // Setup filter buttons
        document.querySelectorAll('[data-rating]').forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('[data-rating]').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                
                // Filter reviews
                const rating = this.getAttribute('data-rating');
                filterReviews(rating);
            });
        });
    });
    
    // Fetch reviews data from API
    async function fetchReviews(productId) {
        try {
            const response = await fetch(`${getApiBaseUrl()}/api/ulasan/get-by-produk/${productId}`);
            const result = await response.json();
            
            if (result.status) {
                // Store all reviews for filtering
                allReviews = result.data.ringkasan_ulasan;
                
                displayProductInfo(result.data);
                displayRatingBreakdown(result.data.rating_summary);
                displayReviews(result.data.ringkasan_ulasan);
            } else {
                showError('Gagal memuat ulasan.');
            }
        } catch (error) {
            console.error('Error fetching reviews:', error);
            showError('Produk Belum Memiliki Ulasan');
        }
    }
    
    // Display product information
    function displayProductInfo(data) {
        document.getElementById('product-name').textContent = data.nama_produk;
        document.getElementById('avg-rating').textContent = `Rating: ${data.rating_summary.average_rating}`;
        document.getElementById('total-reviews').textContent = `Total: ${data.rating_summary.total_reviews}`;
    }
    
    // Display rating breakdown
    function displayRatingBreakdown(ratingData) {
        const container = document.getElementById('rating-breakdown');
        container.innerHTML = '';
        
        for (let i = 5; i >= 1; i--) {
            const percentage = ratingData.rating_breakdown[i]?.percentage || 0;
            const count = ratingData.rating_breakdown[i]?.count || 0;
            
            const ratingRow = document.createElement('div');
            ratingRow.className = 'd-flex align-items-center mb-1';
            ratingRow.innerHTML = `
                <div class="w-25">
                    ${i} ⭐ (${count})
                </div>
                <div class="w-50">
                    <div class="progress" style="height: 15px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: ${percentage}%" 
                            aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="w-25 pl-2">
                    ${percentage}%
                </div>
            `;
            container.appendChild(ratingRow);
        }
    }
    
    // Display reviews using cards
    function displayReviews(reviews) {
        const container = document.getElementById('reviews-container');
        container.innerHTML = '';
        
        if (reviews.length === 0) {
            container.innerHTML = '<div class="col-12 text-center my-4">Tidak ada ulasan yang ditemukan</div>';
            return;
        }
        console.log(reviews);
        
        reviews.forEach(review => {
            // Generate star ratings
            const stars = '⭐'.repeat(review.rating);
            
            // Format date
            const date = new Date(review.tanggal_dibuat);
            const formattedDate = date.toLocaleDateString('id-ID', {
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric'
            });
            
            // Check if review has a reply
            const hasReply = review.balasan !== null;

            const reviewCard = document.createElement('div');
            reviewCard.className = 'col-12 mb-2'; // Ubah kelas menjadi col-12
            reviewCard.setAttribute('data-rating', review.rating);

            reviewCard.innerHTML = `
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-3">
                        <div class="row no-gutters">
                            <div class="col-2 border-right pr-2">
                                <h6 class="mb-1 font-weight-bold">${review.nama_pelanggan}</h6>
                                <small class="d-block text-muted mb-1">${review.variasi}</small>
                                <small class="text-muted">${formattedDate}</small>
                            </div>
                            <div class="col-10 pl-2">
                                <div class="mb-1">${stars}</div>
                                <p class="mb-1 small review-text">${review.ulasan}</p>
                                <div id="reply-container-${review.id_ulasan}">
                                    ${hasReply ? 
                                        `<p class="mb-0 small text-success"><strong>Balasan:</strong> ${review.balasan.balasan}</p>` : 
                                        `<button class="btn btn-sm btn-primary reply-btn" data-review-id="${review.id_ulasan}">Balas</button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                            
            container.appendChild(reviewCard);
        });
        
        // Add event listeners to reply buttons
        document.querySelectorAll('.reply-btn').forEach(button => {
            button.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-review-id');
                openReplyModal(reviewId);
            });
        });
    }

    // Filter reviews by rating
    function filterReviews(rating) {
        if (rating === 'all') {
            displayReviews(allReviews);
            return;
        }
        
        const filteredReviews = allReviews.filter(review => review.rating.toString() === rating);
        displayReviews(filteredReviews);
    }

    // Open reply modal
    function openReplyModal(reviewId) {
        document.getElementById('review-id').value = reviewId;
        document.getElementById('reply-text').value = '';
        $('#replyModal').modal('show');
    }

    // Submit reply
    async function submitReply() {
        const reviewId = document.getElementById('review-id').value;
        const replyText = document.getElementById('reply-text').value.trim();
        
        if (!replyText) {
            Swal.fire({
                title: "Peringatan!",
                text: "Mohon isi balasan terlebih dahulu!",
                icon: "error",
                confirmButtonText: "OK"
            });
            return;
        }
        
        try {
            // You'll need to adjust this endpoint based on your actual API
            const response = await fetch(`${getApiBaseUrl()}/api/ulasan/balasan/${reviewId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    "Authorization": `Bearer ${getJwtToken()}`
                },
                body: JSON.stringify({
                    balasan: replyText
                })
            });
            
            const result = await response.json();
            
            if (result.status) {
                // Update UI without reloading
                updateReplyInUI(reviewId, replyText);
                $('#replyModal').modal('hide');
            } else {
                Swal.fire({
                    title: "Gagal!",
                    text: "Gagal mengirim balasan: " + (result.message || "Terjadi kesalahan"),
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        } catch (error) {
            console.error('Error submitting reply:', error);
            Swal.fire({
                title: "Error!",
                text: "Terjadi kesalahan saat mengirim balasan",
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    }

    // Update UI after successfully submitting a reply
    function updateReplyInUI(reviewId, replyText) {
        // Cari elemen container balasan berdasarkan ID
        const replyContainer = document.getElementById(`reply-container-${reviewId}`);
        
        if (replyContainer) {
            // Update konten balasan
            replyContainer.innerHTML = `
                <p class="mb-0 small text-success">
                    <strong>Balasan:</strong> ${replyText}
                </p>
            `;
        } else {
            console.error(`Elemen dengan ID reply-container-${reviewId} tidak ditemukan.`);
        }
    }

    // Show error message
    function showError(message) {
        const container = document.getElementById('reviews-container');
        container.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${message}</td></tr>`;
    }
</script>
@endpush
