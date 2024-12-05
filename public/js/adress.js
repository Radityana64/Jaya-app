import { ApiService } from "./modules/api.js";
import { NavigationManager } from "./modules/navigation.js";
import { UIHelper } from "./modules/ui-helper.js";

class AddressManager {
    constructor() {
        this.initElements();
        this.initEventListeners();
        this.loadAddresses();
        this.loadProvinsi();
    }

    initElements() {
        // Tambahkan pengecekan null
        this.addressList = document.getElementById("address-list");
        this.addAddressBtn = document.getElementById("add-address-btn");
        this.addressModal = this.addAddressBtn
            ? new bootstrap.Modal(document.getElementById("addressModal"))
            : null;
        this.addressForm = document.getElementById("addressForm");
        this.provinsiSelect = document.getElementById("provinsiSelect");
        this.kabupatenSelect = document.getElementById("kabupatenSelect");
        this.kodeposSelect = document.getElementById("kodeposSelect");
    }

    initEventListeners() {
        // Tambahkan pengecekan null untuk setiap event listener
        if (this.addAddressBtn) {
            this.addAddressBtn.addEventListener("click", () =>
                this.prepareAddressModal()
            );
        }

        if (this.provinsiSelect) {
            this.provinsiSelect.addEventListener("change", (e) =>
                this.handleProvinsiChange(e)
            );
        }

        if (this.kabupatenSelect) {
            this.kabupatenSelect.addEventListener("change", (e) =>
                this.handleKabupatenChange(e)
            );
        }

        if (this.addressForm) {
            this.addressForm.addEventListener("submit", (e) =>
                this.saveAddress(e)
            );
        }
    }

    async loadAddresses() {
        try {
            const response = await ApiService.get("/api/alamat");
            this.renderAddresses(response.data);
        } catch (error) {
            UIHelper.showErrorToast("Gagal Memuat Alamat");
        }
    }

    renderAddresses(addresses) {
        this.addressList.innerHTML = addresses
            .map(
                (address) => `
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5>${address.nama_jalan}</h5>
                        <p class="mb-1">${
                            address.detail_lokasi || "Tidak ada detail tambahan"
                        }</p>
                        <small>${address.kode_pos.nama_kota}, ${
                    address.kode_pos.nama_provinsi
                } ${address.kode_pos.kode_pos}</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary edit-address mr-2" data-id="${
                            address.id_alamat
                        }">Edit</button>
                        <button class="btn btn-sm btn-danger delete-address" data-id="${
                            address.id_alamat
                        }">Hapus</button>
                    </div>
                </div>
            </div>
        `
            )
            .join("");

        this.attachAddressEventListeners();
    }

    attachAddressEventListeners() {
        const editButtons = document.querySelectorAll(".edit-address");
        const deleteButtons = document.querySelectorAll(".delete-address");

        editButtons.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const addressId = e.target.dataset.id;
                this.loadAddressData(addressId);
            });
        });

        deleteButtons.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const addressId = e.target.dataset.id;
                this.deleteAddress(addressId);
            });
        });
    }

    async loadProvinsi() {
        try {
            const response = await ApiService.get("/api/alamat/provinsi");
            this.populateSelect(
                this.provinsiSelect,
                response.data,
                "id_provinsi",
                "provinsi"
            );
        } catch (error) {
            UIHelper.showErrorToast("Gagal Memuat Provinsi");
        }
    }

    async handleProvinsiChange(e) {
        const provinsiId = e.target.value;
        this.kabupatenSelect.disabled = true;
        this.kodeposSelect.disabled = true;

        if (provinsiId) {
            try {
                const response = await ApiService.get(
                    `/api/alamat/kabupaten/${provinsiId}`
                );
                this.populateSelect(
                    this.kabupatenSelect,
                    response.data,
                    "id_kota",
                    "nama_kota"
                );
                this.kabupatenSelect.disabled = false;
            } catch (error) {
                UIHelper.showErrorToast("Gagal Memuat Kabupaten");
            }
        }
    }

    async handleKabupatenChange(e) {
        const kabupatenId = e.target.value;
        this.kodeposSelect.disabled = true;

        if (kabupatenId) {
            try {
                const response = await ApiService.get(
                    `/api/alamat/kodepos/${kabupatenId}`
                );
                this.populateSelect(
                    this.kodeposSelect,
                    response.data,
                    "id_kode_pos",
                    "kode_pos"
                );
                this.kodeposSelect.disabled = false;
            } catch (error) {
                UIHelper.showErrorToast("Gagal Memuat Kode Pos");
            }
        }
    }

    populateSelect(selectElement, data, valueKey, textKey) {
        selectElement.innerHTML =
            `<option value="">Pilih ${selectElement.name}</option>` +
            data
                .map(
                    (item) =>
                        `<option value="${item[valueKey]}">${item[textKey]}</option>`
                )
                .join("");
    }

    prepareAddressModal(addressData = null) {
        this.addressForm.reset();
        document.getElementById("addressId").value = "";
        document.getElementById("modalTitle").textContent = addressData
            ? "Edit Alamat"
            : "Tambah Alamat Baru";

        if (addressData) {
            this.populateAddressForm(addressData);
        } else {
            this.loadProvinsi();
        }

        this.addressModal.show();
    }

    async loadAddressData(addressId) {
        try {
            const response = await ApiService.get(`/api/alamat/${addressId}`);
            this.prepareAddressModal(response.data);
        } catch (error) {
            UIHelper.showErrorToast("Gagal Memuat Data Alamat");
        }
    }

    populateAddressForm(address) {
        document.getElementById("addressId").value = address.id_alamat;
        document.getElementById("namaJalan").value = address.nama_jalan;
        document.getElementById("detailLokasi").value = address.detail_lokasi;

        // Populate and trigger cascading selects
        this.loadProvinsi().then(() => {
            this.provinsiSelect.value =
                address.kode_pos.kota.provinsi.id_provinsi;
            this.handleProvinsiChange({
                target: { value: address.kode_pos.kota.provinsi.id_provinsi },
            }).then(() => {
                this.kabupatenSelect.value = address.kode_pos.id_kota;
                this.handleKabupatenChange({
                    target: { value: address.kode_pos.id_kota },
                }).then(() => {
                    this.kodeposSelect.value = address.id_kode_pos;
                });
            });
        });
    }

    async saveAddress(e) {
        e.preventDefault();
        const formData = {
            id_kode_pos: this.kodeposSelect.value,
            nama_jalan: document.getElementById("namaJalan").value,
            detail_lokasi: document.getElementById("detailLokasi").value,
        };

        const addressId = document.getElementById("addressId").value;

        try {
            const method = addressId ? "put" : "post";
            const url = addressId
                ? `/api/addresses/${addressId}`
                : "/api/addresses";

            await ApiService[method](url, formData);

            UIHelper.showSuccessToast("Alamat berhasil disimpan");
            this.addressModal.hide();
            this.loadAddresses(); // Refresh daftar alamat
        } catch (error) {
            UIHelper.showErrorToast("Gagal Menyimpan Alamat");
        }
    }

    async deleteAddress(addressId) {
        const confirmed = confirm(
            "Apakah Anda yakin ingin menghapus alamat ini?"
        );
        if (confirmed) {
            try {
                await ApiService.delete(`/api/addresses/${addressId}`);
                UIHelper.showSuccessToast("Alamat berhasil dihapus.");
                this.loadAddresses(); // Refresh daftar alamat
            } catch (error) {
                UIHelper.showErrorToast("Gagal Menghapus Alamat");
            }
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    // Pastikan halaman alamat yang aktif
    const addressPage = document.querySelector('[data-page="alamat"]');
    if (addressPage && addressPage.classList.contains("active")) {
        new NavigationManager();
        new AddressManager();
    }
});
