import { ApiService } from "./modules/api.js";
import { NavigationManager } from "./modules/navigation.js";

class ProfileManager {
    constructor() {
        this.initEventListeners();
        this.loadProfileData();
    }

    initEventListeners() {
        const profileForm = document.getElementById("profileForm");
        if (profileForm) {
            profileForm.addEventListener(
                "submit",
                this.handleProfileSubmit.bind(this)
            );
        }

        // Listen for page change event
        document.addEventListener("pageChanged", (e) => {
            if (e.detail.page === "profil") {
                this.loadProfileData();
            }
        });
    }

    getJwtToken() {
        return document
            .querySelector('meta[name="api-token"]')
            .getAttribute("content");
    }

    async loadProfileData() {
        try {
            const token = this.getJwtToken();
            const profileData = await ApiService.get("/api/pelanggan/profil", {
                Authorization: `Bearer ${token}`,
            });

            this.populateProfileForm(profileData);
        } catch (error) {
            console.error("Profile load error:", error);
            Swal.fire({
                icon: "error",
                title: "Gagal Memuat Profil",
                text: "Tidak dapat mengambil data profil",
            });
        }
    }

    populateProfileForm(data) {
        document.getElementById("nama_lengkap").value = data.nama_lengkap;
        document.getElementById("username").value = data.username;
        document.getElementById("email").value = data.email;
        document.getElementById("telepon").value = data.telepon;
    }

    async handleProfileSubmit(e) {
        e.preventDefault();

        const formData = this.getFormData();

        try {
            const token = this.getJwtToken();
            const result = await ApiService.put(
                "/api/pelanggan/update",
                formData,
                {
                    Authorization: `Bearer ${token}`,
                }
            );

            this.handleSuccessUpdate(result);
        } catch (error) {
            this.handleUpdateError(error);
        }
    }

    getFormData() {
        return {
            nama_lengkap: document.getElementById("nama_lengkap").value,
            username: document.getElementById("username").value,
            email: document.getElementById("email").value,
            telepon: document.getElementById("telepon").value,
        };
    }

    handleSuccessUpdate(result) {
        if (result.success) {
            Swal.fire({
                icon: "success",
                title: "Profil Berhasil Diperbarui",
                showConfirmButton: true,
            });
            this.loadProfileData(); // Refresh data
        } else {
            this.handleUpdateError(result);
        }
    }

    handleUpdateError(error) {
        let errorMessage = "Gagal memperbarui profil";
        if (error.errors) {
            errorMessage = Object.values(error.errors).flat().join("\n");
        }

        Swal.fire({
            icon: "error",
            title: "Gagal Memperbarui Profil",
            text: errorMessage,
        });
    }
}

// Inisialisasi saat dokumen siap
document.addEventListener("DOMContentLoaded", () => {
    // Inisialisasi Navigation Manager
    new NavigationManager();

    // Inisialisasi Profile Manager
    new ProfileManager();
});
