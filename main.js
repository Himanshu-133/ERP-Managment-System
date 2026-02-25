// Main JavaScript functionality for ERP System

// Login form handling
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.querySelector(".login-form")
  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin)
  }

  // Search functionality for tables
  const searchInput = document.querySelector(".search-input")
  if (searchInput) {
    searchInput.addEventListener("input", handleTableSearch)
  }

  // Form validation
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", validateForm)
  })

  // Initialize animations
  initializeAnimations()

  // Initialize tooltips
  initializeTooltips()

  // Auto-hide messages
  autoHideMessages()
})

function handleLogin(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const data = {
    username: formData.get("username"),
    password: formData.get("password"),
    role: formData.get("role"),
  }

  // Show loading state
  const submitBtn = e.target.querySelector('button[type="submit"]')
  const originalText = submitBtn.textContent
  submitBtn.innerHTML = '<span class="loading-spinner"></span> Signing in...'
  submitBtn.disabled = true

  // Clear previous errors
  const errorDiv = document.querySelector(".error-message")
  if (errorDiv) {
    errorDiv.remove()
  }

  fetch("auth/login_process.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showSuccess("Login successful! Redirecting...")
        setTimeout(() => {
          window.location.href = result.redirect
        }, 1000)
      } else {
        showError(result.message)
        submitBtn.textContent = originalText
        submitBtn.disabled = false
      }
    })
    .catch((error) => {
      showError("An error occurred. Please try again.")
      submitBtn.textContent = originalText
      submitBtn.disabled = false
    })
}

function showError(message) {
  const form = document.querySelector(".login-form") || document.querySelector("form")
  const errorDiv = document.createElement("div")
  errorDiv.className = "error-message fade-in"
  errorDiv.innerHTML = `<i class="icon-error"></i> ${message}`
  form.parentNode.insertBefore(errorDiv, form)

  // Auto-hide after 5 seconds
  setTimeout(() => {
    errorDiv.classList.add("fade-out")
    setTimeout(() => errorDiv.remove(), 300)
  }, 5000)
}

function showSuccess(message) {
  const form = document.querySelector("form")
  const successDiv = document.createElement("div")
  successDiv.className = "success-message fade-in"
  successDiv.innerHTML = `<i class="icon-success"></i> ${message}`
  form.parentNode.insertBefore(successDiv, form)

  // Auto-hide after 5 seconds
  setTimeout(() => {
    successDiv.classList.add("fade-out")
    setTimeout(() => successDiv.remove(), 300)
  }, 5000)
}

function handleTableSearch(e) {
  const searchTerm = e.target.value.toLowerCase()
  const table = document.querySelector(".data-table")
  const rows = table.querySelectorAll("tbody tr")
  let visibleCount = 0

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase()
    const isVisible = text.includes(searchTerm)
    row.style.display = isVisible ? "" : "none"
    if (isVisible) visibleCount++
  })

  // Update search results count
  updateSearchResults(visibleCount, rows.length)
}

function updateSearchResults(visible, total) {
  let resultsDiv = document.querySelector(".search-results")
  if (!resultsDiv) {
    resultsDiv = document.createElement("div")
    resultsDiv.className = "search-results"
    const searchContainer = document.querySelector(".search-container")
    searchContainer.appendChild(resultsDiv)
  }

  if (visible === total) {
    resultsDiv.textContent = ""
  } else {
    resultsDiv.textContent = `Showing ${visible} of ${total} students`
  }
}

function validateForm(e) {
  const form = e.target
  const requiredFields = form.querySelectorAll("[required]")
  let isValid = true

  // Clear previous validation states
  form.querySelectorAll(".field-error").forEach((error) => error.remove())

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      showFieldError(field, "This field is required")
      isValid = false
    } else {
      clearFieldError(field)
    }
  })

  // Email validation
  const emailFields = form.querySelectorAll('input[type="email"]')
  emailFields.forEach((field) => {
    if (field.value && !isValidEmail(field.value)) {
      showFieldError(field, "Please enter a valid email address")
      isValid = false
    }
  })

  // Phone validation
  const phoneFields = form.querySelectorAll('input[type="tel"]')
  phoneFields.forEach((field) => {
    if (field.value && !isValidPhone(field.value)) {
      showFieldError(field, "Please enter a valid phone number")
      isValid = false
    }
  })

  if (!isValid) {
    e.preventDefault()
    const firstError = form.querySelector(".field-error")
    if (firstError) {
      firstError.scrollIntoView({ behavior: "smooth", block: "center" })
    }
  }
}

function showFieldError(field, message) {
  field.style.borderColor = "#dc3545"
  field.classList.add("error")

  const errorDiv = document.createElement("div")
  errorDiv.className = "field-error"
  errorDiv.textContent = message
  field.parentNode.appendChild(errorDiv)
}

function clearFieldError(field) {
  field.style.borderColor = "#e1e5e9"
  field.classList.remove("error")

  const errorDiv = field.parentNode.querySelector(".field-error")
  if (errorDiv) {
    errorDiv.remove()
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function isValidPhone(phone) {
  const phoneRegex = /^[+]?[1-9][\d]{0,15}$/
  return phoneRegex.test(phone.replace(/[\s\-$$$$]/g, ""))
}

function initializeAnimations() {
  // Animate cards on scroll
  const cards = document.querySelectorAll(".card")
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate-in")
        }
      })
    },
    { threshold: 0.1 },
  )

  cards.forEach((card) => {
    observer.observe(card)
  })

  // Animate table rows
  const tableRows = document.querySelectorAll(".data-table tbody tr")
  tableRows.forEach((row, index) => {
    row.style.animationDelay = `${index * 0.05}s`
    row.classList.add("table-row-animate")
  })
}

function initializeTooltips() {
  const tooltipElements = document.querySelectorAll("[data-tooltip]")

  tooltipElements.forEach((element) => {
    element.addEventListener("mouseenter", showTooltip)
    element.addEventListener("mouseleave", hideTooltip)
  })
}

function showTooltip(e) {
  const tooltip = document.createElement("div")
  tooltip.className = "tooltip"
  tooltip.textContent = e.target.getAttribute("data-tooltip")
  document.body.appendChild(tooltip)

  const rect = e.target.getBoundingClientRect()
  tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px"
  tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + "px"

  setTimeout(() => tooltip.classList.add("show"), 10)
}

function hideTooltip() {
  const tooltip = document.querySelector(".tooltip")
  if (tooltip) {
    tooltip.remove()
  }
}

function autoHideMessages() {
  const messages = document.querySelectorAll(".success-message, .error-message")
  messages.forEach((message) => {
    setTimeout(() => {
      message.classList.add("fade-out")
      setTimeout(() => message.remove(), 300)
    }, 5000)
  })
}

// Confirmation dialogs with better styling
function confirmAction(message, callback) {
  const modal = document.createElement("div")
  modal.className = "confirm-modal"
  modal.innerHTML = `
        <div class="confirm-content">
            <h3>Confirm Action</h3>
            <p>${message}</p>
            <div class="confirm-actions">
                <button class="btn btn-secondary cancel-btn">Cancel</button>
                <button class="btn btn-primary confirm-btn">Confirm</button>
            </div>
        </div>
    `

  document.body.appendChild(modal)

  modal.querySelector(".cancel-btn").addEventListener("click", () => {
    modal.remove()
  })

  modal.querySelector(".confirm-btn").addEventListener("click", () => {
    callback()
    modal.remove()
  })

  // Close on backdrop click
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.remove()
    }
  })

  setTimeout(() => modal.classList.add("show"), 10)
}

// Enhanced form interactions
document.addEventListener("DOMContentLoaded", () => {
  // Add floating labels effect
  const inputs = document.querySelectorAll("input, select, textarea")
  inputs.forEach((input) => {
    input.addEventListener("focus", () => {
      input.parentNode.classList.add("focused")
    })

    input.addEventListener("blur", () => {
      if (!input.value) {
        input.parentNode.classList.remove("focused")
      }
    })

    // Check if input has value on load
    if (input.value) {
      input.parentNode.classList.add("focused")
    }
  })

  // Add ripple effect to buttons
  const buttons = document.querySelectorAll(".btn")
  buttons.forEach((button) => {
    button.addEventListener("click", createRipple)
  })
})

function createRipple(e) {
  const button = e.currentTarget
  const ripple = document.createElement("span")
  const rect = button.getBoundingClientRect()
  const size = Math.max(rect.width, rect.height)
  const x = e.clientX - rect.left - size / 2
  const y = e.clientY - rect.top - size / 2

  ripple.style.width = ripple.style.height = size + "px"
  ripple.style.left = x + "px"
  ripple.style.top = y + "px"
  ripple.classList.add("ripple")

  button.appendChild(ripple)

  setTimeout(() => {
    ripple.remove()
  }, 600)
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()
    const target = document.querySelector(this.getAttribute("href"))
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      })
    }
  })
})

// Loading states for forms
function setFormLoading(form, loading) {
  const submitBtn = form.querySelector('button[type="submit"]')
  const inputs = form.querySelectorAll("input, select, textarea")

  if (loading) {
    submitBtn.disabled = true
    submitBtn.classList.add("loading")
    inputs.forEach((input) => (input.disabled = true))
  } else {
    submitBtn.disabled = false
    submitBtn.classList.remove("loading")
    inputs.forEach((input) => (input.disabled = false))
  }
}
