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
            if (! validatorConstraints[field]) {
                continue;
            }

            for (const constraintKey in validatorConstraints[field]) {
                const fieldError = this.matchConstraint(value, constraintKey, validatorConstraints[field][constraintKey], field);
                console.log('field error', fieldError);
                if (!fieldError) {
                    continue;
                }
                if (fieldError) {
                    errors.push(fieldError);
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
    },
    matchConstraint(value, constraintKey, constraint, field) {
        if (!constraint) {
            return null;
        }

        const error = { field };
        switch (constraintKey) {
            case 'NotBlank':
                if (!constraint.allowNull && (value === null || value === '')) {
                    error.message = constraint.message || "This field cannot be blank.";
                } else {
                    return null;
                }
                break;
            default:
                return null;
        }

        return error;
    }
}
document.addEventListener("DOMContentLoaded", window.hges.validator.init.bind(window.hges.validator));