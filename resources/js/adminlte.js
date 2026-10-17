import "./bootstrap";
import "bootstrap";
import "admin-lte";
import { OverlayScrollbars } from "overlayscrollbars";

function initializeSidebar() {
    const sidebar = document.querySelector(".sidebar-wrapper");

    if (sidebar) {
        OverlayScrollbars(sidebar, {
            scrollbars: {
                theme: "os-theme-dark",
                autoHide: "leave",
            },
        });
    }
}

function initializeImagePreviews() {
    document.querySelectorAll("[data-image-preview-target]").forEach((input) => {
        const preview = document.querySelector(input.dataset.imagePreviewTarget);

        if (!preview) {
            return;
        }

        let objectUrl;

        input.addEventListener("change", () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            const [file] = input.files;

            if (!file) {
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.hidden = false;
        });
    });
}

function initializeApplication() {
    initializeSidebar();
    initializeImagePreviews();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeApplication, {
        once: true,
    });
} else {
    initializeApplication();
}
