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

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeSidebar, {
        once: true,
    });
} else {
    initializeSidebar();
}
