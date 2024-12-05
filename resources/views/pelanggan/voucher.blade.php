<div class="voucher-container">
    <div class="card">
        <div class="card-header">
            <h3>Voucher Saya</h3>
        </div>
        <div class="card-body">
            <div class="row" id="voucher-list">
                <div class="col-12 text-center" id="voucher-loading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="col-12 text-center d-none" id="voucher-empty">
                    <p>Anda tidak memiliki voucher aktif</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    class VoucherManager {
        constructor() {
            this.apiBaseUrl = 'http://127.0.0.1:8000/api';
            this.token = $('meta[name="api-token"]').attr('content');
            
            this.loadActiveVouchers();
        }

        loadActiveVouchers() {
            $.ajax({
                url: `${this.apiBaseUrl}/vouchers/active`,
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Accept': 'application/json'
                },
                beforeSend: () => {
                    $('#voucher-loading').removeClass('d-none');
                    $('#voucher-list').empty();
                },
                success: (response) => {
                    $('#voucher-loading').addClass('d-none');
                    
                    if (response.data && response.data.length > 0) {
                        this.renderVouchers(response.data);
                    } else {
                        $('#voucher-empty').removeClass('d-none');
                    }
                },
                error: (xhr) => {
                    $('#voucher-loading').addClass('d-none');
                    this.handleError(xhr);
                }
            });
        }

        renderVouchers(vouchers) {
            const voucherList = $('#voucher-list');
            voucherList.empty();

            vouchers.forEach(voucherData => {
                const voucher = voucherData.voucher;
                const voucherCard = this.createVoucherCard(voucher, voucherData);
                voucherList.append(voucherCard);
            });
        }

        createVoucherCard(voucher, voucherData) {
            const statusClass = this.getVoucherStatusClass(voucher);
            const formattedDiskon = voucher.diskon + '%';
            const formattedMinPembelian = this.formatRupiah(voucher.min_pembelian);

            return `
                <div class="col-md-4 mb-3">
                    <div class="card voucher-card ${statusClass}">
                        <div class="card-body">
                            <h5 class="card-title">${voucher.kode_voucher}</h5>
                            <p class="card-text">
                                <strong>${voucher.nama_voucher}</strong><br>
                                Diskon: ${formattedDiskon}<br>
                                Min. Pembelian: ${formattedMinPembelian}
                            </p>
                            <p> <small class="text-muted">
                                    Berlaku: ${this.formatDate(voucher.tanggal_mulai)} 
                                    s/d ${this.formatDate(voucher.tanggal_akhir)}
                                </small>
                            </p>
                            <span class="badge ${statusClass}">
                                ${this.formatStatus(voucherData.status)}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }

        handleError(xhr) {
            let errorMessage = 'Terjadi kesalahan';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            alert(errorMessage);
        }

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        getVoucherStatusClass(voucher) {
            const now = new Date();
            const startDate = new Date(voucher.tanggal_mulai);
            const endDate = new Date(voucher.tanggal_akhir);

            if (now < startDate) return 'border-warning text-warning';
            if (now > endDate) return 'border-secondary text-muted';
            return 'border-success text-success';
        }

        formatStatus(status) {
            return status === 'belum_terpakai' ? 'Belum Terpakai' : 'Terpakai';
        }
    }

    new VoucherManager();
});
</script>

