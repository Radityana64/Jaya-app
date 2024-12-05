export class NavigationManager {
    constructor() {
        this.pageLinks = document.querySelectorAll(".page-link");
        this.pageContent = document.getElementById("page-content");
        this.initEventListeners();
    }

    initEventListeners() {
        this.pageLinks.forEach((link) => {
            link.addEventListener("click", this.handlePageChange.bind(this));
        });

        window.addEventListener("popstate", this.handlePopState.bind(this));
    }

    async handlePageChange(e) {
        e.preventDefault();
        const page = e.target.dataset.page;

        // Update active state
        this.updateActiveLink(e.target);

        // Update URL
        history.pushState(null, "", `/data-pelanggan/${page}`);

        // Load page content
        await this.loadPageContent(page);
    }

    updateActiveLink(activeLink) {
        this.pageLinks.forEach((link) => link.classList.remove("active"));
        activeLink.classList.add("active");
    }

    async loadPageContent(page) {
        try {
            const response = await fetch(`/data-pelanggan/${page}`, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            const html = await response.text();

            this.pageContent.innerHTML = html;

            // Dispatch custom event for page-specific initialization
            document.dispatchEvent(
                new CustomEvent("pageChanged", {
                    detail: { page },
                })
            );
        } catch (error) {
            console.error("Page load error:", error);
            Swal.fire({
                icon: "error",
                title: "Kesalahan",
                text: "Gagal memuat halaman",
            });
        }
    }

    handlePopState() {
        const page = window.location.pathname.split("/").pop();
        this.loadPageContent(page);
    }
}
