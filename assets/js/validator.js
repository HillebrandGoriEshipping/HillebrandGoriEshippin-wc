window.hges.validator = {
    initialized: false,
    constraints: {},
    init() {
        this.constraints = window.hges.validatorConstraints;
        if (!this.constraints) {
            throw new Error("validatorConstraints is not defined");
        }

        this.initialized = true;
    },
    attachForm(form, constraintId) {
        if (!form || !form.id) {
            throw new Error("Form is not defined or does not have an ID");
        }

        const initializedPromise = new Promise((resolve, reject) => {
            if (this.initialized) {
                resolve();
            } else {
                const interval = setInterval(() => {
                    if (this.initialized) {
                        clearInterval(interval);
                        resolve();
                    }
                }, 100);
            }
        });

        initializedPromise.then(() => {
            form.addEventListener("submit", this.validateForm.bind(
                this,
                form,
                this.constraints[constraintId]
            ));
        });
    },
    validateForm(form, validatorConstraints, event) {
        event.preventDefault();
        const errors = [];
        const formData = new FormData(form);

        for (const [field, value] of formData.entries()) {
            console.log(`Field: ${field}, Value: ${value}`);
            if (! validatorConstraints[field]) {
                continue;
            }

            for (const constraint of validatorConstraints[field]) {
                if (!constraint.allowNull && (value === null || value === '')) {
                    errors.push({field, message: constraint.message || "This field is required."});
                    continue;
                }
            }
        }

        if (errors.length > 0) {
            this.displayErrors(form, errors);
        } else {
            form.submit();
        }
    },
    displayErrors(form, errors) {
        errors.forEach(error => {
            const errorElement = document.createElement("div");
            errorElement.className = "error-message";
            errorElement.id = `error-${error.field}`;
            errorElement.innerHTML = `<p class="error-message__content">${error.message}</p>`;

            const fieldElement = form.querySelector(`[name="${error.field}"]`);
            fieldElement.addEventListener('focus', () => {
                errorElement.remove();
            });
            fieldElement.insertAdjacentElement('afterend', errorElement);
        });
    }
}
document.addEventListener("DOMContentLoaded", window.hges.validator.init.bind(window.hges.validator));