export default {
    // Function to show an admin notice
    showAdminNotice(message, parentElement, type = "success") {
        let container = parentElement.querySelector(".hges-notice-container");
        // Check if the container already exists in the parent element
        // If it doesn't exist, create it
        if (!container) {
            container = document.createElement("div");
            container.classList.add("hges-notice-container");
            container.style.width = "fit-content";
            parentElement.append(container);
        } else {
            // If the container already exists, clear its content
            container.innerHTML = "";
        }

        // Create the notice element
        const notice = document.createElement("div");
        notice.className = `notice notice-${type} is-dismissible`;
        notice.innerHTML = `<p>${message}</p>`;

        // Add the close button
        const closeBtn = document.createElement("button");
        closeBtn.type = "button";
        closeBtn.className = "notice-dismiss";
        closeBtn.innerHTML =
            '<span class="screen-reader-text">Dismiss this notice.</span>';
        closeBtn.addEventListener("click", () => {
            notice.remove();
        });

        notice.appendChild(closeBtn);
        container.appendChild(notice);
    },
    removeAdminNotices(parentElement) {
        const container = parentElement.querySelector(".hges-notice-container");
        if (container) {
            container.innerHTML = "";
        }
    },
};
