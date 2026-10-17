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
        const fileName = input.dataset.fileNameTarget
            ? document.querySelector(input.dataset.fileNameTarget)
            : null;

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
                if (fileName) {
                    fileName.textContent = "Ningún archivo seleccionado";
                }
                return;
            }

            if (fileName) {
                fileName.textContent = file.name;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.hidden = false;
        });
    });
}

function initializeCartSynchronization() {
    const forms = [...document.querySelectorAll(".js-cart-quantity-form")];
    const checkout = document.querySelector("[data-cart-checkout]");

    if (!forms.length || !checkout) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const errorBox = document.querySelector("[data-cart-error]");
    const status = document.querySelector("[data-cart-status]");
    const states = new Map();
    const currency = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    });

    const updateCheckoutState = () => {
        const blocked = [...states.values()].some(
            (state) => state.dirty || state.promise || state.failed,
        );

        checkout.classList.toggle("disabled", blocked);
        checkout.setAttribute("aria-disabled", blocked ? "true" : "false");
        status.textContent = blocked ? "Sincronizando cantidades…" : "Cantidades actualizadas.";
    };

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.hidden = false;
    };

    const clearError = () => {
        errorBox.hidden = true;
        errorBox.textContent = "";
    };

    const updateInterface = (form, payload) => {
        const line = form.closest("[data-cart-line]");
        const lineSubtotal = line?.querySelector("[data-cart-line-subtotal]");
        const cartTotal = document.querySelector("[data-cart-total]");

        if (lineSubtotal) {
            lineSubtotal.textContent = currency.format(payload.line_subtotal);
        }
        if (cartTotal) {
            cartTotal.textContent = currency.format(payload.cart_total);
        }

        if (payload.quantity === 0 && line) {
            const group = line.closest("[data-cart-group]");
            line.remove();

            if (group && !group.querySelector("[data-cart-line]")) {
                group.remove();
            }

            if (!document.querySelector("[data-cart-line]")) {
                document.querySelector("[data-cart-empty]")?.removeAttribute("hidden");
                document.querySelector("[data-cart-summary]")?.setAttribute("hidden", "");
            }
        }
    };

    const synchronize = async (form, state) => {
        if (state.promise) {
            return state.promise;
        }

        state.promise = (async () => {
            while (state.dirty) {
                state.dirty = false;
                const input = form.querySelector('input[name="cantidad"]');
                const quantity = Number.parseInt(input.value, 10);

                if (!Number.isInteger(quantity) || quantity < 0 || quantity > 99) {
                    throw new Error("Ingresa una cantidad válida entre 0 y 99.");
                }

                const response = await fetch(form.action, {
                    method: "PATCH",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ cantidad: quantity }),
                });
                const payload = await response.json();

                if (!response.ok) {
                    const validationMessage = Object.values(payload.errors ?? {}).flat()[0];
                    throw new Error(validationMessage ?? payload.message ?? "No se pudo actualizar el carrito.");
                }

                updateInterface(form, payload);
                clearError();
            }
        })();

        try {
            await state.promise;
            state.failed = false;
        } catch (error) {
            state.failed = true;
            showError(error.message);
            throw error;
        } finally {
            state.promise = null;
            updateCheckoutState();
        }
    };

    forms.forEach((form) => {
        const input = form.querySelector('input[name="cantidad"]');
        const state = { dirty: false, failed: false, promise: null, timer: null };
        states.set(form, state);

        input.addEventListener("input", () => {
            state.dirty = true;
            state.failed = false;
            clearTimeout(state.timer);
            state.timer = setTimeout(() => {
                state.timer = null;
                synchronize(form, state).catch(() => {});
            }, 450);
            updateCheckoutState();
        });
    });

    checkout.addEventListener("click", async (event) => {
        event.preventDefault();
        const destination = checkout.href;

        for (const [form, state] of states) {
            if (state.timer) {
                clearTimeout(state.timer);
                state.timer = null;
            }

            if (state.dirty || state.promise) {
                try {
                    await synchronize(form, state);
                } catch {
                    return;
                }
            }
        }

        if (![...states.values()].some((state) => state.failed)) {
            window.location.assign(destination);
        }
    });
}

function initializeApplication() {
    initializeSidebar();
    initializeImagePreviews();
    initializeCartSynchronization();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeApplication, {
        once: true,
    });
} else {
    initializeApplication();
}
