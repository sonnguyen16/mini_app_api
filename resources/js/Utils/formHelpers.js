/**
 * Safe error checking for form validation
 * @param {Object} form - Inertia form object
 * @param {string} field - Field name to check
 * @returns {boolean}
 */
export const hasError = (form, field) => {
  return form?.errors && form.errors[field]
}

/**
 * Get error message safely
 * @param {Object} form - Inertia form object
 * @param {string} field - Field name
 * @returns {string}
 */
export const getError = (form, field) => {
  return form?.errors?.[field] || ''
}

/**
 * Get error class for form inputs
 * @param {Object} form - Inertia form object
 * @param {string} field - Field name
 * @param {string} baseClass - Base CSS class
 * @returns {string}
 */
export const getErrorClass = (form, field, baseClass = 'form-control') => {
  return hasError(form, field) ? `${baseClass} is-invalid` : baseClass
}

/**
 * Safe props validation for Vue components
 * @param {Object} props - Component props
 * @param {string} key - Property key
 * @param {*} defaultValue - Default value if undefined
 * @returns {*}
 */
export const safeProp = (props, key, defaultValue = null) => {
  return props?.[key] ?? defaultValue
}
